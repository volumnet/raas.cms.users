<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\User;
use \RAAS\Controller_Frontend;
use \RAAS\Application;

class Controller_Ajax extends Abstract_Controller
{
    protected static $instance;
    
    protected function execute()
    {
        switch ($this->action) {
            case 'get_notifications': case 'send_notification':
                $this->{$this->action}();
                break;
        }
    }
    
    
    protected function get_notifications()
    {
        $notifications = array();
        $notifications['activate_subject'] = sprintf($this->view->_('ACTIVATION_NOTIFICATION'), $_SERVER['HTTP_HOST']);
        $notifications['block_subject'] = sprintf($this->view->_('BLOCK_NOTIFICATION'), $_SERVER['HTTP_HOST']);
        $Item = new User((int)$this->id);
        $lang = $Item->lang ? $Item->lang : $this->view->language;
        Controller_Frontend::i()->exportLang($this->model->parent, $lang);
        foreach ($this->model->parent->modules as $row) {
            Controller_Frontend::i()->exportLang($row, $lang);
        }
        $notifications['activate'] = $this->model->getActivationNotification($Item, true);
        $notifications['block'] = $this->model->getActivationNotification($Item, false);
        $OUT = $notifications;
        $this->view->get_notifications($OUT);
    }


    protected function send_notification()
    {
        $Item = new User((int)$this->id);
        if (trim($_POST['notification_text']) && trim($Item->email)) {
            Application::i()->sendmail(trim($Item->email), trim($_POST['notification_subject']), trim($_POST['notification_text']), $this->view->_('ADMINISTRATION_OF_SITE') . ' ' . $_SERVER['HTTP_HOST'], 'mailer@' . $_SERVER['HTTP_HOST']);
        }
        $this->view->send_notification($OUT);
    }
}