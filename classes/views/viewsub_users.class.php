<?php
namespace RAAS\CMS\Users;

class ViewSub_Users extends \RAAS\Abstract_Sub_View
{
    protected static $instance;
    
    public function edit_field(array $IN = array())
    {
        $this->js[] = \RAAS\CMS\Package::i()->view->publicURL . '/dev_edit_field.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('USERS_FIELDS'), 'href' => $this->url . '&action=fields');
        $this->stdView->stdEdit($IN, 'getUserFieldContextMenu');
    }


    public function getUserFieldContextMenu(Page_Field $Item, $i = 0, $c = 0) 
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
    
    
    public function fields(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new Table(array(
            'columns' => array(
                'name' => array(
                    'caption' => $this->_('NAME'), 
                    'callback' => function($row) use ($view) { 
                        return '<a href="' . $view->url . '&action=edit_page_field&id=' . (int)$row->id . '">' . htmlspecialchars($row->name) . '</a>'; 
                    }
                ),
                'urn' => array(
                    'caption' => $this->_('URN'),
                    'callback' => function($row) use ($view) { 
                        return htmlspecialchars($row->urn) 
                             . ($row->multiple ? '<strong title="' . $view->_('MULTIPLE') . '">[]</strong>' : '') 
                             . ($row->required ? ' <span class="text-error" title="' . $view->_('REQUIRED') . '">*</span>' : ''); 
                    }
                ),
                'datatype' => array(
                    'caption' => $this->_('DATATYPE'), 
                    'callback' => function($row) use ($view) { return htmlspecialchars($view->_('DATATYPE_' . str_replace('-', '_', strtoupper($row->datatype)))); }
                ),
                'show_in_table' => array(
                    'caption' => $this->_('SHOW_IN_TABLE'),
                    'title' => $this->_('SHOW_IN_TABLE'),
                    'callback' => function($row) { return $row->show_in_table ? '<i class="icon-ok"></i>' : ''; }
                ),
                ' ' => array('callback' => function ($row, $i) use ($view, $IN) { return rowContextMenu($view->getPageFieldContextMenu($row, $i, count($IN['Set']))); })
            ),
            'Set' => $IN['Set'],
            'Pages' => $IN['Pages'],
        ));
        $this->assignVars($IN);
        $this->title = $this->_('PAGES_FIELDS');
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->contextmenu = array(array('name' => $this->_('CREATE_FIELD'), 'href' => $this->url . '&action=edit_page_field', 'icon' => 'plus'));
        $this->template = $IN['Table']->template;
    }
}