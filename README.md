<p align="center">
    <a href="https://github.com/yii2-extensions/worker-debug" target="_blank">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" alt="Yii Framework">
    </a>
    <h1 align="center">Debug toolbar for extension PSR Bridge.</h1>
    <br>
</p>

<p align="center">
    <a href="https://www.php.net/releases/8.1/en.php" target="_blank">
        <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-787CB5" alt="PHP Version">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/2.0.53" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20-2.0.53-blue" alt="Yii2 2.0.53">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/22.0" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20-22-blue" alt="Yii2 22.0">
    </a>
    <a href="https://github.com/yii2-extensions/worker-debug/actions/workflows/build.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/worker-debug/actions/workflows/build.yml/badge.svg" alt="PHPUnit">
    </a> 
    <a href="https://dashboard.stryker-mutator.io/reports/github.com/yii2-extensions/worker-debug/main" target="_blank">
        <img src="https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyii2-extensions%2Fworker-debug%2Fmain" alt="Mutation Testing">
    </a>    
    <a href="https://github.com/yii2-extensions/worker-debug/actions/workflows/static.yml" target="_blank">        
        <img src="https://github.com/yii2-extensions/worker-debug/actions/workflows/static.yml/badge.svg" alt="Static Analysis">
    </a>  
</p>

## Features

- ✅ **PHP 8.1+**: This package requires PHP 8.1 or higher.

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
        "github_username/github_repository-name": "^0.1"
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

For detailed configuration options and advanced usage.

- 🧪 [Testing Guide](docs/testing.md)

## Quality code

[![Latest Stable Version](https://poser.pugx.org/yii2-extensions/worker-debug/v)](https://github.com/yii2-extensions/worker-debug/releases)
[![Total Downloads](https://poser.pugx.org/yii2-extensions/worker-debug/downloads)](https://packagist.org/packages/yii2-extensions/worker-debug)
[![codecov](https://codecov.io/gh/yii2-extensions/worker-debug/graph/badge.svg?token=Upc4yA23YN)](https://codecov.io/gh/yii2-extensions/worker-debug)
[![phpstan-level](https://img.shields.io/badge/PHPStan%20level-max-blue)](https://github.com/yii2-extensions/localeurls/actions/workflows/static.yml)
[![StyleCI](https://github.styleci.io/repos/698621511/shield?branch=main)](https://github.styleci.io/repos/698621511?branch=main)

## Our social networks

[![X](https://img.shields.io/badge/follow-@terabytesoftw-1DA1F2?logo=x&logoColor=1DA1F2&labelColor=555555&style=flat)](https://x.com/Terabytesoftw)

## License

[![License](https://img.shields.io/github/license/yii2-extensions/worker-debug?cacheSeconds=0)](LICENSE.md)
