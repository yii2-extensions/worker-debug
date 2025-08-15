<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use yii\debug\Module;
use yii\helpers\Url;
use yii\web\Response;

use function microtime;
use function number_format;

class WorkerDebugModule extends Module
{
    public function init(): void
    {
        parent::init();

        $this->viewPath = '@yii/debug/views';
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

            $event->sender->getHeaders()
                ->set('X-Debug-Tag', $this->logTarget->tag)
                ->set('X-Debug-Duration', number_format((microtime(true) - (float) $statelessAppStartTime) * 1000 + 1))
                ->set('X-Debug-Link', $url);
        }
    }

    /**
     * @phpstan-return array<array-key, mixed>
     */
    protected function corePanels(): array
    {
        $corePanels = parent::corePanels();

        $corePanels['profiling'] = ['class' => WorkerProfilingPanel::class];
        $corePanels['timeline'] = ['class' => WorkerTimelinePanel::class];

        return $corePanels;
    }
}
