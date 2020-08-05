<?php

include __DIR__ . DIRECTORY_SEPARATOR . '../../../autoload.php'; // 引入加载器

use \PipelineProcessor\PipelineProcessor;
use \PipelineProcessor\Handler\PayHandler;
use \PipelineProcessor\Handler\OrderHandler;
use \PipelineProcessor\Registry;

// 创建一些处理器
$order = new OrderHandler();
$pay = new PayHandler();

$application = new PipelineProcessor('application');
$api = new PipelineProcessor('api');

// 或者克隆第一个，只是改变下通道
$security = $api->withName('security');

$application->pushHandler($pay);
$api->pushHandler($pay);
$security->pushHandler($pay);

$application1 = $application->withName('application1');
$api1 = $application->withName('api1');

Registry::addLogger($application1);
Registry::addLogger($api1);


$application->info('application');
$api->info('api');
$security->info('security');

Registry::application1()->info('application1');
Registry::api1()->info('api1');
Registry::clear();


