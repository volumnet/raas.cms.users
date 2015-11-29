<?php
namespace RAAS\CMS\Users;
use \RAAS\FormTab;

class EditGroupForm extends \RAAS\Form
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

        $defaultParams = array(
            'Item' => $Item,
            'caption' => $Item->id ? htmlspecialchars($Item->name) : $this->view->_('ADD_GROUP'),
            'parentUrl' => urldecode(\SOME\HTTP::queryString('id=%s&action=')) . '#groups',
            'children' => array(
                array('name' => 'name', 'caption' => $this->view->_('NAME'), 'required' => 'required'),
                array('name' => 'urn', 'caption' => $this->view->_('URN')),
                array('type' => 'textarea', 'name' => 'description', 'caption' => $this->view->_('DESCRIPTION')),
            )
        );
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}