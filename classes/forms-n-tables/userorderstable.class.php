<?php
namespace RAAS\CMS\Users;

use RAAS\CMS\Shop\OrdersTable;

class UserOrdersTable extends OrdersTable
{
    public function __construct(array $params = array())
    {
        parent::__construct($params);
        unset($this->Pages);
    }
}
