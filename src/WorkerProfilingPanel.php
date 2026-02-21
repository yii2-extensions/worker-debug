<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use Yii;
use yii\debug\panels\ProfilingPanel;
use yii\log\Logger;

use function memory_get_peak_usage;

/**
 * Extends Yii profiling panel behavior for stateless worker requests.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerProfilingPanel extends ProfilingPanel
{
    /**
     * Saves profiling data for memory usage, execution time, and profile messages.
     *
     * @return array Profiling data.
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function save(): array
    {
        $requestTimeFloat = Yii::$app->request->getHeaders()->get('REQUEST_TIME_FLOAT') ?? YII_BEGIN_TIME;

        return [
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - (float) $requestTimeFloat,
            'messages' => $this->getLogMessages(Logger::LEVEL_PROFILE),
        ];
    }
}
