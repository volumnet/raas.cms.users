<?php
namespace RAAS\CMS\User;
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
        unset($params['view']);
        $Item = isset($params['Item']) ? $params['Item'] : null;
        $defaultParams = array(
            'Item' => $Item, 
            'parentUrl' => $this->url, 
            'caption' => $Item->id ? $this->view->_('EDITING_USER') : $this->view->_('CREATING_USER'),
            'children' => array()
        );
        
        // Логин
        $Field = new Field(array('name' => 'login', 'caption' => $this->view->_('LOGIN'), 'required' => 'required'));
        if ($Item->id == $this->model->user->id) {
            $Field->readonly = 'readonly';
            $Field->export = 'is_null';
        } else {
            $Field->required = 'required';
            $Field->check = function($Field) use ($t) {
                $localError = $Field->getErrors();
                if (!$localError) {
                    if ($t->model->checkLoginExists($_POST[$Field->name], $Field->Form->Item->id)) {
                        $localError[] = array('name' => 'INVALID', 'value' => $Field->name, 'description' => $t->view->_('ERR_LOGIN_EXISTS'));
                    }
                }
                return $localError;
           };
        }
        $defaultParams['children']['login'] = $Field;

        // Пароль
        $Field = new Field(array(
            'type' => 'password', 
            'name' => 'password', 
            'caption' => $this->view->_('PASSWORD'),
            'confirm' => true, 
            'export' => function($Field) use ($t) { 
                if ($_POST[$Field->name]) {
                    $Field->Form->Item->password_md5 = $t->application->md5It(trim($_POST[$Field->name])); 
                }
            }
        ));
        if (!$Item->id) {
            $Field->required = 'required';
        }
        $defaultParams['children']['password'] = $Field;
        
        // E-mail
        $defaultParams['children']['email'] = new Field(array('type' => 'email', 'name' => 'email', 'caption' => $this->view->_('EMAIL')));

        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}