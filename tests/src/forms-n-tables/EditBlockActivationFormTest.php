<?php
/**
 * Тест класса EditBlockActivationForm
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
 * Тест класса EditBlockActivationForm
 * @covers RAAS\CMS\Users\EditBlockActivationForm
 */
class EditBlockActivationFormTest extends BaseTest
{
    public static $tables = [
        'cms_blocks',
        'cms_fields',
        'cms_pages',
        'cms_snippet_folders',
        'cms_snippets',
    ];

    /**
     * Тест получения свойства view
     */
    public function testGetView()
    {
        $form = new EditBlockActivationForm();

        $result = $form->view;

        $this->assertInstanceOf(View_Web::class, $result);
    }


    /**
     * Тест получения наследуемых свойств
     */
    public function testGetDefault()
    {
        $form = new EditBlockActivationForm(['Item' => Block::spawn(48)]); // 48 - блок активации

        $result = $form->Item;

        $this->assertInstanceOf(Block_Activation::class, $result);
        $this->assertEquals(48, $result->id);
    }


    /**
     * Тест конструктора класса
     */
    public function testConstruct()
    {
        $form = new EditBlockActivationForm();
        $interfaceField = $form->children['serviceTab']->children['interface_id'];
        $widgetField = $form->children['commonTab']->children['widget_id'];

        $this->assertInstanceOf(InterfaceField::class, $interfaceField);
        $this->assertEquals(ActivationInterface::class, $interfaceField->meta['rootInterfaceClass']);
        $this->assertEquals(ActivationInterface::class, $interfaceField->default);
        $this->assertInstanceOf(WidgetField::class, $widgetField);
    }
}
