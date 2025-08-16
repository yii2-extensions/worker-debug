<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidConfigException;
use yii\log\Logger;
use yii2\extensions\debug\WorkerProfilingPanel;

use function memory_get_peak_usage;

/**
 * Test suite for {@see WorkerProfilingPanel} class functionality and behavior.
 *
 * Verifies the profiling panel ability to capture and report execution time, memory usage, and profiling messages under
 * different application start time scenarios.
 *
 * These tests ensure the panel correctly calculates execution duration using custom and default start times, returns
 * the expected data structure, and accurately reports peak memory usage.
 *
 * Test coverage.
 * - Custom start time handling for execution duration calculation.
 * - Default start time fallback and data structure validation.
 * - Memory usage reporting using {@see memory_get_peak_usage()}.
 * - Profiling message retrieval and structure.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('worker-debug')]
final class WorkerProfilingPanelTest extends TestCase
{
    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSaveReturnsCorrectDataStructureWithCustomStartTime(): void
    {
        $customStartTime = (string) (microtime(true) - 2);

        $this->webApplication(
            [
                'components' => [
                    'request' => $this->buildRequestWithStatelessStart($customStartTime),
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

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSaveReturnsCorrectDataStructureWithDefaultStartTime(): void
    {
        $this->webApplication(
            [
                'components' => [
                    'request' => $this->buildRequestWithStatelessStart(null),
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

    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSaveUsesMemoryGetPeakUsage(): void
    {
        $this->webApplication(
            [
                'components' => [
                    'request' => $this->buildRequestWithStatelessStart(null),
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
