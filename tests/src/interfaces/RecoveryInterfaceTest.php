<?php
/**
 * Файл теста стандартного интерфейса восстановления пароля
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\Application;
use RAAS\Controller_Frontend;
use RAAS\CMS\Block;
use RAAS\CMS\Page;
use RAAS\CMS\Package;
use RAAS\CMS\User;

/**
 * Класс теста стандартного интерфейса восстановления пароля
 * @covers RAAS\CMS\Users\RecoveryInterface
 */
class RecoveryInterfaceTest extends BaseTest
{
    public static $tables = [
        'cms_blocks',
        'cms_data',
        'cms_fields',
        'cms_pages',
        'cms_snippets',
        'cms_users',
        'cms_users_blocks_recovery',
        'registry',
    ];

    /**
     * Тест получения заголовка e-mail сообщения
     */
    public function testGetEmailRecoverySubject()
    {
        $interface = new RecoveryInterface(
            null,
            null,
            [],
            [],
            [],
            [],
            ['HTTP_HOST' => 'xn--d1acufc.xn--p1ai']
        );

        $result = $interface->getEmailRecoverySubject();

        $this->assertStringContainsString(date('d.m.Y H:i'), $result);
        $this->assertStringContainsString('Восстановление пароля на сайте ДОМЕН.РФ', $result);
    }


    /**
     * Тест уведомления пользователя о восстановлении пароля
     */
    public function testNotifyRecovery()
    {
        $block = Block::spawn(47); // Блок восстановления пароля
        $page = new Page(33); // Страница восстановления пароля
        $interface = new RecoveryInterface();
        Package::i()->registrySet(
            'sms_gate',
            'http://smsgate/{{PHONE}}/{{TEXT}}/'
        );
        Controller_Frontend::i()->exportLang(Application::i(), $page->lang);
        Controller_Frontend::i()->exportLang(Package::i(), $page->lang);
        Controller_Frontend::i()->exportLang(Module::i(), $page->lang);

        $result = $interface->notifyRecovery(
            new User(1),
            $page,
            $block->config,
            true
        );

        $this->assertEquals(['test@test.org'], $result['emails']['emails']);
        $this->assertStringContainsString('Восстановление пароля на сайте', $result['emails']['subject']);
        $this->assertStringContainsString(date('d.m.Y H:i'), $result['emails']['message']);
        $this->assertStringContainsString('Вы запросили восстановление пароля на сайте', $result['emails']['message']);
        $this->assertStringContainsString('/recovery/?key=', $result['emails']['message']);
        $this->assertStringContainsString('Администрация сайта', $result['emails']['from']);
        $this->assertStringContainsString('info@', $result['emails']['fromEmail']);
        $this->assertNull($result['smsEmails'] ?? null);
        $this->assertNull($result['smsPhones'] ?? null);

        Package::i()->registrySet('sms_gate', '');
    }


    /**
     * Тест уведомления пользователя о восстановлении пароля
     * Случай, когда не указан шаблон
     */
    public function testNotifyRecoveryWithoutTemplate()
    {
        $block = Block::spawn(47); // Блок восстановления пароля
        $page = new Page(33); // Страница восстановления пароля
        $interface = new RecoveryInterface();
        $config = $block->config;
        $config['notification_id'] = 0;

        $result = $interface->notifyRecovery(
            new User(1),
            $page,
            $config,
            true
        );

        $this->assertNull($result);
    }


