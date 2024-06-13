<?php
/**
 * Тест класса Block_Activation
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Application;
use RAAS\Controller_Frontend as ControllerFrontend;
use RAAS\CMS\Package;
use RAAS\CMS\Page;

/**
 * Тест класса Block_Activation
 * @covers RAAS\CMS\Users\Block_Activation
 */
class BlockActivationTest extends BaseTest
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
        $block = new Block_Activation(['location' => 'content', 'cats' => [1]]);
        $block->commit();

        $this->assertStringContainsString('Активация', $block->name);

        Block_Activation::delete($block);
    }
}
