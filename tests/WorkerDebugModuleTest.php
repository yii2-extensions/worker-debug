<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPForge\Support\Assert;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use yii\base\{Event, InvalidConfigException};
use yii\debug\LogTarget;
use yii\debug\panels\{
    AssetPanel,
    ConfigPanel,
    DbPanel,
    DumpPanel,
    EventPanel,
    LogPanel,
    MailPanel,
    RequestPanel,
    RouterPanel,
    UserPanel,
};
use yii\web\{HeaderCollection, Response};
use yii2\extensions\debug\tests\support\stub\MockerFunctions;
use yii2\extensions\debug\{WorkerDebugModule, WorkerProfilingPanel, WorkerTimelinePanel};

use function dirname;

/**
 * Test suite for {@see WorkerDebugModule} class functionality and behavior.
 *
 * Verifies the debug module ability to configure core panels, set debug headers, and handle various edge cases related
 * to header calculation, log target types, and access control.
 *
 * These tests ensure correct integration of custom and standard debug panels, accurate duration calculation for debug
 * headers, and robust handling of different log target and response scenarios.
 *
 * Test coverage.
 * - Access control for debug header setting.
 * - Core panel configuration and default values.
 * - Debug header calculation and duration rounding.
 * - Handling of stateless and Yii start times.
 * - Log target type validation (object, array, string).
 * - Response sender type validation.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('worker-debug')]
final class WorkerDebugModuleTest extends TestCase
{
    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testReturnCorePanelsAndModuleDefaults(): void
    {
        $this->webApplication();

        $module = new WorkerDebugModule('debug');

        self::assertSame(
            '2.1.27.0',
            $module->getVersion(),
            "'getVersion()' should return '2.1.27.0' as the default module version.",
        );

        self::assertSame(
            dirname(__DIR__) . '/runtime/debug',
            $module->dataPath,
            "'dataPath' should be equal to 'tests/runtime/debug' to ensure debug data is stored in the expected path.",
        );

        $panels = Assert::invokeMethod($module, 'corePanels');

        self::assertSame(
            [
                'config' => [
                    'class' => ConfigPanel::class,
                ],
                'log' => [
                    'class' => LogPanel::class,
                ],
                'profiling' => [
                    'class' => WorkerProfilingPanel::class,
                ],
                'db' => [
                    'class' => DbPanel::class,
                ],
                'event' => [
                    'class' => EventPanel::class,
                ],
                'mail' => [
                    'class' => MailPanel::class,
                ],
                'timeline' => [
                    'class' => WorkerTimelinePanel::class,
                ],
                'dump' => [
                    'class' => DumpPanel::class,
                ],
                'router' => [
                    'class' => RouterPanel::class,
                ],
                'request' => [
                    'class' => RequestPanel::class,
                ],
                'user' => [
                    'class' => UserPanel::class,
                ],
                'asset' => [
                    'class' => AssetPanel::class,
                ],
            ],
            $panels,
            "'corePanels' should include the 'profiling' and 'timeline' panels with the correct classes, ensuring " .
            'integration of custom and standard panels in the debug module.',
        );
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSetDebugHeadersCalculatesCorrectDurationInMilliseconds(): void
    {
        $startTime = '1234567890.500';
        $currentTime = 1234567893.1234;

        MockerFunctions::setMockedMicrotime($currentTime);

        $headers = $this->createPartialMock(HeaderCollection::class, ['get', 'set']);

        $headers->method('get')->with('statelessAppStartTime')->willReturn($startTime);

        $response = $this->createPartialMock(Response::class, ['getHeaders']);

        $response->method('getHeaders')->willReturn($headers);

        $this->webApplication(
            [
                'components' => [
                    'request' => $this->buildRequestWithStatelessStart($startTime),
                    'response' => $response,
                ],
            ],
        );

        $module = $this->createPartialMock(
            WorkerDebugModule::class,
            [
                'checkAccess',
                'getUniqueId',
            ],
        );

        $module->method('checkAccess')->willReturn(true);
        $module->method('getUniqueId')->willReturn('test-module');

        $logTarget = $this->createMock(LogTarget::class);

        $logTarget->tag = 'test-debug-tag';
        $module->logTarget = $logTarget;

        $durationCaptured = false;

        $headers
            ->expects(self::exactly(3))
            ->method('set')
            ->willReturnCallback(
                static function (string $name, string $value) use ($headers, &$durationCaptured): HeaderCollection {
                    if ($name === 'X-Debug-Duration') {
                        self::assertSame(
                            2624,
                            (int) $value,
                            "'X-Debug-Duration' should be '2624ms' using 'ceil()' for proper rounding up, got: {$value}. ",
                        );

                        $durationCaptured = true;
                    }

                    return $headers;
                },
            );

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);

        self::assertTrue(
            $durationCaptured,
            "'X-Debug-Duration' header should be set and calculated correctly.",
        );
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSetDebugHeadersDoesNothingWhenAccessIsNotAllowed(): void
    {
        $response = $this->createPartialMock(Response::class, ['getHeaders']);

        $response->expects(self::never())->method('getHeaders');

        $this->webApplication(
            [
                'components' => [
                    'response' => $response,
                ],
            ],
        );

        $module = $this->createPartialMock(WorkerDebugModule::class, ['checkAccess']);

        $module->method('checkAccess')->willReturn(false);

        $logTarget = $this->createMock(LogTarget::class);

        $logTarget->tag = 'test-debug-tag';
        $module->logTarget = $logTarget;

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSetDebugHeadersDoesNothingWhenLogTargetIsArray(): void
    {
        $response = $this->createPartialMock(Response::class, ['getHeaders']);

        $response->expects(self::never())->method('getHeaders');

        $this->webApplication(
            [
                'components' => [
                    'response' => $response,
                ],
            ],
        );

        $module = $this->createPartialMock(WorkerDebugModule::class, ['checkAccess']);

        $module->method('checkAccess')->willReturn(true);

        $module->logTarget = ['array-target'];

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSetDebugHeadersDoesNothingWhenLogTargetIsString(): void
    {
        $response = $this->createPartialMock(Response::class, ['getHeaders']);

        $response->expects(self::never())->method('getHeaders');

        $this->webApplication(
            [
                'components' => [
                    'response' => $response,
                ],
            ],
        );

        $module = $this->createPartialMock(WorkerDebugModule::class, ['checkAccess']);

        $module->method('checkAccess')->willReturn(true);

        $module->logTarget = 'string-target';

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSetDebugHeadersDoesNothingWhenSenderIsNotResponse(): void
    {
        $this->webApplication();

        $module = $this->createPartialMock(WorkerDebugModule::class, ['checkAccess']);

        $module->method('checkAccess')->willReturn(true);

        $logTarget = $this->createMock(LogTarget::class);

        $logTarget->tag = 'test-tag';
        $module->logTarget = $logTarget;

        $event = new Event();

        $event->sender = new stdClass(); // not a Response instance

        $this->expectNotToPerformAssertions();

        $module->setDebugHeaders($event);
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSetDebugHeadersUsesStatelessAppStartTimeWhenAvailable(): void
    {
        $customStartTime = (string) (microtime(true) - 1);

        $headers = $this->createMock(HeaderCollection::class);

        $response = $this->createPartialMock(Response::class, ['getHeaders']);

        $response->method('getHeaders')->willReturn($headers);

        $this->webApplication(
            [
                'components' => [
                    'request' => $this->buildRequestWithStatelessStart($customStartTime),
                    'response' => $response,
                ],
            ],
        );

        $module = $this->createPartialMock(
            WorkerDebugModule::class,
            [
                'checkAccess',
                'getUniqueId',
            ],
        );

        $module->method('checkAccess')->willReturn(true);
        $module->method('getUniqueId')->willReturn('test-module');

        $logTarget = $this->createMock(LogTarget::class);

        $logTarget->tag = 'test-debug-tag';
        $module->logTarget = $logTarget;

        $durationCaptured = false;

        $headers
            ->expects(self::exactly(3))
            ->method('set')
            ->willReturnCallback(
                static function (string $name, string $value) use ($headers, &$durationCaptured): HeaderCollection {
                    if ($name === 'X-Debug-Duration') {
                        self::assertGreaterThan(
                            0,
                            (float) $value,
                            "'X-Debug-Duration' header should be greater than '0' when 'statelessAppStartTime' is " .
                            "available, got: {$value}.",
                        );
                        self::assertLessThan(
                            10000,
                            (float) $value,
                            "'X-Debug-Duration' should be a reasonable duration in milliseconds, got: {$value}. " .
                            'This suggests incorrect calculation (possibly addition instead of subtraction).',
                        );

                        $durationCaptured = true;
                    }

                    return $headers;
                },
            );

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);

        self::assertTrue(
            $durationCaptured,
            "'X-Debug-Duration' header should be set when 'statelessAppStartTime' is available.",
        );
    }

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSetDebugHeadersUsesYiiBeginTimeWhenStatelessAppStartTimeHeaderIsMissing(): void
    {
        $headers = $this->createMock(HeaderCollection::class);

        $response = $this->createPartialMock(Response::class, ['getHeaders']);

        $response->method('getHeaders')->willReturn($headers);

        $this->webApplication(
            [
                'components' => [
                    'response' => $response,
                ],
            ],
        );

        $module = $this->createPartialMock(
            WorkerDebugModule::class,
            [
                'checkAccess',
                'getUniqueId',
            ],
        );

        $module->method('checkAccess')->willReturn(true);
        $module->method('getUniqueId')->willReturn('test-module');

        $logTarget = $this->createMock(LogTarget::class);

        $logTarget->tag = 'test-debug-tag';
        $module->logTarget = $logTarget;

        $callCount = 0;

        $headers
            ->expects(self::exactly(3))
            ->method('set')
            ->willReturnCallback(
                static function (string $name, string $value) use (&$callCount, $headers): HeaderCollection {
                    $callCount++;

                    switch ($callCount) {
                        case 1:
                            self::assertSame(
                                'X-Debug-Tag',
                                $name,
                                "Header name for debug tag should be 'X-Debug-Tag', got '{$name}'.",
                            );
                            self::assertSame(
                                'test-debug-tag',
                                $value,
                                "Header value for debug tag should be 'test-debug-tag', got '{$value}'.",
                            );

                            break;
                        case 2:
                            self::assertSame(
                                'X-Debug-Duration',
                                $name,
                                "Header name for debug duration should be 'X-Debug-Duration', got '{$name}'.",
                            );

                            break;
                        case 3:
                            self::assertSame(
                                'X-Debug-Link',
                                $name,
                                "Header name for debug link should be 'X-Debug-Link', got '{$name}'.",
                            );
                            self::assertStringContainsString(
                                '/index.php?r=test-module%2Fdefault%2Fview&tag=test-debug-tag',
                                $value,
                                "Header value for debug link should contain the correct URL with tag, got '{$value}'.",
                            );

                            break;
                    }

                    return $headers;
                },
            );

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }
}
