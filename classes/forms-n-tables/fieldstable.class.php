<?php
/**
 * Таблица полей пользователей
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\FieldsTable as CMSFieldsTable;

/**
 * Класс таблицы полей пользователей
 * @property-read ViewSub_Dev $view Представление
 */
class FieldsTable extends CMSFieldsTable
{
    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Dev::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }
}
