<?php
/**
 * Файл теста стандартного интерфейса активации учетной записи
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Controller_Frontend;
use RAAS\CMS\Block;
use RAAS\CMS\Page;
use RAAS\CMS\User;

/**
 * Класс теста стандартного интерфейса активации учетной записи
 * @covers RAAS\CMS\Users\ActivationInterface
 */
class ActivationInterfaceTest extends BaseTest
{
    public static $tables = [
        'cms_blocks',
        'cms_fields',
        'cms_pages',
        'cms_users',
    ];

    /**
     * Тест отработки интерфейса
     * Случай с входом на страницу без данных
     */
    public function testProcessWithoutData()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new ActivationInterface(Block::spawn(48), new Page(31));

        $result = $interface->process();

        $this->assertEquals(['localError', 'User'], array_keys($result));
        $this->assertNull($result['User']->id);
        $this->assertNull(Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['Ключ подтверждения не верен'],
            $result['localError']
        );

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с входом на страницу с уже активированного пользователя
     */
    public function testProcessWithAlreadyActivated()
    {
        Controller_Frontend::i()->user = new User(1);
        $interface = new ActivationInterface(Block::spawn(48), new Page(31));

        $result = $interface->process();

        $this->assertEquals(['localError', 'User'], array_keys($result));
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['Ваша учетная запись уже активирована'],
            $result['localError']
        );

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай нормальной активации
     */
    public function testProcess()
    {
        $user = new User(1);
        $user->vis = 0;
        $user->commit();

        Controller_Frontend::i()->user = new User();
        $interface = new ActivationInterface(
            Block::spawn(48),
            new Page(31),
            ['key' => $user->activationKey]
        );

        $result = $interface->process();

        $this->assertEquals(
            ['success', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEquals(1, $result['User']->vis);
        $this->assertEmpty($result['localError']);
        $user = new User(1);
        $this->assertEquals(1, $user->vis);

        Controller_Frontend::i()->user = new User();
    }
}
