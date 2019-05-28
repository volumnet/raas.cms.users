<?php
/**
 * Таблица пользователей
 */
namespace RAAS\CMS\Users;

use RAAS\Column;
use RAAS\Table;

/**
 * Класс таблицы пользователей
 * @property-read ViewSub_Users $view Представление
 */
class UsersTable extends Table
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


    public function __construct(array $params = [])
    {
        $view = $this->view;
        unset($params['view']);
        $columns = [];
        $columns['post_date'] = [
            'caption' => $this->view->_('REGISTRATION_DATE'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function ($row) use ($view) {
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '"' . (!$row->vis ? ' class="muted"' : '') . '>' .
                          date(DATETIMEFORMAT, strtotime($row->post_date)) .
                       '</a>';
            }
        ];
        $columns['login'] = [
            'caption' => $this->view->_('LOGIN'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function ($row) use ($view) {
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '"' . (!$row->vis ? ' class="muted"' : '') . '>' .
                          htmlspecialchars($row->login) .
                       '</a>';
            }
        ];
        $columns['email'] = [
            'caption' => $this->view->_('EMAIL'),
            'sortable' => Column::SORTABLE_REVERSABLE,
            'callback' => function ($row) use ($view) {
                return '<a href="' . $view->url . '&action=edit&id=' . (int)$row->id . '"' . (!$row->vis ? ' class="muted"' : '') . '>' .
                          htmlspecialchars($row->email) .
                       '</a>';
            }
        ];
        foreach ($params['columns'] as $key => $col) {
            $columns[$col->urn] = [
                'caption' => $col->name,
                'sortable' => Column::SORTABLE_REVERSABLE,
                'callback' => function ($row) use ($col) {
                    if (isset($row->fields[$col->urn])) {
                        $y = $row->fields[$col->urn]->doRich();
                    }
                    if ($y) {
                        return '<span' . (!$row->vis ? ' class="muted"' : '') . '>' .
                                  htmlspecialchars($y) .
                               '</span>';
                    }
                    return '';
                }
            ];
        }
        foreach ($params['billingTypes'] as $billingType) {
            $columns['balance' . (int)$billingType->id] = [
                'caption' => $billingType->name,
                'sortable' => Column::SORTABLE_REVERSABLE,
                'callback' => function ($row) use ($view, $billingType) {
                    $balance = (float)$row->{'balance' . (int)$billingType->id};
                    return '<span class="' . (!$row->vis ? 'muted' : ('text-' . (($balance >= 0) ? 'success' : 'error'))) . '">' .
                              number_format($balance, 2, '.', ' ') .
                           '</a>';
                }
            ];
        }
        $columns[' '] = ['callback' => function ($row) use ($view, $params) {
            return rowContextMenu(
                $view->getUserContextMenu($row, $params['Group'])
            );
        }];
        $defaultParams = [
            'caption' => $this->view->_('USERS'),
            'columns' => $columns,
            'Set' => $params['Set'],
            'Pages' => $params['Pages'],
            'callback' => function ($Row) {
                if ($Row->source->new) {
                    $Row->class = 'info';
                }
            },
            'emptyString' => $this->view->_('NO_USERS_FOUND'),
            'template' => 'showlist',
            'order' => (
                (strtolower($params['order']) == 'desc') ?
                Column::SORT_DESC :
                Column::SORT_ASC
            ),
            'data-role' => 'multitable',
            'meta' => [
                'allContextMenu' => $view->getAllUsersContextMenu($params['Group']),
            ],
        ];
        unset($params['columns'], $params['order']);
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
