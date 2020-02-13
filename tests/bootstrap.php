<?php
namespace RAAS\CMS;

use RAAS\Application;

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/resources/Controller_Cron.php';
require __DIR__ . '/resources/RegisterInterfaceWithSocialMock.php';
require __DIR__ . '/resources/LogInInterfaceWithSocialMock.php';
require __DIR__ . '/src/BaseTest.php';
require __DIR__ . '/src/BaseDBTest.php';
Application::i()->run('cron');
