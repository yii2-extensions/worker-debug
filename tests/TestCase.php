<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\{Application, HeaderCollection, Request};
use yii2\extensions\debug\tests\support\stub\MockerFunctions;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * A secret key used for cookie validation in tests.
     */
    protected const COOKIE_VALIDATION_KEY = 'wefJDF8sfdsfSDefwqdxj9oq'; // gitleaks:allow (test-only, not a real secret)

    public function tearDown(): void
    {
        $this->closeApplication();

        MockerFunctions::clearMockedMicrotime();

        parent::tearDown();
    }

    protected function buildRequestWithStatelessStart(string|null $value): Request
    {
        $headers = $this->createPartialMock(HeaderCollection::class, ['get']);

        $headers->method('get')->with('statelessAppStartTime')->willReturn($value);

        $request = $this->createPartialMock(Request::class, ['getHeaders']);

        $request->method('getHeaders')->willReturn($headers);

        return $request;
    }

    protected function closeApplication(): void
    {
        if (Yii::$app->has('session')) {
            $session = Yii::$app->getSession();

            if ($session->getIsActive()) {
                $session->destroy();
                $session->close();
            }
        }

        // ensure the logger is flushed after closing the application
        $logger = Yii::getLogger();
        $logger->flush();
    }

    /**
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
