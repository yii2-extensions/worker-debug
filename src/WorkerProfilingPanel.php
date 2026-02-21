<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use yii\debug\panels\ProfilingPanel;
use yii\log\Logger;

use function memory_get_peak_usage;

/**
 * Provides profiling panel data with worker-safe request timing.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerProfilingPanel extends ProfilingPanel
{
    /**
     * Saves profiling data for memory usage, execution time, and profile messages.
     *
     * Reads request start time from $_SERVER['REQUEST_TIME_FLOAT'] and falls back to YII_BEGIN_TIME.
     *
     * @return array Profiling data.
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function save(): array
    {
        /** @phpstan-var float $requestTimeFloat */
        $requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'] ?? YII_BEGIN_TIME;

        return [
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - $requestTimeFloat,
            'messages' => $this->getLogMessages(Logger::LEVEL_PROFILE),
        ];
    }
}
