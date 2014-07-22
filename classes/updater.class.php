<?php
namespace RAAS\CMS\Users;
use \RAAS\IContext;

class Updater extends \RAAS\Updater
{
    public function preInstall()
    {
        
    }


    public function postInstall()
    {
        $w = new Webmaster();
        $w->checkStdSnippets();
    }
}