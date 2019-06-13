<?php
/**
 * Тип биллинга
 */
namespace RAAS\CMS\Users;

use SOME\SOME;
use SOME\Text;
use RAAS\CMS\Package;
use RAAS\CMS\User;
use RAAS\CMS\ImportByURNTrait;

/**
 * Класс типа биллинга
 */
class BillingType extends SOME
{
    use ImportByURNTrait;

    protected static $tablename = 'cms_users_billing_types';

    protected static $defaultOrderBy = "name";

    public function commit()
    {
        if (!$this->urn && $this->name) {
            $this->urn = Text::beautify($this->name);
        }
        Package::i()->getUniqueURN($this);
        parent::commit();
    }


    /**
     * Совершить транзакцию
     * @param User $paramname Пользователь
     * @param float $amount Сумма перевода
     * @param string $name Основание перевода
     */
    public function transact(User $user, $amount, $name)
    {
        $transaction = new BillingTransaction([
            'uid' => (int)$user->id,
            'billing_type_id' => (int)$this->id,
            'name' => trim($name),
            'amount' => (float)$amount,
        ]);
        $transaction->commit();
    }


    /**
     * Получить текущий баланс пользователя
     * @param User $paramname Пользователь
     * @return float
     */
    public function getBalance(User $user)
    {
        $sqlQuery = "SELECT balance
                       FROM cms_users_billing_accounts
                      WHERE uid = ?
                        AND billing_type_id = ?";
        $sqlBind = [(int)$user->id, (int)$this->id];
        $sqlResult = (float)static::$SQL->getvalue([$sqlQuery, $sqlBind]);
        return $sqlResult;
    }
}
