<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use Yii;
use yii\debug\panels\ProfilingPanel;
use yii\log\Logger;

class WorkerProfilingPanel extends ProfilingPanel
{
    /**
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
