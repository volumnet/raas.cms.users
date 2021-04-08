<?php
/**
 * Стандартный интерфейс восстановления пароля
 * @param Block_Recovery $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Page;

$interface = new RecoveryInterface(
    $Block,
    $Page,
    $_GET,
    $_POST,
    $_COOKIE,
    $_SESSION,
    $_SERVER,
    $_FILES
);
return $interface->process();
