<?php
namespace RAAS\CMS\Users;
use \RAAS\Table as Table;
use \RAAS\Column as Column;
use \RAAS\Row as Row;

class ViewSub_Dev extends \RAAS\Abstract_Sub_View
{
    protected static $instance;
    
    public function dictionaries(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new Table(array(
            'Set' => $IN['Set'],
            'Pages' => $IN['Pages']
        ));
        $IN['Table']->columns['name'] = array(
            'caption' => $this->_('NAME'), 
            'callback' => function($row) use ($view) { 
                return '<a href="' . $view->url . '&action=dictionaries&id=' . (int)$row->id . '" class="' . (!$row->vis ? ' muted' : '') . ($row->pvis ? '' : ' cms-inpvis') . '">'
                     .    htmlspecialchars($row->name) 
                     . '</a>'; 
            }
        );
        if ($IN['Item']->id) {
            $IN['Table']->columns['urn'] = array(
                'caption' => $this->_('VALUE'),
                'callback' => function($row) { 
                    return '<span class="' . (!$row->vis ? ' muted' : '') . ($row->pvis ? '' : ' cms-inpvis') . '">' . htmlspecialchars($row->urn) . '</span>'; 
                }
            );
        }
        $IN['Table']->columns[' '] = array(
            'callback' => function($row, $i) use ($view, $IN) { return rowContextMenu($view->getDictionaryContextMenu($row, $i, count($IN['Set']))); }
        );
        if ($IN['Item']->id) {
            $IN['Table']->emptyString = $this->_('NO_NOTES_FOUND');
        }

        $this->assignVars($IN);
        $this->title = $IN['Item']->id ? $IN['Item']->name : $this->_('DICTIONARIES');
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('href' => $this->url . '&action=dictionaries', 'name' => $this->_('DICTIONARIES'));
        if ($IN['Item']->parents) {
            foreach ($IN['Item']->parents as $row) {
                $this->path[] = array('href' => $this->url . '&action=dictionaries&id=' . (int)$row->id, 'name' => $row->name);
            }
        }
        $this->contextmenu = $this->getDictionaryContextMenu($IN['Item']);
        $this->template = 'dev_dictionaries';
    }
    
    
    public function edit_dictionary(array $IN = array())
    {
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('href' => $this->url . '&action=dictionaries', 'name' => $this->_('DICTIONARIES'));
        if ($IN['Parent']->id) {
            if ($IN['Parent']->parents) {
                foreach ($IN['Parent']->parents as $row) {
                    $this->path[] = array('href' => $this->url . '&action=dictionaries&id=' . (int)$row->id, 'name' => $row->name);
                }
            }
            $this->path[] = array('href' => $this->url . '&action=dictionaries&id=' . (int)$IN['Parent']->id, 'name' => $IN['Parent']->name);
        }
        $this->stdView->stdEdit($IN, 'getDictionaryContextMenu');
    }
    
    
    public function move_dictionary(array $IN = array())
    {
        $this->assignVars($IN);
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('href' => $this->url . '&action=dictionaries', 'name' => $this->_('DICTIONARIES'));
        if ($IN['Item']->parents) {
            foreach ($IN['Item']->parents as $row) {
                $this->path[] = array('href' => $this->url . '&action=dictionaries' . '&id=' . (int)$row->id, 'name' => $row->name);
            }
        }
        $this->path[] = array('href' => $this->url . '&action=dictionaries' . '&id=' . (int)$IN['Item']->id, 'name' => $IN['Item']->name);
        $this->title = $this->_('MOVING_NOTE');
        $this->template = 'dev_move_dictionary';
    }
    
    
    public function menus(array $IN = array())
    {
        $this->assignVars($IN);
        $this->title = $IN['Item']->id ? $IN['Item']->name : $this->_('MENUS');
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        if ($IN['Item']->id) {
            $this->path[] = array('href' => $this->url . '&action=menus', 'name' => $this->_('MENUS'));
            if ($IN['Item']->parents) {
                foreach ($IN['Item']->parents as $row) {
                    $this->path[] = array('href' => $this->url . '&action=menus' . '&id=' . (int)$row->id, 'name' => $row->name);
                }
            }
        }
        $this->contextmenu = $this->getMenuContextMenu($IN['Item']);
        $this->template = 'dev_menus';
    }
    
    
    public function edit_menu(array $IN = array())
    {
        $this->js[] = $this->publicURL . '/dev_edit_menu.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        if ($IN['Parent']->id) {
            $this->path[] = array('href' => $this->url . '&action=menus', 'name' => $this->_('MENUS'));
            if ($IN['Parent']->parents) {
                foreach ($IN['Parent']->parents as $row) {
                    $this->path[] = array('href' => $this->url . '&action=menus' . '&id=' . (int)$row->id, 'name' => $row->name);
                }
            }
            $this->path[] = array('href' => $this->url . '&action=menus&id=' . (int)$IN['Parent']->id, 'name' => $IN['Parent']->name);
        }
        $this->stdView->stdEdit($IN, 'getMenuContextMenu');
    }
    
    
    public function move_menu(array $IN = array())
    {
        $this->assignVars($IN);
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('href' => $this->url . '&action=menus', 'name' => $this->_('MENUS'));
        if ($IN['Item']->parents) {
            foreach ($IN['Item']->parents as $row) {
                $this->path[] = array('href' => $this->url . '&action=menus' . '&id=' . (int)$row->id, 'name' => $row->name);
            }
        }
        $this->path[] = array('href' => $this->url . '&action=menus' . '&id=' . (int)$IN['Item']->id, 'name' => $IN['Item']->name);
        $this->title = $this->_('MOVING_NOTE');
        $this->template = 'dev_move_menu';
    }
    
    
    public function dev(array $IN = array())
    {
        $this->title = $this->_('DEVELOPMENT');
        $this->template = 'dev';
    }
    
    
    public function templates(array $IN = array())
    {
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->stdView->stdShowlist($IN, 'TEMPLATES', 'edit_template', 'getTemplateContextMenu', 'NO_TEMPLATES_FOUND', 'ADD_TEMPLATE');
    }
    
    
    public function edit_template(array $IN = array())
    {
        $this->js[] = $this->publicURL . '/dev_edit_template.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('TEMPLATES'), 'href' => $this->url . '&action=templates');
        $this->stdView->stdEdit($IN, 'getTemplateContextMenu');
    }
    
    
    public function snippets(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new SnippetsTable(array('view' => $this));
        $IN['Set'] = (array)$IN['Table']->Set;
        $this->assignVars($IN);
        $this->title = $this->_('SNIPPETS');
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->contextmenu = array(
            array('name' => $this->_('CREATE_SNIPPET'), 'href' => $this->url . '&action=edit_snippet', 'icon' => 'plus'),
            array('name' => $this->_('CREATE_SNIPPET_FOLDER'), 'href' => $this->url . '&action=edit_snippet_folder', 'icon' => 'plus'),
        );
        $this->template = $IN['Table']->template;
    }
    
    
    public function edit_snippet_folder(array $IN = array())
    {
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('SNIPPETS'), 'href' => $this->url . '&action=snippets');
        $this->stdView->stdEdit($IN, 'getSnippetFolderContextMenu');
    }
    
    
    public function edit_snippet(array $IN = array())
    {
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('SNIPPETS'), 'href' => $this->url . '&action=snippets');
        $this->stdView->stdEdit($IN, 'getSnippetContextMenu');
    }
    
    
    public function material_types(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new Table(array(
            'columns' => array(
                'name' => array(
                    'caption' => $this->_('NAME'), 
                    'callback' => function($row) use ($view) { 
                        return '<a href="' . $view->url . '&action=edit_material_type&id=' . (int)$row->id . '">' . htmlspecialchars($row->name) . '</a>'; 
                    }
                ),
                'urn' => array('caption' => $this->_('URN')),
                'global_type' => array(
                    'caption' => $this->_('IS_GLOBAL_TYPE'), 
                    'title' => $this->_('GLOBAL_MATERIALS'), 
                    'callback' => function($row) { return $row->global_type ? '<i class="icon-ok"></i>' : ''; }
                ),
                ' ' => array('callback' => function ($row) use ($view) { return rowContextMenu($view->getMaterialTypeContextMenu($row)); })
            ),
            'Set' => $IN['Set'],
            'Pages' => $IN['Pages'],
            'emptyString' => $this->_('NO_MATERIAL_TYPES_FOUND')
        ));
        $this->assignVars($IN);
        $this->title = $this->_('MATERIAL_TYPES');
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->contextmenu = array(array('name' => $this->_('CREATE_MATERIAL_TYPE'), 'href' => $this->url . '&action=edit_material_type', 'icon' => 'plus'));
        $this->template = $IN['Table']->template;

    }
    
    
    public function edit_material_type(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new Table(array(
            'columns' => array(
                'name' => array(
                    'caption' => $this->_('NAME'), 
                    'callback' => function($row) use ($view) { 
                        if ($row->id) {
                            return '<a href="' . $view->url . '&action=edit_material_field&id=' . (int)$row->id . '">' . htmlspecialchars($row->name) . '</a>'; 
                        } else {
                            return $row->name;
                        }
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
                ' ' => array(
                    'callback' => function ($row, $i) use ($view, $IN) { 
                        if ($row->id) {
                            return rowContextMenu($view->getMaterialFieldContextMenu($row, $i - 2, count($IN['Item']->fields))); 
                        }
                    }
                )
            ),
        ));
        $IN['Table']->Set[] = new Material_Field(array('name' => $this->_('NAME'), 'urn' => 'name', 'datatype' => 'text'));
        $IN['Table']->Set[] = new Material_Field(array('name' => $this->_('DESCRIPTION'), 'urn' => 'description', 'datatype' => 'htmlarea'));
        foreach ($IN['Item']->fields as $row) {
            $IN['Table']->Set[] = $row;
        }
        
        $this->assignVars($IN);
        $this->title = $IN['Form']->caption;
        $this->template = 'form_table';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('MATERIAL_TYPES'), 'href' => $this->url . '&action=material_types');
        $this->contextmenu = $this->getMaterialTypeContextMenu($IN['Item']);
    }
    
    
    public function edit_material_field(array $IN = array())
    {
        $this->js[] = $this->publicURL . '/dev_edit_field.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('MATERIAL_TYPES'), 'href' => $this->url . '&action=material_types');
        $this->path[] = array('name' => $IN['Parent']->name, 'href' => $this->url . '&action=edit_material_type&id=' . (int)$IN['Parent']->id);
        $this->stdView->stdEdit($IN, 'getMaterialFieldContextMenu');
    }
    
    
    public function forms(array $IN = array())
    {
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->stdView->stdShowlist($IN, 'FORMS', 'edit_form', 'getFormContextMenu', 'NO_FORMS_FOUND', 'CREATE_FORM');
    }
    
    
    public function edit_form(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new Table(array(
            'columns' => array(
                'name' => array(
                    'caption' => $this->_('NAME'), 
                    'callback' => function($row) use ($view) { 
                        if ($row->id) {
                            return '<a href="' . $view->url . '&action=edit_form_field&id=' . (int)$row->id . '">' . htmlspecialchars($row->name) . '</a>'; 
                        } else {
                            return $row->name;
                        }
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
                ' ' => array(
                    'callback' => function ($row, $i) use ($view, $IN) { 
                        if ($row->id) {
                            return rowContextMenu($view->getFormFieldContextMenu($row, $i, count($IN['Item']->fields))); 
                        }
                    }
                )
            ),
        ));
        foreach ($IN['Item']->fields as $row) {
            $IN['Table']->Set[] = $row;
        }
        $this->assignVars($IN);
        $this->title = $IN['Form']->caption;
        $this->template = 'form_table';
        $this->js[] = $this->publicURL . '/dev_edit_form.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('FORMS'), 'href' => $this->url . '&action=forms');
        $this->contextmenu = $this->getFormContextMenu($IN['Item']);
    }
    
    
    public function edit_form_field(array $IN = array())
    {
        $this->js[] = $this->publicURL . '/dev_edit_field.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('FORMS'), 'href' => $this->url . '&action=forms');
        $this->path[] = array('name' => $IN['Parent']->name, 'href' => $this->url . '&action=edit_form&id=' . (int)$IN['Parent']->id);
        $this->stdView->stdEdit($IN, 'getFormFieldContextMenu');
    }
    
    
    public function pages_fields(array $IN = array())
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
    
    
    public function edit_page_field(array $IN = array())
    {
        $this->js[] = $this->publicURL . '/dev_edit_field.js';
        $this->path[] = array('name' => $this->_('DEVELOPMENT'), 'href' => $this->url);
        $this->path[] = array('name' => $this->_('PAGES_FIELDS'), 'href' => $this->url . '&action=pages_fields');
        $this->stdView->stdEdit($IN, 'getPageFieldContextMenu');
    }


    public function devMenu()
    {
        $submenu = array();
        $submenu[] = array(
            'href' => $this->url . '&action=templates', 
            'name' => $this->_('TEMPLATES'), 
            'active' => (in_array($this->action, array('templates', 'edit_template')) && !$this->moduleName)
        );
        $submenu[] = array(
            'href' => $this->url . '&action=dictionaries', 
            'name' => $this->_('DICTIONARIES'),
            'active' => (in_array($this->action, array('dictionaries', 'edit_dictionary', 'move_dictionary')) && !$this->moduleName),
            'submenu' => (
                in_array($this->action, array('dictionaries', 'edit_dictionary', 'move_dictionary')) ? 
                $this->pagesMenu(new Dictionary(), new Dictionary($this->id ? $this->id : (isset($this->nav['pid']) ? $this->nav['pid'] : 0))) : 
                null
            )
        );
        $submenu[] = array(
            'href' => $this->url . '&action=snippets', 
            'name' => $this->_('SNIPPETS'), 
            'active' => (in_array($this->action, array('snippets', 'edit_snippet', 'edit_snippet_folder', 'copy_snippet')) && !$this->moduleName)
        );
        $submenu[] = array(
            'href' => $this->url . '&action=material_types', 
            'name' => $this->_('MATERIAL_TYPES'),
            'active' => (in_array($this->action, array('material_types', 'edit_material_type', 'edit_material_field')) && !$this->moduleName)
        );
        $submenu[] = array(
            'href' => $this->url . '&action=pages_fields', 
            'name' => $this->_('PAGES_FIELDS'),
            'active' => (in_array($this->action, array('pages_fields', 'edit_page_field')) && !$this->moduleName)
        );
        $submenu[] = array(
            'href' => $this->url . '&action=forms', 
            'name' => $this->_('FORMS'),
            'active' => (in_array($this->action, array('forms', 'edit_form', 'edit_form_field')) && !$this->moduleName)
        );
        $submenu[] = array(
            'href' => $this->url . '&action=menus', 
            'name' => $this->_('MENUS'),
            'active' => (in_array($this->action, array('menus', 'edit_menu', 'move_menu')) && !$this->moduleName),
            'submenu' => (
                in_array($this->action, array('menus', 'edit_menu', 'move_menu')) ? 
                $this->pagesMenu(new Menu(), new Menu($this->id ? $this->id : (isset($this->nav['pid']) ? $this->nav['pid'] : 0))) : 
                null
            )
        );
        foreach ($this->model->modules as $module) {
            $NS = \SOME\Namespaces::getNS($module);
            $sub_classname = $NS . '\\Sub_Dev';
            $view_classname = $NS . '\\ViewSub_Dev';
            if (method_exists($view_classname, 'devMenu')) {
                $row = $sub_classname::i();
                $temp = (array)$row->view->devMenu();
                $submenu = array_merge($submenu, $temp);
            }
        }
        return $submenu;
    }
    
    
    public function getTemplateContextMenu(Template $Item)
    {
        return $this->stdView->stdContextMenu($Item, 0, 0, 'edit_template', 'templates', 'delete_template');
    }
    
    
    public function getDictionaryContextMenu(Dictionary $Item, $i = 0, $c = 0) 
    {
        $arr = array();
        if ($Item->id) {
            $edit = ($this->action == 'edit_dictionary');
            $showlist = ($this->action == 'dictionaries');
            if ($this->id == $Item->id) {
                $arr[] = array('href' => $this->url . '&action=edit_dictionary&pid=' . (int)$Item->id, 'name' => $this->_('CREATE_SUBNOTE'), 'icon' => 'plus');
            }
            if ($edit) {
                $arr[] = array('href' => $this->url . '&action=dictionaries&id=' . (int)$Item->id, 'name' => htmlspecialchars($Item->name), 'icon' => 'th-list');
            }
            $arr[] = array(
                'name' => $Item->vis ? $this->_('VISIBLE') : '<span class="muted">' . $this->_('INVISIBLE') . '</span>', 
                'href' => $this->url . '&action=chvis_dictionary&id=' . (int)$Item->id . '&back=1', 
                'icon' => $Item->vis ? 'ok' : '',
                'title' => $this->_($Item->vis ? 'HIDE' : 'SHOW')
            );
            if (!$Item->parent->id || ($Item->parent->orderby == 'priority')) {
                if ($i) {
                    $arr[] = array(
                        'href' => $this->url . '&action=move_up_dictionary&id=' . (int)$Item->id . ($edit || $showlist ? '' : '&back=1'), 'name' => $this->_('MOVE_UP'), 'icon' => 'arrow-up'
                    );
                }
                if ($i < $c - 1) {
                    $arr[] = array(
                        'href' => $this->url . '&action=move_down_dictionary&id=' . (int)$Item->id . ($edit || $showlist ? '' : '&back=1'), 'name' => $this->_('MOVE_DOWN'), 'icon' => 'arrow-down'
                    );
                }
            }
            if ($this->action != 'move_dictionary') {
                $arr[] = array('href' => $this->url . '&action=move_dictionary&id=' . (int)$Item->id, 'name' => $this->_('MOVE'), 'icon' => 'share-alt');
            }
            $arr = array_merge($arr, $this->stdView->stdContextMenu($Item, 0, 0, 'edit_dictionary', 'dictionaries', 'delete_dictionary'));
        } elseif (!$edit) {
            $arr[] = array('href' => $this->url . '&action=edit_dictionary', 'name' => $this->_('CREATE_NOTE'), 'icon' => 'plus');
        }
        return $arr;
    }
    
    
    public function getSnippetFolderContextMenu(Snippet_Folder $Item)
    {
        if (!$Item->locked) {
            $arr = $this->stdView->stdContextMenu($Item, 0, 0, 'edit_snippet_folder', 'snippets', 'delete_snippet_folder');
        }
        return $arr;
    }
    
    
    public function getSnippetContextMenu(Snippet $Item)
    {
        if (!$Item->locked) {
            $arr = $this->stdView->stdContextMenu($Item, 0, 0, 'edit_snippet', 'snippets', 'delete_snippet');
        }
        if ($Item->id) {
            $arr[] = array('href' => $this->url . '&action=copy_snippet&id=' . (int)$Item->id, 'name' => $this->_('COPY'), 'icon' => 'tags');
        }
        return $arr;
    }
    
    
    public function getMaterialTypeContextMenu(Material_Type $Item)
    {
        $arr = array();
        if ($Item->id) {
            if ($this->action == 'edit_material_type') {
                $arr[] = array('href' => $this->url . '&action=edit_material_field&pid=' . (int)$Item->id, 'name' => $this->_('CREATE_FIELD'), 'icon' => 'plus');
            }
        }
        $arr = array_merge($arr, $this->stdView->stdContextMenu($Item, 0, 0, 'edit_material_type', 'material_types', 'delete_material_type'));
        return $arr;
    }
    
    
    public function getMaterialFieldContextMenu(Material_Field $Item, $i = 0, $c = 0) 
    {
        $arr = array();
        if ($Item->id) {
            $arr[] = array(
                'name' => $this->_('SHOW_IN_TABLE'), 
                'href' => $this->url . '&action=show_in_table_material_field&id=' . (int)$Item->id . '&back=1', 
                'icon' => $Item->show_in_table ? 'ok' : '',
            );
        }
        $arr = array_merge(
            $arr, 
            $this->stdView->stdContextMenu(
                $Item, $i, $c, 'edit_material_field', 'material_types', 'delete_material_field', 'move_up_material_field', 'move_down_material_field'
            )
        );
        return $arr;
    }
    
    
    public function getPageFieldContextMenu(Page_Field $Item, $i = 0, $c = 0) 
    {
        $arr = array();
        if ($Item->id) {
            $arr[] = array(
                'name' => $this->_('SHOW_IN_TABLE'), 
                'href' => $this->url . '&action=show_in_table_page_field&id=' . (int)$Item->id . '&back=1', 
                'icon' => $Item->show_in_table ? 'ok' : '',
            );
        }
        $arr = array_merge(
            $arr, 
            $this->stdView->stdContextMenu($Item, $i, $c, 'edit_page_field', 'pages_fields', 'delete_page_field', 'move_up_page_field', 'move_down_page_field')
        );
        return $arr;
    }
    
    
    public function getFormContextMenu(Form $Item) 
    {
        $arr = array();
        if ($Item->id &&$this->action == 'edit_form') {
            $arr[] = array('href' => $this->url . '&action=edit_form_field&pid=' . (int)$Item->id, 'name' => $this->_('CREATE_FIELD'), 'icon' => 'plus');
        }
        $arr = array_merge($arr, $this->stdView->stdContextMenu($Item, $i, $c, 'edit_form', 'forms', 'delete_form'));
        return $arr;
    }
    
    
    public function getFormFieldContextMenu(Form_Field $Item, $i = 0, $c = 0) 
    {
        $arr = array();
        if ($Item->id) {
            $arr[] = array(
                'name' => $this->_('SHOW_IN_TABLE'), 
                'href' => $this->url . '&action=show_in_table_form_field&id=' . (int)$Item->id . '&back=1', 
                'icon' => $Item->show_in_table ? 'ok' : '',
            );
        }
        $arr = array_merge(
            $arr, 
            $this->stdView->stdContextMenu($Item, $i, $c, 'edit_form_field', 'pages_fields', 'delete_form_field', 'move_up_form_field', 'move_down_form_field')
        );
        return $arr;
    }
    
    
    public function getMenuContextMenu(Menu $Item) 
    {
        $arr = array();
        if ($Item->id) {
            $edit = ($this->action == 'edit_menu');
            $showlist = ($this->action == 'menus');
            if ($this->id == $Item->id) {
                $arr[] = array('href' => $this->url . '&action=edit_menu&pid=' . (int)$Item->id, 'name' => $this->_('CREATE_SUBNOTE'), 'icon' => 'plus');
            }
            if ($Item->vis) {
                $arr[] = array(
                    'name' => $this->_('VISIBLE'), 
                    'href' => $this->url . '&action=chvis_menu&id=' . (int)$Item->id . '&back=1', 
                    'icon' => 'ok',
                    'title' => $this->_('HIDE')
                );
            } else {
                $arr[] = array(
                    'name' => '<span class="muted">' . $this->_('INVISIBLE') . '</span>', 
                    'href' => $this->url . '&action=chvis_menu&id=' . (int)$Item->id . '&back=1', 
                    'icon' => '',
                    'title' => $this->_('SHOW')
                );
            }
            if ($this->action != 'move_menu') {
                $arr[] = array('href' => $this->url . '&action=move_menu&id=' . (int)$Item->id, 'name' => $this->_('MOVE'), 'icon' => 'share-alt');
            }
            if (($this->id == $Item->id) && ($Item->inherit > 0)) {
                $arr[] = array(
                    'href' => $this->url . '&action=realize_menu&id=' . (int)$Item->id . ($edit || $showlist ? '' : '&back=1'), 
                    'name' => $this->_('REALIZE'), 
                    'icon' => 'asterisk',
                    'onclick' => 'return confirm(\'' . $this->_('REALIZE_MENU_TEXT') . '\')'
                );
            }
            $arr = array_merge($arr, $this->stdView->stdContextMenu($Item, 0, 0, 'edit_menu', 'menus', 'delete_menu'));
        } elseif (!$edit) {
            $arr[] = array('href' => $this->url . '&action=edit_menu', 'name' => $this->_('CREATE_NOTE'), 'icon' => 'plus');
        }
        return $arr;
    }
}