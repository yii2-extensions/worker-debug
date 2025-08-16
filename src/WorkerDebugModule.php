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
 * Debug module extension for worker profiling and timeline panels integration.
 *
 * Extends the Yii debug module to provide additional profiling and timeline panels for enhanced debugging and
 * performance analysis.
 *
 * This module customizes the debug view path and sets custom HTTP headers for debug sessions, including tag and
 * duration information.
 *
 * Key features.
 * - Customizes the debug view path for compatibility with Yii debug views.
 * - Ensures access control for debug header injection.
 * - Integrates worker profiling and timeline panels into the debug interface.
 * - Sets custom debug headers with tag, duration, and debug link for each response.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
class WorkerDebugModule extends Module
{
    /**
     * Initializes the worker debug module and sets the custom view path.
     *
     * Sets the {@see viewPath} property to use the Yii debug views directory before invoking the parent initialization
     * logic.
     *
     * This ensures compatibility with the Yii debug module view templates.
     *
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     */
    public function init(): void
    {
        $this->viewPath = '@yii/debug/views';

        parent::init();
    }

    /**
     * Sets custom debug headers on the HTTP response for the current debug session.
     *
     * Injects the 'X-Debug-Tag', 'X-Debug-Duration', and 'X-Debug-Link' headers into the response if access is allowed
     * and the log target is a valid object.
     *
     * The duration is calculated from the application start time header or the 'YII_BEGIN_TIME' constant.
     *
     * The debug link points to the debug view for the current tag.
     *
     * This method ensures that debug headers are only set for authorized requests and when the log target is not a
     * string or array, maintaining compatibility with the debug module expected behavior.
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

        $url = Url::toRoute(
            [
                '/' . $this->getUniqueId() . '/default/view',
                'tag' => $this->logTarget->tag,
            ],
        );

        if ($event->sender instanceof Response) {
            $statelessAppStartTime = Yii::$app->request->getHeaders()->get('statelessAppStartTime') ?? YII_BEGIN_TIME;
            $durationMs = ceil((microtime(true) - (float) $statelessAppStartTime) * 1000);

            $event->sender->getHeaders()
                ->set('X-Debug-Tag', $this->logTarget->tag)
                ->set('X-Debug-Duration', (string) $durationMs)
                ->set('X-Debug-Link', $url);
        }
    }

    /**
     * Returns the core panels configuration with worker profiling and timeline integration.
     *
     * Extends the parent core panels array by adding the worker profiling and timeline panels to the debug module.
     *
     * This method ensures that the profiling and timeline panels are available in the debug interface for enhanced
     * performance analysis and profiling capabilities.
     *
     * @return array Merges the parent core panels with worker profiling and timeline panels.
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
