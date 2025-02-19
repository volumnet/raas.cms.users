<?php
/**
 * Тест класса EditBlockLogInForm
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
 * Тест класса EditBlockLogInForm
 */
#[CoversClass(EditBlockLogInForm::class)]
class EditBlockLogInFormTest extends BaseTest
{
    public static $tables = [
        'cms_blocks',
        'cms_fields',
        'cms_pages',
        'cms_snippet_folders',
        'cms_snippets',
        'cms_users_blocks_login',
    ];

    /**
     * Тест получения свойства view
     */
    public function testGetView()
    {
        $form = new EditBlockLogInForm();

        $result = $form->view;

        $this->assertInstanceOf(View_Web::class, $result);
    }


    /**
     * Тест получения наследуемых свойств
     */
    public function testGetDefault()
    {
        $form = new EditBlockLogInForm(['Item' => Block::spawn(46)]); // 46 - блок входа в систему

        $result = $form->Item;

        $this->assertInstanceOf(Block_LogIn::class, $result);
        $this->assertEquals(46, $result->id);
    }


    /**
     * Тест конструктора класса
     */
    public function testConstruct()
    {
        $form = new EditBlockLogInForm();
        $interfaceField = $form->children['serviceTab']->children['interface_id'];
        $widgetField = $form->children['commonTab']->children['widget_id'];

        $this->assertInstanceOf(InterfaceField::class, $interfaceField);
        $this->assertEquals(LogInInterface::class, $interfaceField->meta['rootInterfaceClass']);
        $this->assertEquals(LogInInterface::class, $interfaceField->default);
        $this->assertInstanceOf(WidgetField::class, $widgetField);
    }
}
