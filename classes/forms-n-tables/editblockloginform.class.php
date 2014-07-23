<?php
namespace RAAS\CMS\Users;
use \RAAS\Field as RAASField;
use \RAAS\CMS\Form as CMSForm;
use \RAAS\CMS\EditBlockForm;
use \RAAS\CMS\Snippet;

class EditBlockLogInForm extends EditBlockForm
{
    public function __construct(array $params)
    {
        $params['view'] = Module::i()->view;
        parent::__construct($params);
    }


    protected function getInterfaceField()
    {
        $field = parent::getInterfaceField();
        $snippet = Snippet::importByURN('__RAAS_users_login_interface');
        if ($snippet) {
            $field->default = (int)$snippet->id;
        }
        return $field;
    }


    protected function getCommonTab()
    {
        $tab = parent::getCommonTab();
        $tab->children[] = new RAASField(array('type' => 'checkbox', 'name' => 'email_as_login', 'caption' => $this->_view->_('USE_EMAIL_AS_LOGIN')));
        $tab->children[] = new RAASField(array(
            'type' => 'select', 
            'name' => 'social_login_type', 
            'caption' => $this->_view->_('SOCIAL_LOGIN_TYPE'),
            'children' => array(
                array('value' => Block_Login::SOCIAL_LOGIN_NONE, 'caption' => $this->_view->_('_NONE')),
                array('value' => Block_Login::SOCIAL_LOGIN_ONLY_REGISTERED, 'caption' => $this->_view->_('ONLY_REGISTERED')),
                array('value' => Block_Login::SOCIAL_LOGIN_QUICK_REGISTER, 'caption' => $this->_view->_('QUICK_REGISTER')),
            ),
        ));
        $tab->children[] = new RAASField(array(
            'type' => 'select', 
            'name' => 'password_save_type', 
            'caption' => $this->_view->_('PASSWORD_SAVE_TYPE'),
            'children' => array(
                array('value' => Block_Login::SAVE_PASSWORD_NONE, 'caption' => $this->_view->_('_NONE')),
                array('value' => Block_Login::SAVE_PASSWORD_SAVE_PASSWORD, 'caption' => $this->_view->_('CHECKBOX_SAVE_PASSWORD')),
                array('value' => Block_Login::SAVE_PASSWORD_FOREIGN_COMPUTER, 'caption' => $this->_view->_('CHECKBOX_FOREIGN_COMPUTER')),
            ),
        ));
        $tab->children[] = $this->getWidgetField();
        return $tab;
    }


    protected function getServiceTab()
    {
        $tab = parent::getServiceTab();
        $tab->children[] = $this->getInterfaceField();
        return $tab;
    }
}