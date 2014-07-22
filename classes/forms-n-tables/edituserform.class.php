<?php
namespace RAAS\CMS\Users;
use \RAAS\Application;
use \RAAS\FormTab;
use \RAAS\Field as RAASField;
use \RAAS\Option;

class EditUserForm extends \RAAS\Form
{
    protected $_view;

    public function __construct(array $params = array())
    {
        $this->_view = isset($params['view']) ? $params['view'] : null;
        $t = $this;
        unset($params['view']);
        $Item = isset($params['Item']) ? $params['Item'] : null;
        $defaultParams = array(
            'Item' => $Item, 
            'parentUrl' => $this->url, 
            'caption' => $Item->id ? $this->view->_('EDITING_USER') : $this->view->_('CREATING_USER'),
            'children' => array()
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
        $Field = new RAASField(array('type' => 'email', 'name' => 'email', 'caption' => $this->view->_('EMAIL'), 'required' => true));
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
        $Field = new RAASField(array('type' => 'checkbox', 'name' => 'vis', 'caption' => $this->view->_('ACTIVATED')));
        $defaultParams['children']['vis'] = $Field;

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
}