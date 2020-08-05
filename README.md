# pipeline_processor

依赖
--------
* PHP5.5+
* monolog/monolog

安装
------------
```shell
composer require wildfire/pipeline_processor
```

使用
------------

```php
<?php
include 'vendor/autoload.php';

use \PipelineProcessor\PipelineProcessor;
use \PipelineProcessor\Handler\OrderHandler;
use \PipelineProcessor\Handler\PayHandler;
use \PipelineProcessor\Registry;

//创建一些处理器
$order = new \PipelineProcessor\Handler\OrderHandler();
$pay = new PayHandler();

//创建一些通道
$application = new \PipelineProcessor\PipelineProcessor('application');
$api = new \PipelineProcessor\PipelineProcessor('api');

// 或者克隆第一个，只是改变下通道
$security = $api->withName('security');

//对通道应用哪些处理器
$application->pushHandler($pay);
$application->pushHandler($order);

$api->pushHandler($pay);
$security->pushHandler($pay);


$application1 = $application->withName('application1');
$api1 = $application->withName('api1');

//通道的另一种调用方式
Registry::addLogger($application1);
Registry::addLogger($api1);

//通道开始处理
$application->info('application');
$api->info('api');
$security->info('security');

Registry::application1()->info('application1');
Registry::api1()->info('api1');
Registry::clear();
````


创建Job处理类
----------

```php
<?php

namespace PipelineProcessor\Handler;

use PipelineProcessor\Handler\AbstractHandler;

// 必须继承PipelineProcessor\Handler\AbstractHandler, 并实现方法perform
class OrderHandler extends AbstractHandler
{
    protected function perform($record)
    {
	   echo "Order OK\n";
	   print_r($record);
	   echo "\n";
    }
}

```