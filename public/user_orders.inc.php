<?php
/**
 * Отображение заказов пользователя
 */
namespace RAAS\CMS\Users;

use RAAS\FormTab;

/**
 * Отображает вкладку
 * @param FormTab $formTab Вкладка для отображения
 */
$_RAASForm_FormTab = function (FormTab $formTab) {
    echo $formTab->meta['Table']->render();
};
