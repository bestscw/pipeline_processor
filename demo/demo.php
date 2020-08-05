<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'src/Loader.php'; // 引入加载器
spl_autoload_register('Loader::autoload'); // 注册自动加载

use \PipelineProcessor\PipelineProcessor;
use \PipelineProcessor\Handler\OrderHandler;
use \PipelineProcessor\Handler\PayHandler;
use \PipelineProcessor\Registry;

// 创建一些处理器
$order = new \PipelineProcessor\Handler\OrderHandler();
$pay = new PayHandler();

$application = new \PipelineProcessor\PipelineProcessor('application');
$api = new \PipelineProcessor\PipelineProcessor('api');

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

