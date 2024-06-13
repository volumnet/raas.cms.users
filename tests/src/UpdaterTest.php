<?php
/**
 * Файл теста менеджера обновлений
 * (поскольку мы не можем хранить все предыдущие версии, тестируем текущее состояние, без покрытия менеджера обновлений)
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Application;
use RAAS\CMS\Block;
use RAAS\CMS\Snippet;

/**
 * Класс теста обновлений
 */
class UpdaterTest extends BaseTest
{
    public static $tables = [
        'cms_blocks',
        'cms_snippets',
        'cms_users_blocks_login',
        'cms_users_blocks_recovery',
        'cms_users_blocks_register',
    ];


    /**
     * Тест состояния версии 4.3.23 - чтобы не было сниппетов интерфейсов и в блоках поменялись на классы
     */
    public function testState040323ReplaceSnippetsWithInterfacesClassnames()
    {
        $snippet = Snippet::importByURN('__raas_users_activation_interface');
        $block = Block::spawn(48); // Блок активации

        $this->assertNull($snippet);
        $this->assertEmpty($block->interface_id);
        $this->assertEquals(ActivationInterface::class, $block->interface_classname);


        $snippet = Snippet::importByURN('__raas_users_login_interface');
        $block = Block::spawn(46); // Блок входа в систему

        $this->assertNull($snippet);
        $this->assertEmpty($block->interface_id);
        $this->assertEquals(LogInInterface::class, $block->interface_classname);


        $snippet = Snippet::importByURN('__raas_users_recovery_interface');
        $block = Block::spawn(47); // Блок восстановления пароля

        $this->assertNull($snippet);
        $this->assertEmpty($block->interface_id);
        $this->assertEquals(RecoveryInterface::class, $block->interface_classname);


        $snippet = Snippet::importByURN('__raas_users_register_interface');
        $block = Block::spawn(45); // Блок регистрации

        $this->assertNull($snippet);
        $this->assertEmpty($block->interface_id);
        $this->assertEquals(RegisterInterface::class, $block->interface_classname);
    }
}
