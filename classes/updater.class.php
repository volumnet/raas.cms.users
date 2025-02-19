<?php
namespace RAAS\CMS\Users;

use SOME\SOME;
use RAAS\CMS\Snippet;
use RAAS\CMS\User_Field;

class Updater extends \RAAS\Updater
{
    public function preInstall()
    {
        // 2025 год - 8
        // 2024 год - 7/8
        // 2023 год - 7
        // 2022 год - 5/7
        // 2021 год - 5 -- убираем его и ранее
        $v = (string)($this->Context->registryGet('baseVersion') ?? '');
        if (version_compare($v, '4.3.23') < 0) {
            $this->update20240613();
        }
        // ПО ВОЗМОЖНОСТИ НЕ ПИШЕМ СЮДА, А ПИШЕМ В postInstall
    }


    public function postInstall()
    {
        $w = new Webmaster();
        $userFields = User_Field::getSet();
        $w->checkStdInterfaces();
        if (!$userFields) {
            $w->createCab();
        }
    }


    /**
     * Обновления по версии 4.3.23
     * Исправление значения по умолчанию в cms_shop_orders.user_agent
     * Переход от сниппетов интерфейсов к классам
     */
    public function update20240613()
    {
        if (in_array(SOME::_dbprefix() . "cms_snippets", $this->tables)) {
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
}
