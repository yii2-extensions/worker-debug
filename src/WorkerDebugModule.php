<?php

declare(strict_types=1);

namespace yii2\extensions\debug;

use Yii;
use yii\base\{Event, InvalidConfigException};
use yii\debug\Module;
use yii\helpers\Url;
use yii\web\Response;

use function array_merge;
use function ceil;
use function is_array;
use function is_string;

/**
 * Extends Yii debug module integration for worker profiling and timeline panels.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerDebugModule extends Module
{
    /**
     * Initializes the module and sets the Yii debug view path.
     *
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function init(): void
    {
        $this->viewPath = '@yii/debug/views';

        parent::init();
    }

    /**
     * Sets debug headers for the current response when access is allowed.
     *
     * @param Event $event Event object containing the response sender.
     */
    public function setDebugHeaders($event): void
    {
        if ($this->checkAccess() === false) {
            return;
        }

        if (is_string($this->logTarget) || is_array($this->logTarget)) {
            return;
        }

        if ($event->sender instanceof Response) {
            $url = Url::toRoute(
                [
                    '/' . $this->getUniqueId() . '/default/view',
                    'tag' => $this->logTarget->tag,
                ],
            );
            $requestTimeFloat = Yii::$app->request->getHeaders()->get('REQUEST_TIME_FLOAT') ?? YII_BEGIN_TIME;
            $durationMs = ceil((microtime(true) - (float) $requestTimeFloat) * 1000);

            $event->sender->getHeaders()
                ->set('X-Debug-Tag', $this->logTarget->tag)
                ->set('X-Debug-Duration', (string) $durationMs)
                ->set('X-Debug-Link', $url);
        }
    }

    /**
     * Returns core panel configuration with worker profiling and timeline panels.
     *
     * @return array Merged core panel configuration.
     *
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
