<p align="center">
    <a href="https://github.com/yii2-extensions/worker-debug" target="_blank">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" alt="Yii Framework">
    </a>
    <h1 align="center">Debug toolbar for the Yii2 PSR Bridge extension.</h1>
    <br>
</p>

<p align="center">
    <a href="https://www.php.net/releases/8.1/en.php" target="_blank">
        <img src="https://img.shields.io/badge/%3E%3D8.1-777BB4.svg?style=for-the-badge&logo=php&logoColor=white" alt="PHP version">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/2.0.53" target="_blank">
        <img src="https://img.shields.io/badge/2.0.x-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white" alt="Yii 2.0.x">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/22.0" target="_blank">
        <img src="https://img.shields.io/badge/22.0.x-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white" alt="Yii 22.0.x">
    </a>
    <a href="https://github.com/yii2-extensions/worker-debug/actions/workflows/build.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/worker-debug/build.yml?style=for-the-badge&label=PHPUnit" alt="PHPUnit">
    </a> 
    <a href="https://dashboard.stryker-mutator.io/reports/github.com/yii2-extensions/worker-debug/main" target="_blank">
        <img src="https://img.shields.io/endpoint?style=for-the-badge&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyii2-extensions%2Fworker-debug%2Fmain" alt="Mutation Testing">
    </a>    
    <a href="https://github.com/yii2-extensions/worker-debug/actions/workflows/static.yml" target="_blank">        
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/worker-debug/static.yml?style=for-the-badge&label=PHPStan" alt="PHPStan">
    </a>  
</p>

A specialized debug toolbar extension that provides enhanced debugging capabilities for Yii2 applications using the PSR 
Bridge, offering comprehensive insights into application execution, performance metrics, and component interactions.

## Features

✅ **Enhanced Debug Toolbar**
- Real-time performance monitoring and metrics collection.

## Quick start

### System requirements

- [`PHP`](https://www.php.net/downloads) 8.1 or higher.
- [`Composer`](https://getcomposer.org/download/) for dependency management.
- [`Yii2`](https://github.com/yiisoft/yii2) 2.0.53+ or 22.x.

### Installation

#### Method 1: Using [Composer](https://getcomposer.org/download/) (recommended)

Install the extension.

```bash
composer require yii2-extensions/worker-debug:^0.1
```

#### Method 2: Manual installation

Add to your `composer.json`.

```json
{
    "require": {
        "yii2-extensions/worker-debug": "^0.1"
    }
}
```

Then run.

```bash
composer update
```

### Basic Usage

Add the following code to your configuration file (`web.php`).

```php
<?php

declare(strict_types=1);

use yii2\extensions\debug\WorkerDebugModule;

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => WorkerDebugModule::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}
```

## Documentation

For testing guidance, see.

- 🧪 [Testing Guide](docs/testing.md)

## Package information

[![Latest Stable Version](https://img.shields.io/packagist/v/yii2-extensions/worker-debug.svg?style=for-the-badge&logo=packagist&logoColor=white&label=Stable)](https://packagist.org/packages/yii2-extensions/worker-debug)
[![Total Downloads](https://img.shields.io/packagist/dt/yii2-extensions/worker-debug.svg?style=for-the-badge&logo=packagist&logoColor=white&label=Downloads)](https://packagist.org/packages/yii2-extensions/worker-debug)

## Quality code

[![codecov](https://img.shields.io/codecov/c/github/yii2-extensions/worker-debug.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Coverage)](https://codecov.io/gh/yii2-extensions/worker-debug)
[![phpstan-level](https://img.shields.io/badge/PHPStan%20level-max-blue?style=for-the-badge)](https://github.com/yii2-extensions/worker-debug/actions/workflows/static.yml)
[![StyleCI](https://img.shields.io/badge/StyleCI-Passed-44CC11.svg?style=for-the-badge&logo=styleci&logoColor=white)](https://github.styleci.io/repos/1038618413?branch=main)

## Our social networks

[![Follow on X](https://img.shields.io/badge/-Follow%20on%20X-1DA1F2.svg?style=for-the-badge&logo=x&logoColor=white&labelColor=000000)](https://x.com/Terabytesoftw)

## License

[![License](https://img.shields.io/github/license/yii2-extensions/worker-debug?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=333333)](LICENSE.md)
