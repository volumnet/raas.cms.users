<?php
/**
 * Файл теста стандартного интерфейса регистрации
 */
namespace RAAS\CMS\Users;

use ReflectionClass;
use RAAS\Application;
use RAAS\Controller_Frontend;
use RAAS\CMS\Block;
use RAAS\CMS\Form;
use RAAS\CMS\Form_Field;
use RAAS\CMS\Material;
use RAAS\CMS\Material_Type;
use RAAS\CMS\Page;
use RAAS\CMS\Package;
use RAAS\CMS\SocialProfile;
use RAAS\CMS\ULogin;
use RAAS\CMS\User;
use RAAS\CMS\User_Field;

/**
 * Класс теста стандартного интерфейса регистрации
 */
class EditUserMaterialInterfaceTest extends BaseDBTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $block = Block::spawn(27);
        $block->form = 4;
        $block->commit();

        $nameField = new Form_Field([
            'pid' => 4,
            'urn' => '_name_',
            'datatype' => 'text',
        ]);
        $nameField->commit();
        $priceField = new Form_Field([
            'pid' => 4,
            'urn' => 'price',
            'datatype' => 'number',
        ]);
        $priceField->commit();
        $relatedField = new Form_Field([
            'pid' => 4,
            'urn' => 'related',
            'datatype' => 'material',
            'source' => 4,
            'multiple' => 1,
        ]);
        $relatedField->commit();
        $catalogField = new User_Field([
            'urn' => 'catalog',
            'datatype' => 'material',
            'source' => 4,
        ]);
        $catalogField->commit();

        $form = new Form(4); // Обратная связь
        $form->material_type = 4; // Каталог продукции
        $form->commit();

        $loginField = $form->fields['login'];
        $loginField->required = 0;
        $loginField->commit();

        $passwordField = $form->fields['password'];
        $passwordField->required = 0;
        $passwordField->commit();

        $fullNameField = $form->fields['full_name'];
        $fullNameField->required = 0;
        $fullNameField->commit();
    }


    public static function tearDownAfterClass()
    {
        $block = Block::spawn(27);
        $block->form = 1;
        $block->commit();

        $form = new Form(4); // Обратная связь
        $form->material_type = 0; // Новости
        $form->commit();

        $nameField = $form->fields['_name_'];
        $priceField = $form->fields['price'];
        $relatedField = $form->fields['related'];
        $user = new User();
        $catalogField = $user->fields['catalog'];

        Form_Field::delete($nameField);
        Form_Field::delete($priceField);
        Form_Field::delete($relatedField);
        User_Field::delete($catalogField);

        $loginField = $form->fields['login'];
        $loginField->required = 1;
        $loginField->commit();

        $passwordField = $form->fields['password'];
        $passwordField->required = 1;
        $passwordField->commit();

        $fullNameField = $form->fields['full_name'];
        $fullNameField->required = 1;
        $fullNameField->commit();
    }


    /**
     * Тест обработки
     */
    public function testProcess()
    {
        $block = Block::spawn(27);
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User(1);
        $user->fields['catalog']->addValue(10);
        $post = [
            '_name_' => 'Новый товар',
            'price' => 12345,
            'related' => [11, 12, 13],
            'form_signature' => md5('form427'),
        ];
        $interface = new EditUserMaterialInterface($block, $page, [], $post);

        $result = $interface->process();

        $this->assertEmpty($result['localError']);
        $this->assertInstanceof(Material::class, $result['Material']);
        $material = $result['Material'];
        $this->assertNotNull($material->id);
        $this->assertEquals(10, $material->id);
        $this->assertEquals('Новый товар', $material->name);
        $this->assertCount(3, $material->related);
        $this->assertInstanceof(Material::class, $material->related[0]);
        $this->assertEquals(11, $material->related[0]->id);
        $this->assertEquals(12, $material->related[1]->id);
        $this->assertEquals(13, $material->related[2]->id);
        $this->assertEquals('12345', $material->price);

        $material = new Material(10);
        $material->name = 'Товар 1';
        $material->commit();
        $material->fields['price']->deleteValues();
        $material->fields['price']->addValue(83620);
        $material->fields['related']->deleteValues();
        $user->fields['catalog']->deleteValues();
    }


    /**
     * Тест обработки
     * (случай нового материала)
     */
    public function testProcessWithNewMaterial()
    {
        $block = Block::spawn(27);
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User(1);
        $post = [
            '_name_' => 'Новый товар',
            'price' => 12345,
            'form_signature' => md5('form427'),
        ];
        $interface = new EditUserMaterialInterface($block, $page, [], $post);

        $result = $interface->process();

        $this->assertEmpty($result['localError']);
        $this->assertInstanceof(Material::class, $result['Material']);
        $material = $result['Material'];
        $this->assertEquals(0, $material->vis);
        $this->assertEquals(4, $material->pid);
        $this->assertEquals('Новый товар', $material->name);
        $this->assertEquals('12345', $material->price);
        $this->assertEquals([30], $material->pages_ids);

        Material::delete($material);
        $user->fields['catalog']->deleteValues();
    }


    /**
     * Тест обработки
     * (случай с незарегистрированным пользователем)
     */
    public function testProcessWithoutUser()
    {
        $block = Block::spawn(27);
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User();
        $post = [
            '_name_' => 'Новый товар',
            'price' => 12345,
            'form_signature' => md5('form427')
        ];

        $interface = new EditUserMaterialInterface(
            $block,
            $page,
            [],
            $post,
            [],
            [],
            [],
            []
        );

        $result = $interface->process();

        $this->assertEmpty($result['localError']);
        $this->assertNull($result['success']);
        $this->assertInstanceof(Form::class, $result['Form']);
        $this->assertEquals(4, $result['Form']->id);
        $this->assertEquals($user, $result['User']);
    }


    /**
     * Тест обработки
     * (случай без отправки формы)
     */
    public function testProcessWithNoFormData()
    {
        $block = Block::spawn(27);
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User(1);
        $user->fields['catalog']->addValue(10);
        $material = new Material(10);
        $material->fields['related']->addValue(11);
        $material->fields['related']->addValue(12);
        $material->fields['related']->addValue(13);
        $post = [];

        $interface = new EditUserMaterialInterface(
            $block,
            $page,
            [],
            $post,
            [],
            [],
            [],
            []
        );

        $result = $interface->process();

        $this->assertEquals([], $result['localError']);
        $this->assertNull($result['success']);
        $this->assertEquals('Товар 1', $result['DATA']['_name_']);
        $this->assertEquals('83620', $result['DATA']['price']);
        $this->assertCount(3, $result['DATA']['related']);
        $this->assertEquals(11, $result['DATA']['related'][0]);
        $this->assertEquals(12, $result['DATA']['related'][1]);
        $this->assertEquals(13, $result['DATA']['related'][2]);
        $this->assertInstanceof(Form::class, $result['Form']);
        $this->assertEquals(4, $result['Form']->id);
        $this->assertEquals($user, $result['User']);

        $user->fields['catalog']->deleteValues();
        $material->fields['related']->deleteValues();
    }
}
