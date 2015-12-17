<?php

/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader = require __DIR__.'/../vendor/autoload.php';
$autoLoader->addPsr4('CodeGeneratormocks\\', __DIR__.'/mocks');
$autoLoader->addPsr4('CodeGeneratorHelpers\\', __DIR__.'/helpers');
