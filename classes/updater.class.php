<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\Snippet;

class Updater extends \RAAS\Updater
{
    public function preInstall()
    {
        
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
}