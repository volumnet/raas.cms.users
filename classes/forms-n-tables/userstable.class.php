<?php
namespace RAAS\CMS\Users;
use \RAAS\Column;

class UsersTable extends \RAAS\Table
{
    protected $_view;

    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Users::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = array())
    {
        $view = $this->view;
        unset($params['view']);
        $columns = array();
        $columns['post_date'] = array(
            'caption' => $this->view->_('REGISTRATION_DATE'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function($row) use ($view) {
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '"' . (!$row->vis ? ' class="muted"' : '') . '>' . date(DATETIMEFORMAT, strtotime($row->post_date)) . '</a>';
            }
        );
        $columns['login'] = array(
            'caption' => $this->view->_('LOGIN'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function($row) use ($view) {
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '"' . (!$row->vis ? ' class="muted"' : '') . '>' . htmlspecialchars($row->login) . '</a>';
            }
        );
        $columns['email'] = array(
            'caption' => $this->view->_('EMAIL'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function($row) use ($view) {
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '"' . (!$row->vis ? ' class="muted"' : '') . '>' . htmlspecialchars($row->email) . '</a>';
            }
        );
        foreach ($params['columns'] as $key => $col) {
            $columns[$col->urn] = array(
                'caption' => $col->name,
                'sortable' => Column::SORTABLE_REVERSABLE,
                'callback' => function($row) use ($col) { if (isset($row->fields[$col->urn])) { $y = $row->fields[$col->urn]->doRich(); } return $y ? '<span' . (!$row->vis ? ' class="muted"' : '') . '>' . htmlspecialchars($y) . '</span>' : ''; }
            );
        }
        $columns[' '] = array('callback' => function ($row) use ($view, $params) { return rowContextMenu($view->getUserContextMenu($row, $params['Group'])); });
        $defaultParams = array(
            'caption' => $this->view->_('USERS'),
            'columns' => $columns,
            'Set' => $params['Set'],
            'Pages' => $params['Pages'],
            'callback' => function($Row) { if ($Row->source->new) { $Row->class = 'info'; } },
            'emptyString' => $this->view->_('NO_USERS_FOUND'),
            'template' => 'showlist',
            'order' => ((strtolower($params['order']) == 'desc') ? Column::SORT_DESC : Column::SORT_ASC),
            'data-role' => 'multitable',
            'meta' => array(
                'allContextMenu' => $view->getAllUsersContextMenu($params['Group']),
            ),
        );
        unset($params['columns'], $params['order']);
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
