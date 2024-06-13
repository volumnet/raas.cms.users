<?php
/**
 * Форма редактирования блока регистрации
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\Field as RAASField;
use RAAS\FormTab;
use RAAS\CMS\EditBlockForm;
use RAAS\CMS\Form as CMSForm;
use RAAS\CMS\InterfaceField;
use RAAS\CMS\Snippet;

/**
 * Форма редактирования блока регистрации
 */
class EditBlockRegisterForm extends EditBlockForm
{
    const DEFAULT_BLOCK_CLASSNAME = Block_Register::class;

    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return View_Web::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = [])
    {
        $params['view'] = Module::i()->view;
        parent::__construct($params);
    }


    protected function getCommonTab(): FormTab
    {
        $tab = parent::getCommonTab();
        $tab->children['form_id'] = new RAASField([
            'type' => 'select',
            'name' => 'form_id',
            'caption' => $this->view->_('REGISTRATION_FORM'),
            'children' => ['Set' => CMSForm::getSet()],
            'required' => true,
        ]);
        $tab->children['email_as_login'] = new RAASField([
            'type' => 'checkbox',
            'name' => 'email_as_login',
            'caption' => $this->view->_('USE_EMAIL_AS_LOGIN'),
        ]);
        $tab->children['notify_about_edit'] = new RAASField([
            'type' => 'checkbox',
            'name' => 'notify_about_edit',
            'caption' => $this->view->_('NOTIFY_ADMIN_ABOUT_EDIT'),
        ]);
        $tab->children['allow_edit_social'] = new RAASField([
            'type' => 'checkbox',
            'name' => 'allow_edit_social',
            'caption' => $this->view->_('ALLOW_EDIT_SOCIAL'),
        ]);
        $tab->children['activation_type'] = new RAASField([
            'type' => 'select',
            'name' => 'activation_type',
            'caption' => $this->view->_('ACTIVATION_TYPE'),
            'children' => [
                [
                    'value' => Block_Register::ACTIVATION_TYPE_ADMINISTRATOR,
                    'caption' => $this->view->_('ACTIVATION_BY_ADMINISTRATOR'),
                ],
                [
                    'value' => Block_Register::ACTIVATION_TYPE_USER,
                    'caption' => $this->view->_('ACTIVATION_BY_USER'),
                ],
                [
                    'value' => Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED,
                    'caption' => $this->view->_('ALREADY_ACTIVATED'),
                ],
            ],
        ]);
        $tab->children['allow_to'] = new RAASField([
            'type' => 'select',
            'name' => 'allow_to',
            'caption' => $this->view->_('ALLOW_TO'),
            'children' => [
                [
                    'value' => Block_Register::ALLOW_TO_UNREGISTERED,
                    'caption' => $this->view->_('ALLOW_TO_UNREGISTERED'),
                ],
                [
                    'value' => Block_Register::ALLOW_TO_ALL,
                    'caption' => $this->view->_('ALLOW_TO_ALL'),
                ],
                [
                    'value' => Block_Register::ALLOW_TO_REGISTERED,
                    'caption' => $this->view->_('ALLOW_TO_REGISTERED'),
                ],
            ],
        ]);
        $tab->children['redirect_url'] = new RAASField([
            'name' => 'redirect_url',
            'caption' => $this->view->_('REDIRECT_URL'),
        ]);
        $tab->children['widget_id'] = $this->getWidgetField();
        return $tab;
    }


    protected function getServiceTab(): FormTab
    {
        $tab = parent::getServiceTab();
        $tab->children['interface_id'] = $this->getInterfaceField();
        return $tab;
    }
}
