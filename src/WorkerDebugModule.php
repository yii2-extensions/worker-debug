<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use yii\debug\Module;
use yii\helpers\Url;
use yii\web\Response;

use function array_merge;
use function ceil;
use function microtime;

class WorkerDebugModule extends Module
{
    public function init(): void
    {
        $this->viewPath = '@yii/debug/views';

        parent::init();
    }

    public function setDebugHeaders($event): void
    {
        if ($this->checkAccess() === false) {
            return;
        }

        if (is_string($this->logTarget) || is_array($this->logTarget)) {
            return;
        }

        $url = Url::toRoute(
            [
                '/' . $this->getUniqueId() . '/default/view',
                'tag' => $this->logTarget->tag,
            ],
        );

        if ($event->sender instanceof Response) {
            $statelessAppStartTime = $event->sender->getHeaders()->get('statelessAppStartTime') ?? YII_BEGIN_TIME;
            $durationMs = (int) ceil((microtime(true) - (float) $statelessAppStartTime) * 1000);

            $event->sender->getHeaders()
                ->set('X-Debug-Tag', $this->logTarget->tag)
                ->set('X-Debug-Duration', (string) $durationMs)
                ->set('X-Debug-Link', $url);
        }
    }

    /**
     * @phpstan-return array<array-key, mixed>
     */
    protected function corePanels(): array
    {
        return array_merge(
            parent::corePanels(),
            [
                'profiling' => ['class' => WorkerProfilingPanel::class],
                'timeline' => ['class' => WorkerTimelinePanel::class],
            ],
        );
    }
}
