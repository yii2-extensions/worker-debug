<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPUnit\Framework\Attributes\Group;
use yii\web\{HeaderCollection, Response};
use yii2\extensions\debug\WorkerTimelinePanel;

#[Group('worker-debug')]
final class WorkerTimelinePanelTest extends TestCase
{
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
            "'start' value should be greater than or equal to 'end' value, indicating correct timeline order.",
        );
    }
}