    /**
     * Тест отработки интерфейса
     * Случай, когда пользователь просто зашел на страницу
     */
    public function testProcessWithEmptyData()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            [],
            [],
            [],
            [],
            []
        );

        $result = $interface->process();

        $this->assertEquals(['localError', 'User'], array_keys($result));
        $this->assertNull($result['User']->id);
        $this->assertEmpty($result['localError']);

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай, когда пользователь ввел некорректные данные
     */
    public function testProcessWithInvalidData()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            [],
            ['login' => 'unexisting@test.org'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process();

        $this->assertEquals(['localError', 'User'], array_keys($result));
        $this->assertNull($result['User']->id);
        $this->assertEquals(
            ['login' => 'Пользователь с таким логином/e-mail не найден'],
            $result['localError']
        );

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай, когда пользователь ввел данные заблокированного пользователя
     */
    public function testProcessWithBlockedAccountData()
    {
        $user = new User(1);
        $user->vis = 0;
        $user->commit();
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            [],
            ['login' => 'test@test.org'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process();

        $this->assertEquals(['localError', 'User'], array_keys($result));
        $this->assertNull($result['User']->id);
        $this->assertEquals(
            ['login' => 'Ваша учетная запись заблокирована'],
            $result['localError']
        );

        $user->vis = 1;
        $user->commit();
        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай, когда пользователь ввел данные пользователя без e-mail
     */
    public function testProcessWithNoEmailedUserData()
    {
        $user = new User(1);
        $user->email = '';
        $user->commit();
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            [],
            ['login' => 'test'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process();

        $this->assertEquals(['localError', 'User'], array_keys($result));
        $this->assertNull($result['User']->id);
        $this->assertEquals(
            [
                'login' => 'У данного пользователя не указан '
                        .  'адрес электронной почты. '
                        .  'Пожалуйста, обратитесь к администратору сайта.'
            ],
            $result['localError']
        );

        $user->email = 'test@test.org';
        $user->commit();
        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с вводом корректных данных (пользователь получает письмо)
     */
    public function testProcessWithCorrectData()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            [],
            ['login' => 'test@test.org'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process();

        $this->assertEquals(
            ['success', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertNull($result['User']->id);
        $this->assertEmpty($result['localError']);
        $this->assertTrue($result['success']);

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с входом по неправильному ключу
     */
    public function testProcessStep2WithInvalidKey()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => 'invalidkey'],
            [],
            [],
            [],
            []
        );

        $result = $interface->process();

        $this->assertEquals(
            ['proceed', 'key_is_invalid', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertNull($result['User']->id);
        $this->assertEquals(
            ['Ключ подтверждения не верен'],
            $result['localError']
        );
        $this->assertTrue($result['key_is_invalid']);

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с входом по ключу заблокированного пользователя
     */
    public function testProcessStep2WithBlockedAccountKey()
    {
        $user = new User(1);
        $user->vis = 0;
        $user->commit();
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => $user->recoveryKey],
            [],
            [],
            [],
            []
        );

        $result = $interface->process();

        $this->assertEquals(
            ['proceed', 'key_is_invalid', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user, $result['User']->id);
        $this->assertEquals(
            ['Ваша учетная запись заблокирована'],
            $result['localError']
        );
        $this->assertTrue($result['key_is_invalid']);

        $user->vis = 1;
        $user->commit();
        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с входом по ключу без действий
     */
    public function testProcessStep2WithNoAction()
    {
        $user = new User(1);
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => $user->recoveryKey],
            [],
            [],
            [],
            []
        );

        $result = $interface->process();

        $this->assertEquals(
            ['proceed', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEmpty($result['localError']);

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с входом по ключу и отправки данных без пароля
     */
    public function testProcessStep2WithNoPassword()
    {
        $user = new User(1);
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => $user->recoveryKey],
            ['password' => ''],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process();

        $this->assertEquals(
            ['proceed', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['password' => 'Необходимо указать пароль'],
            $result['localError']
        );

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с входом по ключу и отправки данных с неправильным
     * подтверждением пароля
     */
    public function testProcessStep2WithInvalidPasswordConfirmation()
    {
        $user = new User(1);
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => $user->recoveryKey],
            ['password' => 'aaa', 'password@confirm' => 'bbb'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process();

        $this->assertEquals(
            ['proceed', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['password' => 'Пароль и его подтверждение не совпадают'],
            $result['localError']
        );

        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с входом по ключу и отправки корректных данных (пароль сменен)
     */
    public function testProcess()
    {
        $user = new User(1);
        $oldPasswordMD5 = $user->password_md5;
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => $user->recoveryKey],
            ['password' => 'aaa', 'password@confirm' => 'aaa'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process();

        $this->assertEquals(
            ['proceed', 'success', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEmpty($result['localError']);
        $this->assertTrue($result['success'][47] ?? null);

        $user = new User(1);
        $this->assertEquals(Application::i()->md5It('aaa'), $user->password_md5);

        $user->password_md5 = $oldPasswordMD5;
        $user->commit();
        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с редиректом в POST-переменной
     */
    public function testProcessWithPostReferer()
    {
        $user = new User(1);
        $oldPasswordMD5 = $user->password_md5;
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => $user->recoveryKey],
            ['password' => 'aaa', 'password@confirm' => 'aaa', 'HTTP_REFERER' => 'https://test.org/'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process(true);

        $this->assertEquals(
            ['proceed', 'success', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEmpty($result['localError']);
        $this->assertEquals('https://test.org/', $result['success'][47] ?? null);

        $user = new User(1);
        $this->assertEquals(Application::i()->md5It('aaa'), $user->password_md5);

        $user->password_md5 = $oldPasswordMD5;
        $user->commit();
        Controller_Frontend::i()->user = new User();
    }


    /**
     * Тест отработки интерфейса
     * Случай с редиректом в GET-переменной
     */
    public function testProcessWithGetReferer()
    {
        $user = new User(1);
        $oldPasswordMD5 = $user->password_md5;
        Controller_Frontend::i()->user = new User();
        $interface = new RecoveryInterface(
            Block::spawn(47),
            new Page(33),
            ['key' => $user->recoveryKey, 'HTTP_REFERER' => 'https://test.org/'],
            ['password' => 'aaa', 'password@confirm' => 'aaa'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST']
        );

        $result = $interface->process(true);

        $this->assertEquals(
            ['proceed', 'success', 'localError', 'User'],
            array_keys($result)
        );
        $this->assertTrue($result['proceed']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEmpty($result['localError']);
        $this->assertEquals('https://test.org/', $result['success'][47] ?? null);

        $user = new User(1);
        $this->assertEquals(Application::i()->md5It('aaa'), $user->password_md5);

        $user->password_md5 = $oldPasswordMD5;
        $user->commit();
        Controller_Frontend::i()->user = new User();
    }
}
