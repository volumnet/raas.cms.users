<?php
namespace RAAS\CMS\Users;

class FieldsTable extends \RAAS\CMS\FieldsTable
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