<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use Yii;
use yii\debug\panels\ProfilingPanel;
use yii\log\Logger;

use function memory_get_peak_usage;

/**
 * Profiling panel for worker debug module with stateless start time support.
 *
 * Extends the Yii profiling panel to accurately measure execution time and memory usage in stateless worker
 * environments.
 *
 * This panel retrieves the application start time from the 'statelessAppStartTime' HTTP header if available, falling
 * back to the 'YII_BEGIN_TIME' constant.
 *
 * This ensures precise profiling in scenarios where the application lifecycle is decoupled from the request lifecycle,
 * such as in queue workers or asynchronous jobs.
 *
 * Key features.
 * - Collects and exposes profiling log messages for analysis.
 * - Integrates seamlessly with the Yii debug module interface.
 * - Reports peak memory usage and execution duration.
 * - Uses stateless application start time from HTTP headers for accurate timing.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerProfilingPanel extends ProfilingPanel
{
    /**
     * Saves profiling data including memory usage, execution time, and profiling log messages.
     *
     * Retrieves the application start time from the 'statelessAppStartTime' HTTP header if available, falling back to
     * the {@see YII_BEGIN_TIME} constant.
     *
     * This enables accurate profiling in stateless worker environments where the application lifecycle is decoupled
     * from the request lifecycle.
     *
     * @return array Associative array with memory usage, execution time, and log messages.
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function save(): array
    {
        $statelessAppStartTime = Yii::$app->request->getHeaders()->get('statelessAppStartTime') ?? YII_BEGIN_TIME;

        return [
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - (float) $statelessAppStartTime,
            'messages' => $this->getLogMessages(Logger::LEVEL_PROFILE),
        ];
    }
}
