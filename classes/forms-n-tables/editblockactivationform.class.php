<?php
/**
 * Форма редактирования блока активации
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
 * Форма редактирования блока активации
 */
class EditBlockActivationForm extends EditBlockForm
{
    const DEFAULT_BLOCK_CLASSNAME = Block_Activation::class;

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
