<?php
/**
 * Таблица типов биллинга
 */
namespace RAAS\CMS\Users;

use RAAS\Table;

/**
 * Класс таблицы типов биллинга
 * @property-read ViewSub_Dev $view Представление
 */
class BillingTypesTable extends Table
{
    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Dev::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = [])
    {
        $view = $this->view;
        $defaultParams = [
            'columns' => [
                'name' => [
                    'caption' => $this->view->_('NAME'),
                    'callback' => function ($row) use ($view) {
                        return '<a href="' . $view->url . '&action=edit_billing_type&id=' . (int)$row->id . '">' .
                                  htmlspecialchars($row->name) .
                               '</a>';
                    }
                ],
                'urn' => [
                    'caption' => $this->view->_('URN'),
                    'callback' => function ($row) use ($view) {
                        return '<a href="' . $view->url . '&action=edit_billing_type&id=' . (int)$row->id . '">' .
                                  htmlspecialchars($row->urn) .
                               '</a>';
                    }
                ],
                ' ' => [
                    'callback' => function ($row) use ($view) {
                        return rowContextMenu(
                            $view->getBillingTypeContextMenu($row)
                        );
                    }
                ]
            ],
            'emptyString' => $this->view->_('NO_BILLING_TYPES_FOUND'),
        ];
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
