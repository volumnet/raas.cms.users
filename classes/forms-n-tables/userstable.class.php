<?php
namespace RAAS\CMS\Users;

class UsersTable extends \RAAS\Table
{
    protected $_view;

    public function __construct(array $params = array())
    {
        $this->_view = $view = isset($params['view']) ? $params['view'] : null;
        unset($params['view']);
        $columns = array();
        $columns['post_date'] = array(
            'caption' => $this->_('REGISTRATION_DATE'),
            'callback' => function($row) use ($view) { 
                return '<a href="' . $this->_view->url . '&action=edit&id=' . (int)$row->id . '">' . date(DATETIMEFORMAT, strtotime($row->post_date)) . '</a>';
            }
        );
        $columns['login'] = array(
            'caption' => $this->_('LOGIN'),
            'callback' => function($row) use ($view) { 
                return '<a href="' . $this->_view->url . '&action=edit&id=' . (int)$row->id . '">' . htmlspecialchars($row->login) . '</a>';
            }
        );
        $columns['email'] = array(
            'caption' => $this->_('EMAIL'),
            'callback' => function($row) use ($view) { 
                return '<a href="' . $this->_view->url . '&action=edit&id=' . (int)$row->id . '">' . htmlspecialchars($row->email) . '</a>';
            }
        );
        foreach ($IN['columns'] as $key => $col) {
            $columns[$col->urn] = array(
                'caption' => $col->name,
                'callback' => function($row) use ($col) { if (isset($row->fields[$col->urn])) { $y = $row->fields[$col->urn]->doRich(); } return $y ? $y : ''; }
            );
        }
        $columns[' '] = array('callback' => function ($row) use ($view) { return rowContextMenu($this->_view->getUserContextMenu($row)); });
        $defaultParams = array(
            'columns' => $columns, 
            'Set' => $IN['Set'], 
            'Pages' => $IN['Pages'],
            'callback' => function($Row) { if (!$Row->vis) { $Row->class = 'info'; } },
            'emptyString' => $this->_view->_('NO_USERS_FOUND'),
        );
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}