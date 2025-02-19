<?php
/**
 * Тест класса Block_Activation
 */
namespace RAAS\CMS\Users;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use SOME\BaseTest;
use RAAS\Application;
use RAAS\Controller_Frontend as ControllerFrontend;
use RAAS\CMS\Package;
use RAAS\CMS\Page;

/**
 * Тест класса Block_Activation
 */
#[CoversClass(Block_Activation::class)]
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
