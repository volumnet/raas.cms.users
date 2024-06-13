<?php
/**
 * Тест класса Block_Login
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Application;
use RAAS\Controller_Frontend as ControllerFrontend;
use RAAS\CMS\Package;
use RAAS\CMS\Page;

/**
 * Тест класса Block_Login
 * @covers RAAS\CMS\Users\Block_Login
 */
class BlockLoginTest extends BaseTest
{
    public static $tables = [
    ];

    public static function setUpBeforeClass(): void
    {
        ControllerFrontend::i()->exportLang(Application::i(), 'ru');
        ControllerFrontend::i()->exportLang(Package::i(), 'ru');
        ControllerFrontend::i()->exportLang(Module::i(), 'ru');
    }

    /**
     * Тест метода commit() - случай с установленным виджетом, без интерфейса
     */
    public function testCommit()
    {
        $block = new Block_Login(['location' => 'content', 'cats' => [1]]);
        $block->commit();

        $this->assertStringContainsString('Вход', $block->name);

        Block_Login::delete($block);
    }


    /**
     * Тест метода getAddData
     */
    public function testGetAddData()
    {
        $block = new Block_Login([
            'email_as_login' => true,
            'social_login_type' => Block_Login::SOCIAL_LOGIN_ONLY_REGISTERED,
            'password_save_type' => Block_Login::SAVE_PASSWORD_FOREIGN_COMPUTER,
            'location' => 'content',
            'cats' => [1]
        ]);
        $block->commit();
        $blockId = $block->id;

        $result = $block->getAddData();

        $this->assertEquals($blockId, $result['id']);
        $this->assertEquals(1, $result['email_as_login']);
        $this->assertEquals(Block_Login::SOCIAL_LOGIN_ONLY_REGISTERED, $result['social_login_type']);
        $this->assertEquals(Block_Login::SAVE_PASSWORD_FOREIGN_COMPUTER, $result['password_save_type']);

        Block_Login::delete($block);
    }
}
