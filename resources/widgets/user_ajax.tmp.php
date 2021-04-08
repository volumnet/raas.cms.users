<?php
/**
 * Отображение данных пользователя
 * @param Block_PHP $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\Controller_Frontend as RAASControllerFrontend;
use RAAS\CMS\Block_PHP;
use RAAS\CMS\Page;

$user = RAASControllerFrontend::i()->user;

$result = [];

if ($user->id) {
    foreach (['login', 'email', 'lang'] as $key) {
        $result[$key] = $user->$key;
    }

    foreach (['phone'] as $key) {
        $field = $user->fields[$key];
        if ($field->id) {
            $result[$key] = $field->getValues();
        }
    }
}

echo json_encode((object)$result);
