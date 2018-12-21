<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

// Define file upload properties
/*******************************************************************
 * 以下选项有些不能使用ini_set()进行设置，
 * 需在php.ini中或.htaccess文件中进行设置
 ini_set('file_uploads', 'On');
 ini_set('post_max_size', '500M');
 ini_set('upload_max_filesize', '500M');
 ini_set('session.upload_progress.enabled', 'On');
 ini_set('session.upload_progress.freq', '1%');
 ini_set('session.upload_progress.min_freq', '1');
 ********************************************************************/

return array(
    'db' => array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname=Myblog;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
        ),
    ),
    'phpSettings'   => array(
        'display_startup_errors'        => true,
        'display_errors'                => true,
        'zlib.output_compression'       => true, //打开gzip压缩
    ),
);
