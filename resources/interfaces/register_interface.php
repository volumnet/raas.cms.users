<?php
/**
 * Стандартный интерфейс регистрации
 * @param Block_Register $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Page;

$interface = new RegisterInterface(
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
