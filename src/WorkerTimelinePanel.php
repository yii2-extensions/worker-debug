<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use yii\debug\panels\TimelinePanel;

use function memory_get_peak_usage;

/**
 * Provides timeline panel data with worker-safe request timing.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerTimelinePanel extends TimelinePanel
{
    /**
     * Saves timeline data for request start, request end, and peak memory usage.
     *
     * Reads request start time from $_SERVER['REQUEST_TIME_FLOAT'] and falls back to YII_BEGIN_TIME.
     *
     * @return array Timeline data.
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function save(): array
    {
        $requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'] ?? YII_BEGIN_TIME;

        return [
            'start' => $requestTimeFloat,
            'end' => microtime(true),
            'memory' => memory_get_peak_usage(),
        ];
    }
}
