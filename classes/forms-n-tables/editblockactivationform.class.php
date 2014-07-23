<?php
namespace RAAS\CMS\Users;
use \RAAS\Field as RAASField;
use \RAAS\CMS\Form as CMSForm;
use \RAAS\CMS\EditBlockForm;
use \RAAS\CMS\Snippet;

class EditBlockActivationForm extends EditBlockForm
{
    public function __construct(array $params)
    {
        $params['view'] = Module::i()->view;
        parent::__construct($params);
    }


    protected function getInterfaceField()
    {
        $field = parent::getInterfaceField();
        $snippet = Snippet::importByURN('__RAAS_users_activation_interface');
        if ($snippet) {
            $field->default = (int)$snippet->id;
        }
        return $field;
    }


    protected function getCommonTab()
    {
        $tab = parent::getCommonTab();
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