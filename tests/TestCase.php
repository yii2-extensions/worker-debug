<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\{Application, IdentityInterface, Request};

use function dirname;

/**
 * Base class for package integration tests.
 *
 * Provides common setup and teardown logic for all package integration tests, including application lifecycle
 * management and request mocking utilities.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * A secret key used for cookie validation in tests.
     */
    protected const COOKIE_VALIDATION_KEY = 'wefJDF8sfdsfSDefwqdxj9oq'; // gitleaks:allow (test-only, not a real secret)

    /**
     * Tracks whether the original REQUEST_TIME_FLOAT value existed before test override.
     */
    private bool $hasOriginalRequestTimeFloat = false;

    /**
     * Stores the original REQUEST_TIME_FLOAT value for restoration in tearDown.
     */
    private mixed $originalRequestTimeFloat = null;

    /**
     * Tears down the test environment after each test execution.
     *
     * Closes the application and flushes the logger to ensure a clean state for subsequent tests.
     */
    public function tearDown(): void
    {
        if ($this->hasOriginalRequestTimeFloat) {
            $_SERVER['REQUEST_TIME_FLOAT'] = $this->originalRequestTimeFloat;
        } else {
            unset($_SERVER['REQUEST_TIME_FLOAT']);
        }

        $this->closeApplication();

        parent::tearDown();
    }

    /**
     * Creates a partial {@see Request} instance with the 'REQUEST_TIME_FLOAT' server param mocked.
     *
     * Builds a web request object with the 'REQUEST_TIME_FLOAT' server param set to the provided value, enabling
     * tests for
     * stateless application scenarios and start time measurement.
     *
     * @param string|null $value Value to return for the 'REQUEST_TIME_FLOAT' server param.
     *
     * @return Request Request instance with prepared server params.
     */
    protected function buildRequestWithStatelessStart(string|null $value): Request
    {
        $this->hasOriginalRequestTimeFloat = array_key_exists('REQUEST_TIME_FLOAT', $_SERVER);
        $this->originalRequestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'] ?? null;

        if ($value === null) {
            unset($_SERVER['REQUEST_TIME_FLOAT']);
        } else {
            $_SERVER['REQUEST_TIME_FLOAT'] = $value;
        }

        return new Request(
            [
                'cookieValidationKey' => self::COOKIE_VALIDATION_KEY,
                'scriptFile' => __DIR__ . '/index.php',
                'scriptUrl' => '/index.php',
            ],
        );
    }

    /**
     * Closes the application and flushes the logger to ensure a clean state for subsequent tests.
     *
     * Destroys the session if active and flushes the logger to prevent test contamination and maintain isolation
     * between test cases.
     */
    protected function closeApplication(): void
    {
        if (Yii::$app->has('session')) {
            $session = Yii::$app->getSession();

            if ($session->getIsActive()) {
                $session->destroy();
                $session->close();
            }
        }

        Yii::getLogger()->flush();
    }

    /**
     * Prepares the test environment before each test execution.
     *
     * Invokes the parent setup logic to ensure consistent test initialization.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Builds and returns a new web {@see Application} instance for test scenarios.
     *
     * Merges the provided configuration with default test settings, including runtime and vendor paths, aliases, and a
     * secure cookie validation key.
     *
     * This utility ensures a consistent and isolated application state for each test case, supporting custom overrides
     * as needed.
     *
     * @param array $config Optional configuration overrides for the application instance.
     *
     * @throws InvalidConfigException if the configuration is invalid or incomplete.
     *
     * @return Application Initialized web application instance for testing.
     *
     * @phpstan-param array<string, mixed> $config
     * @phpstan-return Application<IdentityInterface>
     */
    protected function webApplication(array $config = []): Application
    {
        /** @phpstan-var array<string, mixed> $configApplication */
        $configApplication = ArrayHelper::merge(
            [
                'id' => 'web-app',
                'basePath' => __DIR__,
                'runtimePath' => dirname(__DIR__) . '/runtime',
                'vendorPath' => dirname(__DIR__) . '/vendor',
                'aliases' => [
                    '@bower' => '@vendor/bower-asset',
                    '@npm' => '@vendor/npm-asset',
                ],
                'components' => [
                    'request' => [
                        'cookieValidationKey' => self::COOKIE_VALIDATION_KEY,
                        'scriptFile' => __DIR__ . '/index.php',
                        'scriptUrl' => '/index.php',
                    ],
                ],
            ],
            $config,
        );

        return new Application($configApplication);
    }
}
