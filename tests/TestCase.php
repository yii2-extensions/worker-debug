<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use PHPForge\Support\TestSupport;
use PHPUnit\Framework\MockObject\Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\{Application, HeaderCollection, Request};
use yii2\extensions\debug\tests\support\stub\MockerFunctions;

use function dirname;

/**
 * Base test case for worker debug module test suites.
 *
 * Provides common setup and teardown logic for all worker debug module tests, including application lifecycle
 * management and request mocking utilities.
 *
 * This class centralizes logic for initializing the test environment, managing application state, and providing helpers
 * for request and session handling, ensuring consistency and reducing duplication across test cases.
 *
 * Key features.
 * - Application bootstrap and teardown for isolated test execution.
 * - Session and logger cleanup to prevent test contamination.
 * - Stateless request header mocking for profiling scenarios.
 * - Utility for building web application instances with custom configuration.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use TestSupport;

    /**
     * A secret key used for cookie validation in tests.
     */
    protected const COOKIE_VALIDATION_KEY = 'wefJDF8sfdsfSDefwqdxj9oq'; // gitleaks:allow (test-only, not a real secret)

    /**
     * Prepares the test environment before each test execution.
     *
     * Invokes the parent setup logic and resets the mocked microtime state to ensure consistent timing behavior across
     * test runs.
     */
    protected function setUp(): void
    {
        parent::setUp();

        MockerFunctions::clearMockedMicrotime();
    }

    /**
     * Tears down the test environment after each test execution.
     *
     * Closes the application and flushes the logger to ensure a clean state for subsequent tests.
     */
    public function tearDown(): void
    {
        $this->closeApplication();

        parent::tearDown();
    }

    /**
     * Creates a partial {@see Request} instance with the 'statelessAppStartTime' header mocked.
     *
     * Builds a web request object with the 'statelessAppStartTime' header set to the provided value, enabling tests for
     * stateless application scenarios and start time measurement.
     *
     * @param string|null $value Value to return for the 'statelessAppStartTime' header.
     *
     * @throws Exception If the mock object creation fails.
     *
     * @return Request Partial request instance with the mocked header.
     */
    protected function buildRequestWithStatelessStart(string|null $value): Request
    {
        $headers = $this->createPartialMock(HeaderCollection::class, ['get']);

        $headers->method('get')->with('statelessAppStartTime')->willReturn($value);

        $request = $this->createPartialMock(Request::class, ['getHeaders']);

        $request->method('getHeaders')->willReturn($headers);

        return $request;
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
     */
    protected function webApplication(array $config = []): Application
    {
        return new Application(
            ArrayHelper::merge(
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
            ),
        );
    }
}
