<?php
/**
 * Транзакция в биллинге
 */
namespace RAAS\CMS\Users;

use SOME\SOME;
use RAAS\CMS\User;

/**
 * Класс транзакции в биллинге
 * @property-read User $user Пользователь
 * @property-read BillingType $billingType Тип биллинга
 */
class BillingTransaction extends SOME
{
    protected static $tablename = 'cms_users_billing_transactions';

    protected static $defaultOrderBy = "post_date";

    protected static $references = [
        'user' => [
            'FK' => 'uid',
            'classname' => User::class,
            'cascade' => false,
        ],
        'billingType' => [
            'FK' => 'billing_type_id',
            'classname' => BillingType::class,
            'cascade' => false,
        ],
    ];

    public function commit()
    {
        if (!$this->post_date) {
            $this->post_date = date('Y-m-d H:i:s');
        }
        $amount = (float)$this->amount;
        parent::commit();
        if ($this->user->id && $this->billingType->id) {
            $oldBalance = (float)$this->billingType->getBalance($this->user);
            $newBalance = $oldBalance + $amount;
            static::$SQL->add('cms_users_billing_accounts', [
                'uid' => (int)$this->user->id,
                'billing_type_id' => (int)$this->billingType->id,
                'balance' => (float)$newBalance,
            ]);
        }
    }
}
