<?php
/**
 * Тест класса Block_Register
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Application;
use RAAS\Controller_Frontend as ControllerFrontend;
use RAAS\CMS\Package;
use RAAS\CMS\Page;
use RAAS\CMS\Snippet;
use RAAS\CMS\User;

/**
 * Тест класса Block_Register
 * @covers RAAS\CMS\Users\Block_Register
 */
class BlockRegisterTest extends BaseTest
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
        $block = new Block_Register(['location' => 'content', 'cats' => [1]]);
        $block->commit();

        $this->assertStringContainsString('Регистрация', $block->name);

        Block_Register::delete($block);
    }


    /**
     * Тест метода getAddData
     */
    public function testGetAddData()
    {
        $block = new Block_Register([
            'form_id' => 4, // Форма для регистрации
            'email_as_login' => true,
            'notify_about_edit' => true,
            'allow_edit_social' => true,
            'activation_type' => Block_Register::ACTIVATION_TYPE_USER,
            'allow_to' => Block_Register::ALLOW_TO_REGISTERED,
            'redirect_url' => '',

            'location' => 'content',
            'cats' => [1]
        ]);
        $block->commit();
        $blockId = $block->id;

        $result = $block->getAddData();

        $this->assertEquals($blockId, $result['id']);
        $this->assertEquals(4, $result['form_id']);
        $this->assertEquals(1, $result['email_as_login']);
        $this->assertEquals(1, $result['notify_about_edit']);
        $this->assertEquals(1, $result['allow_edit_social']);
        $this->assertEquals(Block_Register::ACTIVATION_TYPE_USER, $result['activation_type']);
        $this->assertEquals(Block_Register::ALLOW_TO_REGISTERED, $result['allow_to']);
        $this->assertEquals('', $result['redirect_url']);

        Block_Register::delete($block);
    }


    /**
     * Тест метода process()
     */
    public function testProcess()
    {
        $snippet = new Snippet(51); // Регистрация
        $snippet->description = file_get_contents(Module::i()->resourcesDir . '/widgets/register.tmp.php');
        $snippet->commit();

        $block = new Block_Register(45); // Блок регистрации на странице регистрации

        ob_start();
        $result = $block->process(new Page(30), true); // Страница регистрации
        $html = ob_get_clean();

        $this->assertNotEmpty($html);
    }


    /**
     * Тест метода process() - случай с закрытым доступом
     */
    public function testProcessWithDeniedAccess()
    {
        ControllerFrontend::i()->user = new User();
        $block = new Block_Register([
            'form_id' => 4, // Форма для регистрации
            'email_as_login' => true,
            'notify_about_edit' => true,
            'allow_edit_social' => true,
            'activation_type' => Block_Register::ACTIVATION_TYPE_USER,
            'allow_to' => Block_Register::ALLOW_TO_REGISTERED,
            'redirect_url' => '',

            'location' => 'content',
            'cats' => [1]
        ]);
        $block->commit();

        ob_start();
        $result = $block->process(new Page(1), true);
        $html = ob_get_clean();

        $this->assertNull($result);
        $this->assertEmpty($html);

        Block_Register::delete($block);
    }
}
