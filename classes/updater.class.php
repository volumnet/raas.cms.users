<?php
namespace RAAS\CMS\Users;

use SOME\SOME;
use RAAS\CMS\Snippet;

class Updater extends \RAAS\Updater
{
    public function preInstall()
    {
        $this->update20151129();
        $this->update20190702();
    }


    public function postInstall()
    {
        $w = new Webmaster();
        $s = Snippet::importByURN('__RAAS_users_register_interface');
        $w->checkStdInterfaces();
        if (!$s || !$s->id) {
            $w->createCab();
        }
    }

    public function update20151129()
    {
        if (in_array(SOME::_dbprefix() . "cms_forms", $this->tables) &&
            in_array('urn', $this->columns(SOME::_dbprefix() . "cms_forms"))) {
            $sqlQuery = "UPDATE " . SOME::_dbprefix() . "cms_forms
                             SET urn = 'register'
                           WHERE (urn = '')
                             AND (
                                    name = 'Форма для регистрации'
                                 OR name = 'Registration form'
                             )";
            $this->SQL->query($sqlQuery);
        }
    }


    /**
     * Добавление поле "Автор" в транзакции
     */
    public function update20190702()
    {
        if (in_array(SOME::_dbprefix() . "cms_users_billing_transactions", $this->tables) &&
            !in_array('author_id', $this->columns(SOME::_dbprefix() . "cms_users_billing_transactions"))) {
            $sqlQuery = "ALTER TABLE " . SOME::_dbprefix() . "cms_users_billing_transactions
                           ADD author_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Author ID#' AFTER id,
                           ADD KEY (author_id)";
            $this->SQL->query($sqlQuery);
        }
    }

}
