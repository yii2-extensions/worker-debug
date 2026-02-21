<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use Yii;
use yii\debug\panels\TimelinePanel;

use function memory_get_peak_usage;

/**
 * Extends Yii timeline panel behavior for stateless worker requests.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerTimelinePanel extends TimelinePanel
{
    /**
     * Saves timeline data for request start, request end, and peak memory usage.
     *
     * @return array Timeline data.
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function save(): array
    {
        $requestTimeFloat = Yii::$app->request->getHeaders()->get('REQUEST_TIME_FLOAT') ?? YII_BEGIN_TIME;

        return [
            'start' => (float) $requestTimeFloat,
            'end' => microtime(true),
            'memory' => memory_get_peak_usage(),
        ];
    }
}
