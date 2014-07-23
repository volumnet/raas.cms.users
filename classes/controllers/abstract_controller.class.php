<?php
namespace RAAS\CMS\Users;

abstract class Abstract_Controller extends \RAAS\Abstract_Module_Controller
{
    protected static $instance;
    
    protected function execute()
    {
        switch ($this->sub) {
            case 'dev':
                parent::execute();
                break;
            default:
                Sub_Users::i()->run();
                break;
        }
    }

    
    public function config()
    {
        return array(
            array('type' => 'codearea', 'name' => 'activation_notify', 'caption' => $this->view->_('ACTIVATION_BLOCK_NOTIFICATION')),
            array(
                'type' => 'select', 
                'name' => 'automatic_notification', 
                'caption' => $this->view->_('SEND_NOTIFICATION_AUTOMATICALLY'),
                'children' => array(
                    array('value' => Module::AUTOMATIC_NOTIFICATION_NONE, 'caption' => $this->view->_('_NONE')),
                    array('value' => Module::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION, 'caption' => $this->view->_('ONLY_ACTIVATION')),
                    array('value' => Module::AUTOMATIC_NOTIFICATION_BOTH, 'caption' => $this->view->_('BOTH_ACTIVATION_AND_BLOCKING')),
                )
            ),
        );
    }
}