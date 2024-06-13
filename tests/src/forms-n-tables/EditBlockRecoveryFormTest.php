<?php
/**
 * Тест класса EditBlockRecoveryForm
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Application;
use RAAS\Field as RAASField;
use RAAS\FormTab;
use RAAS\User as RAASUser;
use RAAS\CMS\Block;
use RAAS\CMS\InterfaceField;
use RAAS\CMS\WidgetField;

/**
 * Тест класса EditBlockRecoveryForm
 * @covers RAAS\CMS\Users\EditBlockRecoveryForm
 */
class EditBlockRecoveryFormTest extends BaseTest
{
    public static $tables = [
        'cms_blocks',
        'cms_fields',
        'cms_pages',
        'cms_snippet_folders',
        'cms_snippets',
        'cms_users_blocks_recovery',
    ];

    /**
     * Тест получения свойства view
     */
    public function testGetView()
    {
        $form = new EditBlockRecoveryForm();

        $result = $form->view;

        $this->assertInstanceOf(View_Web::class, $result);
    }


    /**
     * Тест получения наследуемых свойств
     */
    public function testGetDefault()
    {
        $form = new EditBlockRecoveryForm(['Item' => Block::spawn(47)]); // 47 - блок восстановления пароля

        $result = $form->Item;

        $this->assertInstanceOf(Block_Recovery::class, $result);
        $this->assertEquals(47, $result->id);
    }


    /**
     * Тест конструктора класса
     */
    public function testConstruct()
    {
        $form = new EditBlockRecoveryForm();
        $interfaceField = $form->children['serviceTab']->children['interface_id'];
        $widgetField = $form->children['commonTab']->children['widget_id'];
        $notificationField = $form->children['commonTab']->children['notification_id'];

        $this->assertInstanceOf(InterfaceField::class, $interfaceField);
        $this->assertEquals(RecoveryInterface::class, $interfaceField->meta['rootInterfaceClass']);
        $this->assertEquals(RecoveryInterface::class, $interfaceField->default);
        $this->assertInstanceOf(WidgetField::class, $widgetField);
        $this->assertInstanceOf(InterfaceField::class, $notificationField);
        $this->assertNull($notificationField->meta['rootInterfaceClass'] ?? null);
        $this->assertEquals(50, $notificationField->default); // Стандартное уведомление об активации
    }
}
