<?php
/**
 * Таблица транзакций биллинга
 */
namespace RAAS\CMS\Users;

use RAAS\Table;

/**
 * Класс таблицы транзакций биллинга
 * @property-read ViewSub_Dev $view Представление
 */
class BillingTransactionsTable extends Table
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
                'post_date' => [
                    'caption' => $this->view->_('DATE'),
                    'class' => 'span3',
                    'callback' => function ($row) {
                        $t = strtotime($row->post_date);
                        return ($t > 0) ? date(DATETIMEFORMAT, $t) : '';
                    }
                ],
                'debit' => [
                    'caption' => $this->view->_('BILLING_DEBIT'),
                    'class' => 'span2',
                    'callback' => function ($row) {
                        $text = '';
                        if ((float)$row->amount >= 0) {
                            $text = '<span class="text-success">'
                                  .    number_format($row->amount, 2, '.', ' ')
                                  . '</span>';
                        }
                        return $text;
                    }
                ],
                'credit' => [
                    'caption' => $this->view->_('BILLING_CREDIT'),
                    'class' => 'span2',
                    'callback' => function ($row) {
                        $text = '';
                        if ((float)$row->amount < 0) {
                            $text = '<span class="text-error">'
                                  .    number_format($row->amount, 2, '.', ' ')
                                  . '</span>';
                        }
                        return $text;
                    }
                ],
                'name' => [
                    'caption' => $this->view->_('BILLING_PAYMENT_BASIS'),
                    'callback' => function ($row) {
                        return htmlspecialchars($row->name);
                    }
                ],
            ],
        ];
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
