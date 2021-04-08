<?php
/**
 * Стандартный интерфейс входа в систему
 * @param Block_LogIn $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Page;

$interface = new LogInInterface(
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
