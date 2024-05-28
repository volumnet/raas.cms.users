<?php
/**
 * Форма редактирования блока активации
 */
namespace RAAS\CMS\Users;

use RAAS\Field as RAASField;
use RAAS\FormTab;
use RAAS\CMS\Form as CMSForm;
use RAAS\CMS\EditBlockForm;
use RAAS\CMS\Snippet;

/**
 * Форма редактирования блока активации
 */
class EditBlockActivationForm extends EditBlockForm
{
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


    public function __construct(array $params)
    {
        $params['view'] = Module::i()->view;
        parent::__construct($params);
    }


    protected function getInterfaceField(): RAASField
    {
        $field = parent::getInterfaceField();
        $snippet = Snippet::importByURN('__RAAS_users_activation_interface');
        if ($snippet) {
            $field->default = (int)$snippet->id;
        }
        return $field;
    }


    protected function getCommonTab(): FormTab
    {
        $tab = parent::getCommonTab();
        $tab->children[] = $this->getWidgetField();
        return $tab;
    }


    protected function getServiceTab(): FormTab
    {
        $tab = parent::getServiceTab();
        $tab->children[] = $this->getInterfaceField();
        return $tab;
    }
}
