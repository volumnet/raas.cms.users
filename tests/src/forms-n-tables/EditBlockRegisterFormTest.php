<?php
/**
 * Тест класса EditBlockRegisterForm
 */
namespace RAAS\CMS\Users;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use SOME\BaseTest;
use RAAS\Application;
use RAAS\Field as RAASField;
use RAAS\FormTab;
use RAAS\User as RAASUser;
use RAAS\CMS\Block;
use RAAS\CMS\InterfaceField;
use RAAS\CMS\WidgetField;

/**
 * Тест класса EditBlockRegisterForm
 */
#[CoversClass(EditBlockRegisterForm::class)]
class EditBlockRegisterFormTest extends BaseTest
{
    public static $tables = [
        'cms_blocks',
        'cms_fields',
        'cms_forms',
        'cms_pages',
        'cms_snippet_folders',
        'cms_snippets',
        'cms_users_blocks_register',
    ];

    /**
     * Тест получения свойства view
     */
    public function testGetView()
    {
        $form = new EditBlockRegisterForm();

        $result = $form->view;

        $this->assertInstanceOf(View_Web::class, $result);
    }


    /**
     * Тест получения наследуемых свойств
     */
    public function testGetDefault()
    {
        $form = new EditBlockRegisterForm(['Item' => Block::spawn(45)]); // 45 - блок регистрации

        $result = $form->Item;

        $this->assertInstanceOf(Block_Register::class, $result);
        $this->assertEquals(45, $result->id);
    }


    /**
     * Тест конструктора класса
     */
    public function testConstruct()
    {
        $form = new EditBlockRegisterForm();
        $interfaceField = $form->children['serviceTab']->children['interface_id'];
        $widgetField = $form->children['commonTab']->children['widget_id'];

        $this->assertInstanceOf(InterfaceField::class, $interfaceField);
        $this->assertEquals(RegisterInterface::class, $interfaceField->meta['rootInterfaceClass']);
        $this->assertEquals(RegisterInterface::class, $interfaceField->default);
        $this->assertInstanceOf(WidgetField::class, $widgetField);
    }
}
