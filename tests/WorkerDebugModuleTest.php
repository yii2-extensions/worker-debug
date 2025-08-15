<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPUnit\Framework\Attributes\Group;
use stdClass;
use yii\base\Event;
use yii\debug\LogTarget;
use yii\web\{HeaderCollection, Response};
use yii2\extensions\debug\WorkerDebugModule;

#[Group('worker-debug')]
final class WorkerDebugModuleTest extends TestCase
{
    public function testReturnModuleVersionAndDataPathWhenInstantiated(): void
    {
        $this->webApplication();

        $module = new WorkerDebugModule('debug');

        self::assertSame(
            '2.1.27.0',
            $module->getVersion(),
            "'getVersion()' should return '2.1.27.0' for the default module version.",
        );
        self::assertSame(
            dirname(__DIR__) . '/runtime/debug',
            $module->dataPath,
        );
    }

    public function testSetDebugHeadersDoesNothingWhenAccessIsNotAllowed(): void
    {
        $this->webApplication();

        $module = $this->createPartialMock(WorkerDebugModule::class, ['checkAccess']);

        $module->method('checkAccess')->willReturn(false);

        $response = $this->createMock(Response::class);

        $response->expects(self::never())->method('getHeaders');

        $logTarget = $this->createMock(LogTarget::class);

        $logTarget->tag = 'test-debug-tag';
        $module->logTarget = $logTarget;

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }

    public function testSetDebugHeadersDoesNothingWhenLogTargetIsArray(): void
    {
        $this->webApplication();

        $module = $this->createPartialMock(WorkerDebugModule::class, ['checkAccess']);

        $module->method('checkAccess')->willReturn(true);

        $module->logTarget = ['array-target'];

        $response = $this->createMock(Response::class);

        $response->expects(self::never())->method('getHeaders');

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }

    public function testSetDebugHeadersDoesNothingWhenLogTargetIsString(): void
    {
        $this->webApplication();

        $module = $this->createPartialMock(WorkerDebugModule::class, ['checkAccess']);

        $module->method('checkAccess')->willReturn(true);

        $module->logTarget = 'string-target';

        $response = $this->createMock(Response::class);

        $response->expects(self::never())->method('getHeaders');

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }

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

    public function testSetDebugHeadersSetsCorrectHeadersWhenConditionsAreMet(): void
    {
        $this->webApplication();

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

        $headers = $this->createMock(HeaderCollection::class);

        $headers->method('get')->with('statelessAppStartTime')->willReturn(null);

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

        $response = $this->createMock(Response::class);

        $response->method('getHeaders')->willReturn($headers);

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);
    }

    public function testSetDebugHeadersUsesStatelessAppStartTimeWhenAvailable(): void
    {
        $this->webApplication();

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

        $customStartTime = (string) (microtime(true) - 1);

        $headers = $this->createMock(HeaderCollection::class);

        $headers->method('get')->with('statelessAppStartTime')->willReturn($customStartTime);

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
                            "This suggests incorrect calculation (possibly addition instead of subtraction).",
                        );

                        $durationCaptured = true;
                    }

                    return $headers;
                },
            );

        $response = $this->createMock(Response::class);

        $response->method('getHeaders')->willReturn($headers);

        $event = new Event();

        $event->sender = $response;

        $module->setDebugHeaders($event);

        self::assertTrue(
            $durationCaptured,
            "'X-Debug-Duration' header should be set when 'statelessAppStartTime' is available.",
        );
    }
}
