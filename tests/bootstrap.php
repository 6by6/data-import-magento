<?php

use AspectMock\Kernel;

$files = [__DIR__.'/../vendor/autoload.php', __DIR__.'/../../../autoload.php'];

foreach ($files as $file) {
    if (file_exists($file)) {
        $loader = include $file;

        break;
    }
}

$loader->add('', __DIR__.'/../vendor/firegento/magento/app');
$loader->add('', __DIR__.'/../vendor/firegento/magento/app/code/community');
$loader->add('', __DIR__.'/../vendor/firegento/magento/app/code/core');
$loader->add('', __DIR__.'/../vendor/firegento/magento/lib');
$loader->add('Mage', __DIR__.'/../vendor/firegento/magento/app/Mage.php');

$loader->register();

$kernel = Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'vendor' => __DIR__.'/../vendor/',
    'includePaths' => [__DIR__.'/../vendor/firegento/magento', __DIR__.'/src'],
    'excludePaths' => [__DIR__],
]);

//bootstrap Magento - eugrh
\Mage::app('admin');
foreach (spl_autoload_functions() as $autoloader) {
    if (is_array($autoloader) && $autoloader[0] instanceof Varien_Autoload) {
        spl_autoload_unregister($autoloader);
    }
}

//get rid of magento error handler as it swallows errors
restore_error_handler();
