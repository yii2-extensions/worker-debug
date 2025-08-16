<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\debug\WorkerTimelinePanel;

#[Group('worker-debug')]
final class WorkerTimelinePanelTest extends TestCase
{
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

        $panel = $this->createPartialMock(WorkerTimelinePanel::class, []);

        $result = $panel->save();

        self::assertIsFloat(
            $result['start'] ?? null,
            "'start' value should be a float representing the request start time.",
        );
        self::assertEqualsWithDelta(
            (float) $customStartTime,
            $result['start'],
            0.005,
            "'start' should match header-provided start time within 5ms tolerance.",
        );
        self::assertIsFloat(
            $result['end'] ?? null,
            "'end' value should be a float representing the request end time.",
        );
        self::assertGreaterThanOrEqual(
            2.0,
            $result['end'] - (float) $customStartTime,
            "'end' value should be approximately '2.0' seconds greater than the custom start time.",
        );
        self::assertGreaterThanOrEqual(
            $result['start'],
            $result['end'],
            "'end' value should be greater than or equal to 'start' value, indicating correct timeline order.",
        );
    }

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
