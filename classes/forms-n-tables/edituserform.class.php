<?php
namespace RAAS\CMS\Users;
use \RAAS\Application;
use \RAAS\FormTab;
use \RAAS\Field as RAASField;
use \RAAS\Option;
use \RAAS\CMS\Package;
use \RAAS\CMS\User;
use \RAAS\Controller_Frontend;

class EditUserForm extends \RAAS\Form
{
    protected $_view;

    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Users::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = array())
    {
        $view = $this->view;
        $t = $this;
        unset($params['view']);
        $Item = isset($params['Item']) ? $params['Item'] : null;

        $CONTENT = array();
        $CONTENT['languages'] = array();
        foreach ($this->view->availableLanguages as $key => $val) {
            $CONTENT['languages'][] = array('value' => $key, 'caption' => $val);
        }

        $defaultParams = array(
            'Item' => $Item, 
            'parentUrl' => $this->url, 
            'caption' => $Item->id ? $this->view->_('EDITING_USER') : $this->view->_('CREATING_USER'),
            'children' => array(),
            'template' => 'edit',
            'export' => function($Form) use ($t) {
                $oldVis = (int)$Form->Item->vis;
                $Form->exportDefault();
                $Form->Item->new = 0;
                $newVis = (int)$Form->Item->vis;
                if (($an = Module::i()->registryGet('automatic_notification')) && $Form->Item->email) {
                    if (!$oldVis && $newVis && in_array($an, array(Module::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION, Module::AUTOMATIC_NOTIFICATION_BOTH))) {
                        // Уведомление об активации
                        $t->sendNotification($Form->Item);
                    } elseif ($oldVis && !$newVis && ($an == Module::AUTOMATIC_NOTIFICATION_BOTH)) {
                        // Уведомление о блокировке
                        $t->sendNotification($Form->Item);
                    }
                }
            }
        );
        
        // Логин
        $Field = new RAASField(array('name' => 'login', 'caption' => $this->view->_('LOGIN'), 'required' => 'required'));
        $Field->required = 'required';
        $Field->check = function($Field) use ($t) {
            $localError = $Field->getErrors();
            if (!$localError) {
                if ($Field->Form->Item->checkLoginExists($_POST[$Field->name])) {
                    $localError[] = array('name' => 'INVALID', 'value' => $Field->name, 'description' => $t->view->_('ERR_LOGIN_EXISTS'));
                }
            }
            return $localError;
        };
        $defaultParams['children']['login'] = $Field;

        // Пароль
        $Field = new RAASField(array(
            'type' => 'password', 
            'name' => 'password', 
            'caption' => $this->view->_('PASSWORD'),
            'confirm' => true, 
            'export' => function($Field) use ($t) { 
                if ($_POST[$Field->name]) {
                    $Field->Form->Item->password_md5 = \RAAS\Application::i()->md5It(trim($_POST[$Field->name])); 
                }
            }
        ));
        if (!$Item->id) {
            $Field->required = 'required';
        }
        $defaultParams['children']['password'] = $Field;
        
        // E-mail
        $Field = new RAASField(array('type' => 'email', 'name' => 'email', 'caption' => $this->view->_('EMAIL')));
        $Field->check = function($Field) use ($t) {
            $localError = $Field->getErrors();
            if (!$localError) {
                if ($Field->Form->Item->checkEmailExists($_POST[$Field->name])) {
                    $localError[] = array('name' => 'INVALID', 'value' => $Field->name, 'description' => $t->view->_('ERR_EMAIL_EXISTS'));
                }
            }
            return $localError;
        };
        $defaultParams['children']['email'] = $Field;

        // Активирован
        $Field = new RAASField(array('type' => 'checkbox', 'name' => 'vis', 'caption' => $this->view->_('ACTIVATED'), 'template' => 'edit.vis.tmp.php'));
        $defaultParams['children']['vis'] = $Field;

        // Язык
        $Field = new RAASField(array(
            'type' => 'select', 'name' => 'lang', 'caption' => $this->view->_('LANGUAGE'), 'children' => $CONTENT['languages'], 'default' => $this->view->language
        ));
        $defaultParams['children']['lang'] = $Field;
                       

        // Кастомные поля
        foreach ($Item->fields as $row) {
            $defaultParams['children'][$row->urn] = $row->Field;
        }

        // Социальные сети
        $defaultParams['children']['social'] = new RAASField(array(
            'type' => 'text', 
            'name' => 'social', 
            'multiple' => true, 
            'caption' => $this->view->_('SOCIAL_NETWORKS'),
            'export' => function($Field) use ($t) {
                $Field->Form->Item->meta_social = isset($_POST[$Field->name]) ? (array)$_POST[$Field->name] : array();
            }
        ));

        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }


    public function sendNotification(User $User)
    {
        $lang = $User->lang ? $User->lang : $this->view->language;
        Controller_Frontend::i()->exportLang(Application::i(), $lang);
        Controller_Frontend::i()->exportLang(Package::i(), $lang);
        foreach (Package::i()->modules as $row) {
            Controller_Frontend::i()->exportLang($row, $lang);
        }
        $text = Module::i()->getActivationNotification($User);
        $subject = sprintf($this->view->_($User->vis ? 'ACTIVATION_NOTIFICATION' : 'BLOCK_NOTIFICATION'), $_SERVER['HTTP_HOST']);
        Application::i()->sendmail(trim($User->email), trim($subject), trim($text), $this->view->_('ADMINISTRATION_OF_SITE') . ' ' . $_SERVER['HTTP_HOST'], 'info@' . $_SERVER['HTTP_HOST']);
    }
}