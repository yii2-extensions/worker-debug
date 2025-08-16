<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use Yii;
use yii\debug\panels\TimelinePanel;

use function memory_get_peak_usage;

/**
 * Timeline panel extension for worker debug sessions.
 *
 * Extends the Yii debug timeline panel to capture and store timing and memory usage data for worker-based requests.
 *
 * This panel records the start time, end time, and peak memory usage of the application, using a custom header if
 * present to determine the application start time.
 *
 * The collected data is used for performance analysis and visualization in the debug interface.
 *
 * Key features.
 * - Captures precise start and end times for each request, supporting stateless worker environments.
 * - Integrates with the Yii debug timeline panel for seamless analysis.
 * - Records peak memory usage for profiling resource consumption.
 * - Supports custom application start time via the 'statelessAppStartTime' HTTP header.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerTimelinePanel extends TimelinePanel
{
    /**
     * Saves timing and memory usage data for the current worker request.
     *
     * Captures the application start time (from the 'statelessAppStartTime' HTTP header if present, or the
     * {@see YII_BEGIN_TIME} constant), the current time as the end time, and the peak memory usage.
     *
     * This data is used for performance analysis and visualization in the debug timeline panel.
     *
     * @return array Timing and memory usage data for the request.
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function save(): array
    {
        $statelessAppStartTime = Yii::$app->request->getHeaders()->get('statelessAppStartTime') ?? YII_BEGIN_TIME;

        return [
            'start' => (float) $statelessAppStartTime,
            'end' => microtime(true),
            'memory' => memory_get_peak_usage(),
        ];
    }
}
