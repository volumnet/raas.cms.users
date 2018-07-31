<?php
namespace RAAS\CMS\Users;

use RAAS\CMS\Shop\OrdersTable;

class UserOrdersTable extends OrdersTable
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);
        // 2016-03-11, AVS: убрал удаление контекстного меню — непонятно, зачем удалял, клиентам неудобно админить отзывы
        unset($this->Pages/*, $this->columns[' ']*/);
    }
}
