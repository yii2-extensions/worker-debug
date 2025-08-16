<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests\support;

use PHPUnit\Event\Test\{PreparationStarted, PreparationStartedSubscriber};
use PHPUnit\Event\TestSuite\{Started, StartedSubscriber};
use PHPUnit\Runner\Extension\{Extension, Facade, ParameterCollection};
use PHPUnit\TextUI\Configuration\Configuration;
use Xepozz\InternalMocker\{Mocker, MockerState};
use yii2\extensions\debug\tests\support\stub\TimeFunctions;

final class MockerExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class implements StartedSubscriber {
                public function notify(Started $event): void
                {
                    MockerExtension::load();
                }
            },
            new class implements PreparationStartedSubscriber {
                public function notify(PreparationStarted $event): void
                {
                    MockerState::resetState();
                }
            },
        );
    }

    public static function load(): void
    {
        $mocks = [
            [
                'namespace' => 'yii2\extensions\debug',
                'name' => 'microtime',
                'function' => static fn(bool $as_float = false): float|string => TimeFunctions::microtime($as_float),
            ],
        ];

        $mocker = new Mocker();
        $mocker->load($mocks);

        MockerState::saveState();
    }
}
