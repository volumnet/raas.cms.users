<?php
/**
 * Форма редактирования блока восстановления пароля
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\Field as RAASField;
use RAAS\FormTab;
use RAAS\Option;
use RAAS\OptionCollection;
use RAAS\CMS\Form as CMSForm;
use RAAS\CMS\EditBlockForm;
use RAAS\CMS\InterfaceField;
use RAAS\CMS\Snippet;
use RAAS\CMS\Snippet_Folder;

/**
 * Форма редактирования блока восстановления пароля
 */
class EditBlockRecoveryForm extends EditBlockForm
{
    const DEFAULT_BLOCK_CLASSNAME = Block_Recovery::class;

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
        $field = new InterfaceField([
            'name' => 'notification_id',
            'required' => true,
            'caption' => $this->view->_('PASSWORD_RECOVERY_NOTIFICATION'),
            'default' => Snippet::importByURN('__raas_users_recovery_notify')->id,
        ]);
        $tab->children['notification_id'] = $field;
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
