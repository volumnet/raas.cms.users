<?php
/**
 * Таблица группы пользователей
 */
namespace RAAS\CMS\Users;

use \RAAS\Column;
use \RAAS\Table;

/**
 * Класс таблицы группы пользователей
 */
class GroupsTable extends Table
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
        $columns['name'] = [
            'callback' => function ($row) use ($view) {
                return '<div class="media">
                          <div class="media-body">
                            <h4 class="media-heading">
                              <a href="' . $view->url . '&id=' . (int)$row->id . '">
                                ' . htmlspecialchars($row->name) . '
                                (' . htmlspecialchars($row->urn) . ')
                              </a>
                            </h4>
                            ' . htmlspecialchars(\SOME\Text::cuttext($row->description)) . '
                          </div>
                        </div>';
            }
        ];
        $columns[' '] = [
            'callback' => function ($row) use ($view, $params) {
                return rowContextMenu($view->getGroupContextMenu($row));
            }
        ];
        $defaultParams = [
            'columns' => $columns,
            'caption' => $this->view->_('GROUPS'),
            'Set' => $params['Set'],
            'header' => false,
            'data-role' => 'multitable',
            'meta' => array(
                'allContextMenu' => $view->getAllGroupsContextMenu(),
            ),
        ];
        unset($params['columns'], $params['order']);
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
