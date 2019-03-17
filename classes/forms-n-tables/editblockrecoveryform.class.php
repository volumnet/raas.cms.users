<?php
namespace RAAS\CMS\Users;

use RAAS\Field as RAASField;
use RAAS\CMS\Form as CMSForm;
use RAAS\CMS\EditBlockForm;
use RAAS\CMS\Snippet;
use RAAS\CMS\Snippet_Folder;
use RAAS\Option;
use RAAS\OptionCollection;

class EditBlockRecoveryForm extends EditBlockForm
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


    protected function getInterfaceField()
    {
        $field = parent::getInterfaceField();
        $snippet = Snippet::importByURN('__raas_users_recovery_interface');
        if ($snippet) {
            $field->default = (int)$snippet->id;
        }
        return $field;
    }


    protected function getCommonTab()
    {
        $tab = parent::getCommonTab();
        $snippet = Snippet::importByURN('__raas_users_recovery_notify');
        $wf = function(Snippet_Folder $x) use (&$wf) {
            $temp = array();
            foreach ($x->children as $row) {
                if ($row->urn != '__raas_views') {
                    $o = array('value' => '', 'caption' => $row->name, 'disabled' => 'disabled');
                    $o['children'] = $wf($row);
                    $temp[] = $o;
                }
            }
            foreach ($x->snippets as $row) {
                $temp[] = array('value' => $row->id, 'caption' => $row->name);
            }
            return $temp;
        };
        $field = new RAASField(array(
            'type' => 'select',
            'class' => 'input-xxlarge',
            'name' => 'notification_id',
            'required' => true,
            'caption' => $this->view->_('PASSWORD_RECOVERY_NOTIFICATION'),
            'default' => $snippet->id,
            'children' => $wf(new Snippet_Folder())
        ));
        $tab->children[] = $field;
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
