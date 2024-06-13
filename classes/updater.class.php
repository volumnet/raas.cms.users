<?php
namespace RAAS\CMS\Users;

use SOME\SOME;
use RAAS\CMS\Snippet;

class Updater extends \RAAS\Updater
{
    public function preInstall()
    {
        $v = (string)($this->Context->registryGet('baseVersion') ?? '');
        if (version_compare($v, '4.2.13') < 0) {
            $this->update20151129();
            $this->update20190702();
        }
        if (version_compare($v, '4.3.23') < 0) {
            $this->update20240613();
        }
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
    /**
     * Обновления по версии 4.3.23
     * Исправление значения по умолчанию в cms_shop_orders.user_agent
     * Переход от сниппетов интерфейсов к классам
     */
    public function update20240613()
    {
        $sqlQuery = "SELECT COUNT(*) FROM " . SOME::_dbprefix() . "cms_snippets WHERE urn = '__raas_users_register_interface'";
        $sqlResult = (int)$this->SQL->getvalue($sqlQuery);
        if ($sqlResult > 0) {
            foreach ([
                '__raas_users_activation_interface' => ActivationInterface::class,
                '__raas_users_login_interface' => LogInInterface::class,
                '__raas_users_recovery_interface' => RecoveryInterface::class,
                '__raas_users_register_interface' => RegisterInterface::class,
            ] as $snippetURN => $interfaceClassname) {
                $sqlBind = ['snippetURN' => $snippetURN, 'interfaceClassname' => $interfaceClassname];
                // Заменим основной интерфейс
                $sqlQuery = "UPDATE " . SOME::_dbprefix() . "cms_blocks AS tB
                               JOIN " . SOME::_dbprefix() . "cms_snippets AS tS ON tB.interface_id = tS.id
                                SET tB.interface_id = 0,
                                    tB.interface_classname = :interfaceClassname
                              WHERE tS.urn = :snippetURN";
                $this->SQL->query([$sqlQuery, $sqlBind]);
                // Заменим интерфейс кэширования
                $sqlQuery = "UPDATE " . SOME::_dbprefix() . "cms_blocks AS tB
                               JOIN " . SOME::_dbprefix() . "cms_snippets AS tS ON tB.cache_interface_id = tS.id
                                SET tB.cache_interface_id = 0,
                                    tB.cache_interface_classname = :interfaceClassname
                              WHERE tS.urn = :snippetURN";
                $this->SQL->query([$sqlQuery, $sqlBind]);
                // Заменим интерфейс процессоров
                $sqlQuery = "UPDATE " . SOME::_dbprefix() . "cms_fields AS tF
                               JOIN " . SOME::_dbprefix() . "cms_snippets AS tS ON tF.preprocessor_id = tS.id
                                SET tF.preprocessor_id = 0,
                                    tF.preprocessor_classname = :interfaceClassname
                              WHERE tS.urn = :snippetURN";
                $this->SQL->query([$sqlQuery, $sqlBind]);
                $sqlQuery = "UPDATE " . SOME::_dbprefix() . "cms_fields AS tF
                               JOIN " . SOME::_dbprefix() . "cms_snippets AS tS ON tF.postprocessor_id = tS.id
                                SET tF.postprocessor_id = 0,
                                    tF.postprocessor_classname = :interfaceClassname
                              WHERE tS.urn = :snippetURN";
                $this->SQL->query([$sqlQuery, $sqlBind]);
                // Удалим сниппеты
                $sqlQuery = "DELETE FROM " . SOME::_dbprefix() . "cms_snippets WHERE urn = ?";
                $this->SQL->query([$sqlQuery, [$snippetURN]]);
            }
        }
    }
}
