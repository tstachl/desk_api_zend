<?php
/**
 * Copyright 2014 Salesforce.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0.html
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

error_reporting(-1);
date_default_timezone_set('America/Los_Angeles');

// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . '/composer.lock')) {
    die("Dependencies must be installed using composer:\n\nphp composer.phar install\n\n"
        . "See http://getcomposer.org for help with installing composer\n");
}

// Include the composer autoloader
$loader = require 'vendor/autoload.php';
// $loader->addClassMap(['../lib', 'DeskTest']);
$loader->add('DeskTest_', __DIR__);

DeskTest_TestCase::setFixturesPath(__DIR__ . '/fixtures');
if (file_exists(__DIR__ . '/config.yml')) {
  DeskTest_TestCase::setConfig(__DIR__ . '/config.yml');
} else {
  DeskTest_TestCase::setConfig(__DIR__ . '/config.example.yml');
}


// /**
//  * Compilation includes configuration file
//  */
// define('MAGENTO_ROOT', dirname(__DIR__));
//
// $compilerConfig = MAGENTO_ROOT . '/includes/config.php';
// if (file_exists($compilerConfig)) {
//     include $compilerConfig;
// }
//
// $mageFilename = MAGENTO_ROOT . '/app/Mage.php';
// $maintenanceFile = 'maintenance.flag';
//
// if (!file_exists($mageFilename)) {
//     if (is_dir('downloader')) {
//         header("Location: downloader");
//     } else {
//         echo $mageFilename." was not found";
//     }
//     exit;
// }
//
// if (file_exists($maintenanceFile)) {
//     include_once dirname(__FILE__) . '/errors/503.php';
//     exit;
// }
//
// require_once $mageFilename;
//
// Mage::setIsDeveloperMode(true);
