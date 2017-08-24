<?php

umask(0000);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

//var_dump(get_class_methods($loader));
var_dump(($loader->getClassMap()));

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase
    {
    }
}
