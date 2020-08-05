<?php
use PipelineProcessor\Exception\ServiceNotFoundException;
use PipelineProcessor\Exception\ClassNotFoundException;

class Loader
{
    /* 路径映射 */
    public static $vendorMap = array(
        'PipelineProcessor' => __DIR__ . DIRECTORY_SEPARATOR,
    );

    /**
     * 自动加载器
     */
    public static function autoload($class)
    {
        $file = self::findFile($class);
        if (file_exists($file)) {
            self::includeFile($file);
            self::validateClassName($class);
        }else{
            throw new ServiceNotFoundException(sprintf('can not find file [%s]', $file));
        }
    }

    /**
     * 解析文件路径
     */
    private static function findFile($class)
    {
        $class = ltrim($class,"/");
        $vendor = substr($class, 0, strpos($class, '\\')); // 顶级命名空间
        $vendorDir = self::$vendorMap[$vendor]; // 文件基目录
        $filePath = substr($class, strlen($vendor)) . '.php'; // 文件相对路径
        return strtr($vendorDir . $filePath, '\\', DIRECTORY_SEPARATOR); // 文件标准路径
    }

    private static function validateClassName($className) {
        
        if (!class_exists($className)) {
            throw new ClassNotFoundException(sprintf('can not find class [%s]', $className));
        }
    }
    
    /**
     * 引入文件
     */
    private static function includeFile($file)
    {
        if (is_file($file)) {
            include $file;
        }
    }
}
