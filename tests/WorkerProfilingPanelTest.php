<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPUnit\Framework\Attributes\Group;
use yii\log\Logger;
use yii\web\{HeaderCollection, Response};
use yii2\extensions\debug\WorkerProfilingPanel;

#[Group('worker-debug')]
final class WorkerProfilingPanelTest extends TestCase
{
    public function testSaveReturnsCorrectDataStructureWithCustomStartTime(): void
    {
        $customStartTime = (string) (microtime(true) - 2);

        $headers = $this->createMock(HeaderCollection::class);

        $headers->method('get')->with('statelessAppStartTime')->willReturn($customStartTime);

        $response = $this->createMock(Response::class);

        $response->method('getHeaders')->willReturn($headers);

        $this->webApplication(
            [
                'components' => [
                    'response' => $response,
                ],
            ],
        );

        $panel = $this->createPartialMock(WorkerProfilingPanel::class, ['getLogMessages']);

        $expectedMessages = [
            [
                'level' => Logger::LEVEL_PROFILE,
                'message' => 'custom profile message',
            ],
        ];

        $panel->method('getLogMessages')->with(Logger::LEVEL_PROFILE)->willReturn($expectedMessages);

        $result = $panel->save();

        self::assertGreaterThan(
            1.5,
            $result['time'] ?? null,
            "'time' value should be greater than '1.5' seconds when using custom start time from '2' seconds ago.",
        );
        self::assertLessThan(
            10.0,
            $result['time'] ?? null,
            "'time' value should be a reasonable duration in seconds.",
        );
        self::assertSame(
            $expectedMessages,
            $result['messages'] ?? null,
            "'messages' value should match the result from 'getLogMessages()' with custom start time.",
        );
    }

    public function testSaveReturnsCorrectDataStructureWithDefaultStartTime(): void
    {
        $headers = $this->createMock(HeaderCollection::class);

        $headers->method('get')->with('statelessAppStartTime')->willReturn(null);

        $response = $this->createMock(Response::class);

        $response->method('getHeaders')->willReturn($headers);

        $this->webApplication(
            [
                'components' => [
                    'response' => $response,
                ],
            ],
        );

        $panel = $this->createPartialMock(WorkerProfilingPanel::class, ['getLogMessages']);

        $expectedMessages = [
            [
                'level' => Logger::LEVEL_PROFILE,
                'message' => 'test profile message',
            ],
        ];

        $panel->method('getLogMessages')->with(Logger::LEVEL_PROFILE)->willReturn($expectedMessages);

        $result = $panel->save();

        self::assertArrayHasKey(
            'memory',
            $result,
            "Result array should contain a 'memory' key.",
        );
        self::assertArrayHasKey(
            'time',
            $result,
            "Result array should contain a 'time' key.",
        );
        self::assertArrayHasKey(
            'messages',
            $result,
            "Result array should contain a 'messages' key.",
        );
        self::assertIsInt(
            $result['memory'] ?? null,
            "'memory' value should be an integer from 'memory_get_peak_usage()'.",
        );
        self::assertIsFloat(
            $result['time'] ?? null,
            "'time' value should be a float representing execution time.",
        );
        self::assertSame(
            $expectedMessages,
            $result['messages'] ?? null,
            "'messages' value should match the result from 'getLogMessages()'.",
        );
    }

    public function testSaveUsesMemoryGetPeakUsage(): void
    {
        $headers = $this->createMock(HeaderCollection::class);

        $headers->method('get')->with('statelessAppStartTime')->willReturn(null);

        $response = $this->createMock(Response::class);

        $response->method('getHeaders')->willReturn($headers);

        $this->webApplication(
            [
                'components' => [
                    'response' => $response,
                ],
            ],
        );

        $panel = $this->createPartialMock(WorkerProfilingPanel::class, ['getLogMessages']);

        $panel->method('getLogMessages')->willReturn([]);

        $result = $panel->save();

        $actualMemoryUsage = memory_get_peak_usage();

        self::assertLessThanOrEqual(
            $actualMemoryUsage,
            $result['memory'] ?? null,
            "'memory' value should be less than or equal to current peak memory usage.",
        );
        self::assertGreaterThan(
            0,
            $result['memory'] ?? null,
            "'memory' value should be greater than '0'.",
        );
    }
}
