<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit21ab68b0425d74fb0326f23f1f3e5093
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'Zend\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Zend\\' => 
        array (
            0 => __DIR__ . '/..' . '/zendframework/zendframework/library/Zend',
        ),
    );

    public static $prefixesPsr0 = array (
        'Z' => 
        array (
            'ZendXml' => 
            array (
                0 => __DIR__ . '/..' . '/zendframework/zendxml/library',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit21ab68b0425d74fb0326f23f1f3e5093::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit21ab68b0425d74fb0326f23f1f3e5093::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit21ab68b0425d74fb0326f23f1f3e5093::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}