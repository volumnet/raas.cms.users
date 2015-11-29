<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\Snippet;

class Updater extends \RAAS\Updater
{
    public function preInstall()
    {
        $this->update20151129();
    }


    public function postInstall()
    {
        $w = new Webmaster();
        $s = Snippet::importByURN('__RAAS_users_register_interface');
        $w->checkStdSnippets();
        if (!$s || !$s->id) {
            $w->createCab();
        }
    }

    public function update20151129()
    {
        if (in_array('urn', $this->columns(\SOME\SOME::_dbprefix() . "cms_forms"))) {
            $SQL_query = "UPDATE " . \SOME\SOME::_dbprefix() . "cms_forms SET urn = 'register' WHERE (urn = '') AND (name = 'Форма для регистрации' OR name = 'Registration form')";
            $this->SQL->query($SQL_query);
        }
    }

}