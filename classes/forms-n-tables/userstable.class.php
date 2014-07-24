<?php
namespace RAAS\CMS\Users;
use \RAAS\Column;

class UsersTable extends \RAAS\Table
{
    protected $_view;

    public function __construct(array $params = array())
    {
        $this->_view = $view = isset($params['view']) ? $params['view'] : null;
        unset($params['view']);
        $columns = array();
        $columns['post_date'] = array(
            'caption' => $this->_view->_('REGISTRATION_DATE'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function($row) use ($view) { 
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '">' . date(DATETIMEFORMAT, strtotime($row->post_date)) . '</a>';
            }
        );
        $columns['login'] = array(
            'caption' => $this->_view->_('LOGIN'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function($row) use ($view) { 
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '">' . htmlspecialchars($row->login) . '</a>';
            }
        );
        $columns['email'] = array(
            'caption' => $this->_view->_('EMAIL'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function($row) use ($view) { 
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '">' . htmlspecialchars($row->email) . '</a>';
            }
        );
        foreach ($params['columns'] as $key => $col) {
            $columns[$col->urn] = array(
                'caption' => $col->name,
                'sortable' => Column::SORTABLE_REVERSABLE,
                'callback' => function($row) use ($col) { if (isset($row->fields[$col->urn])) { $y = $row->fields[$col->urn]->doRich(); } return $y ? $y : ''; }
            );
        }
        $columns[' '] = array('callback' => function ($row) use ($view) { return rowContextMenu($view->getUserContextMenu($row)); });
        $defaultParams = array(
            'columns' => $columns, 
            'Set' => $params['Set'], 
            'Pages' => $params['Pages'],
            'callback' => function($Row) { if ($Row->source->new) { $Row->class = 'info'; } },
            'emptyString' => $this->_view->_('NO_USERS_FOUND'),
            'template' => 'showlist',
            'order' => ((strtolower($params['order']) == 'desc') ? Column::SORT_DESC : Column::SORT_ASC)
        );
        unset($params['columns'], $params['order']);
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}