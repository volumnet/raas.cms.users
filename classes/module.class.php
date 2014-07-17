<?php
namespace RAAS\CMS\Users;

class Module extends \RAAS\Module
{
    protected static $instance;

    public function __get($var)
    {
        switch ($var) {
            default:
                return parent::__get($var);
                break;
        }
    }


    public function dev_fields()
    {
        return User_Field::getSet();
    }
}