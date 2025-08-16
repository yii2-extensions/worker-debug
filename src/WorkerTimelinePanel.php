<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use Yii;
use yii\debug\panels\TimelinePanel;

use function memory_get_peak_usage;
use function microtime;

class WorkerTimelinePanel extends TimelinePanel
{
    /**
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
