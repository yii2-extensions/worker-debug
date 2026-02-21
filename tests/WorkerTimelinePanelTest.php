<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidConfigException;
use yii2\extensions\debug\WorkerTimelinePanel;

/**
 * Unit tests for the {@see WorkerTimelinePanel} class.
 *
 * Test coverage.
 * - Returns timeline data with custom request start time.
 * - Returns timeline data with default request start time.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
#[Group('worker-debug')]
final class WorkerTimelinePanelTest extends TestCase
{
    /**
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function testSaveReturnsCorrectDataStructureWithCustomStartTime(): void
    {
        $customStartTime = microtime(true) - 2;

        $this->webApplication(
            [
                'components' => [
                    'request' => $this->buildRequestWithStatelessStart($customStartTime),
                ],
            ],
        );

        $panel = $this->createPartialMock(WorkerTimelinePanel::class, []);

        $result = $panel->save();

        self::assertIsFloat(
            $result['start'] ?? null,
            "'start' value should be a float representing the request start time.",
        );
        self::assertEqualsWithDelta(
            $customStartTime,
            $result['start'],
            0.005,
            "'start' should match server-param-provided start time within 5ms tolerance.",
        );
        self::assertIsFloat(
            $result['end'] ?? null,
            "'end' value should be a float representing the request end time.",
        );
        self::assertGreaterThanOrEqual(
            2.0,
            $result['end'] - $customStartTime,
            "'end' value should be approximately '2.0' seconds greater than the custom start time.",
        );
        self::assertGreaterThanOrEqual(
            $result['start'],
            $result['end'],
            "'end' value should be greater than or equal to 'start' value, indicating correct timeline order.",
        );
        self::assertIsInt(
            $result['memory'] ?? null,
            "'memory' value should be an integer from 'memory_get_peak_usage()'.",
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

        $panel = $this->createPartialMock(WorkerTimelinePanel::class, []);

        $result = $panel->save();

        self::assertArrayHasKey(
            'start',
            $result,
            "Result array should contain a 'start' key representing the start time.",
        );
        self::assertArrayHasKey(
            'end',
            $result,
            "Result array should contain an 'end' key representing the end time.",
        );
        self::assertArrayHasKey(
            'memory',
            $result,
            "Result array should contain a 'memory' key representing the peak memory usage.",
        );
        self::assertIsFloat(
            $result['start'] ?? null,
            "'start' value should be a float representing the request start time.",
        );
        self::assertIsFloat(
            $result['end'] ?? null,
            "'end' value should be a float representing the request end time.",
        );
        self::assertIsInt(
            $result['memory'] ?? null,
            "'memory' value should be an integer from 'memory_get_peak_usage()'.",
        );
        self::assertGreaterThanOrEqual(
            $result['start'],
            $result['end'],
            "'end' value should be greater than or equal to 'start' value, indicating correct timeline order.",
        );
    }
}
