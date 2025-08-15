<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPUnit\Framework\Attributes\Group;
use yii\web\{HeaderCollection, Response};
use yii2\extensions\debug\WorkerTimelinePanel;

#[Group('worker-debug')]
final class WorkerTimelinePanelTest extends TestCase
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

        $panel = $this->createPartialMock(WorkerTimelinePanel::class, []);

        $result = $panel->save();

        self::assertIsFloat(
            $result['start'] ?? null,
            "'start' value should be a float representing the request start time.",
        );
        self::assertGreaterThan(
            1.5,
            $result['end'] ?? null,
            "'end' value should be greater than '1.5' seconds when using custom start time from '2' seconds ago.",
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
            "'start' value should be greater than or equal to 'end' value, indicating correct timeline order.",
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
