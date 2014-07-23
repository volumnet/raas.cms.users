<?php
namespace RAAS\CMS\Users;
use \RAAS\Field as RAASField;
use \RAAS\CMS\Form as CMSForm;
use \RAAS\CMS\EditBlockForm;
use \RAAS\CMS\Snippet;

class EditBlockRegisterForm extends EditBlockForm
{
    public function __construct(array $params)
    {
        $params['view'] = Module::i()->view;
        parent::__construct($params);
    }


    protected function getInterfaceField()
    {
        $field = parent::getInterfaceField();
        $snippet = Snippet::importByURN('__RAAS_users_register_interface');
        if ($snippet) {
            $field->default = (int)$snippet->id;
        }
        return $field;
    }


    protected function getCommonTab()
    {
        $tab = parent::getCommonTab();
        $tab->children[] = new RAASField(array(
            'type' => 'select', 
            'name' => 'register_form_id', 
            'caption' => $this->_view->_('REGISTRATION_FORM'), 
            'children' => array('Set' => CMSForm::getSet()), 
            'required' => true,
        ));
        $tab->children[] = new RAASField(array(
            'type' => 'select', 
            'name' => 'edit_form_id', 
            'caption' => $this->_view->_('PROFILE_DATA_FORM'), 
            'children' => array('Set' => CMSForm::getSet()), 
            'placeholder' => '--'
        ));
        $tab->children[] = new RAASField(array('type' => 'checkbox', 'name' => 'email_as_login', 'caption' => $this->_view->_('USE_EMAIL_AS_LOGIN')));
        $tab->children[] = new RAASField(array('type' => 'checkbox', 'name' => 'allow_edit_login', 'caption' => $this->_view->_('ALLOW_EDIT_LOGIN')));
        $tab->children[] = new RAASField(array('type' => 'checkbox', 'name' => 'allow_edit_email', 'caption' => $this->_view->_('ALLOW_EDIT_EMAIL')));
        $tab->children[] = new RAASField(array('type' => 'checkbox', 'name' => 'allow_edit_social', 'caption' => $this->_view->_('ALLOW_EDIT_SOCIAL')));
        $tab->children[] = new RAASField(array(
            'type' => 'select', 
            'name' => 'activation_type', 
            'caption' => $this->_view->_('ACTIVATION_TYPE'),
            'children' => array(
                array('value' => Block_Register::ACTIVATION_TYPE_ADMINISTRATOR, 'caption' => $this->_view->_('ACTIVATION_BY_ADMINISTRATOR')),
                array('value' => Block_Register::ACTIVATION_TYPE_USER, 'caption' => $this->_view->_('ACTIVATION_BY_USER')),
                array('value' => Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED, 'caption' => $this->_view->_('ALREADY_ACTIVATED')),
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