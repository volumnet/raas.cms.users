<?php
/**
 * Тест класса Block_Recovery
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Application;
use RAAS\Controller_Frontend as ControllerFrontend;
use RAAS\CMS\Package;
use RAAS\CMS\Page;

/**
 * Тест класса Block_Recovery
 * @covers RAAS\CMS\Users\Block_Recovery
 */
class BlockRecoveryTest extends BaseTest
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
        $block = new Block_Recovery(['location' => 'content', 'cats' => [1]]);
        $block->commit();

        $this->assertStringContainsString('Восстановление', $block->name);

        Block_Recovery::delete($block);
    }


    /**
     * Тест метода getAddData
     */
    public function testGetAddData()
    {
        $block = new Block_Recovery([
            'notification_id' => 50, // Уведомление о восстановлении пароля
            'location' => 'content',
            'cats' => [1],
        ]);
        $block->commit();
        $blockId = $block->id;

        $result = $block->getAddData();

        $this->assertEquals($blockId, $result['id']);
        $this->assertEquals(50, $result['notification_id']);

        Block_Recovery::delete($block);
    }
}
