<?php
namespace RAAS\Cms\Users;

abstract class Abstract_Controller extends \RAAS\Abstract_Module_Controller
{
    protected static $instance;
    
    protected function execute()
    {
        $this->view->submenu = $this->view->usersMenu();
        switch ($this->sub) {
            case 'dev':
                parent::execute();
                break;
            default:
                Sub_Users::i()->run();
                break;
        }
    }
}