<?php
/**
 * Форма редактирования блока входа в систему
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\Field as RAASField;
use RAAS\FormTab;
use RAAS\CMS\Form as CMSForm;
use RAAS\CMS\EditBlockForm;
use RAAS\CMS\InterfaceField;
use RAAS\CMS\Snippet;

/**
 * Форма редактирования блока входа в систему
 */
class EditBlockLogInForm extends EditBlockForm
{
    const DEFAULT_BLOCK_CLASSNAME = Block_LogIn::class;

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
        $tab->children['email_as_login'] = new RAASField([
            'type' => 'checkbox',
            'name' => 'email_as_login',
            'caption' => $this->view->_('USE_EMAIL_AS_LOGIN'),
        ]);
        $tab->children['social_login_type'] = new RAASField([
            'type' => 'select',
            'name' => 'social_login_type',
            'caption' => $this->view->_('SOCIAL_LOGIN_TYPE'),
            'children' => [
                [
                    'value' => Block_Login::SOCIAL_LOGIN_NONE,
                    'caption' => $this->view->_('_NONE'),
                ],
                [
                    'value' => Block_Login::SOCIAL_LOGIN_ONLY_REGISTERED,
                    'caption' => $this->view->_('ONLY_REGISTERED'),
                ],
                [
                    'value' => Block_Login::SOCIAL_LOGIN_QUICK_REGISTER,
                    'caption' => $this->view->_('QUICK_REGISTER'),
                ],
            ],
        ]);
        $tab->children['password_save_type'] = new RAASField([
            'type' => 'select',
            'name' => 'password_save_type',
            'caption' => $this->view->_('PASSWORD_SAVE_TYPE'),
            'children' => [
                [
                    'value' => Block_Login::SAVE_PASSWORD_NONE,
                    'caption' => $this->view->_('_NONE'),
                ],
                [
                    'value' => Block_Login::SAVE_PASSWORD_SAVE_PASSWORD,
                    'caption' => $this->view->_('CHECKBOX_SAVE_PASSWORD'),
                ],
                [
                    'value' => Block_Login::SAVE_PASSWORD_FOREIGN_COMPUTER,
                    'caption' => $this->view->_('CHECKBOX_FOREIGN_COMPUTER'),
                ],
            ],
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
