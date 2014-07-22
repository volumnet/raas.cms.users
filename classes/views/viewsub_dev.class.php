<?php
namespace RAAS\CMS\Users;
use \RAAS\Table as Table;
use \RAAS\Column as Column;
use \RAAS\Row as Row;
use \RAAS\CMS\FieldsTable;
use \RAAS\CMS\User_Field;

class ViewSub_Dev extends \RAAS\Abstract_Sub_View
{
    protected static $instance;
    
    public function fields(array $IN = array())
    {
        $IN['Table'] = new FieldsTable(array_merge($IN, array('view' => $this, 'editAction' => 'edit_field', 'ctxMenu' => 'getFieldContextMenu')));
        $this->assignVars($IN);
        $this->title = $this->_('USERS_FIELDS');
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->contextmenu = array(array('name' => $this->_('CREATE_FIELD'), 'href' => $this->url . '&action=edit_field', 'icon' => 'plus'));
        $this->template = $IN['Table']->template;
    }

    
    public function edit_field(array $IN = array())
    {
        $this->js[] = Module::i()->parent->view->publicURL . '/dev_edit_field.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('USERS_FIELDS'), 'href' => $this->url . '&action=fields');
        $this->stdView->stdEdit($IN, 'getFieldContextMenu');
    }


    public function devMenu()
    {
        $submenu = array();
        $submenu[] = array(
            'href' => $this->url . '&action=fields', 
            'name' => $this->_('USERS_FIELDS'),
            'active' => (in_array($this->action, array('fields', 'edit_field')) && !$this->moduleName)
        );
        return $submenu;
    }
    
    
    public function getFieldContextMenu(User_Field $Item, $i = 0, $c = 0) 
    {
        $arr = array();
        if ($Item->id) {
            $arr[] = array(
                'name' => $this->_('SHOW_IN_TABLE'), 
                'href' => $this->url . '&action=show_in_table_field&id=' . (int)$Item->id . '&back=1', 
                'icon' => $Item->show_in_table ? 'ok' : '',
            );
        }
        $arr = array_merge(
            $arr, 
            $this->stdView->stdContextMenu($Item, $i, $c, 'edit_field', 'fields', 'delete_field', 'move_up_field', 'move_down_field')
        );
        return $arr;
    }
}