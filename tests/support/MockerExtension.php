<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests\support;

use PHPUnit\Event\Test\{PreparationStarted, PreparationStartedSubscriber};
use PHPUnit\Event\TestSuite\{Started, StartedSubscriber};
use PHPUnit\Runner\Extension\{Extension, Facade, ParameterCollection};
use PHPUnit\TextUI\Configuration\Configuration;
use Xepozz\InternalMocker\{Mocker, MockerState};
use yii2\extensions\debug\tests\support\stub\MockerFunctions;

/**
 * PHPUnit extension for function mocking and state isolation in tests.
 *
 * Integrates the InternalMocker library with PHPUnit to enable deterministic mocking of global functions (such as
 * {@see \microtime()}) within the test suite, ensuring test isolation and repeatability.
 *
 * This extension registers event subscribers to automatically load mocks at the start of each test suite and reset
 * mock state before each test, providing a controlled environment for time-dependent and side-effect-prone logic.
 *
 * Key features.
 * - Automatic registration of function mocks for the test namespace.
 * - Integration with InternalMocker for global function overrides.
 * - Mocked implementation of {@see \microtime()} for time control in tests.
 * - No side effects on the global state outside the test context.
 * - State reset before each test for isolation and repeatability.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
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
                'function' => static fn(bool $as_float = false): float|string => MockerFunctions::microtime($as_float),
            ],
        ];

        $mocker = new Mocker();

        $mocker->load($mocks);

        MockerState::saveState();
    }
}
