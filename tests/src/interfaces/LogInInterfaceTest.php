<?php
/**
 * Файл теста стандартного интерфейса входа в систему
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Auth;
use RAAS\CMS\User;

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
use RAAS\CMS\User_Field;

/**
 * Класс теста стандартного интерфейса входа в систему
 */
class LogInInterfaceTest extends BaseDBTest
{
    /**
     * Тест проверки, нужно ли сохранять пароль
     * Случай с проверкой на флажок "Сохранять пароль" и установленным флажком
     */
    public function testCheckIfSavePasswordWithSavePassword()
    {
        $block = new Block_LogIn([
            'password_save_type' => Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD
        ]);
        $post = ['save_password' => 1];
        $interface = new LogInInterface();

        $result = $interface->checkIfSavePassword($block, $post);

        $this->assertTrue($result);
    }


    /**
     * Тест проверки, нужно ли сохранять пароль
     * Случай с проверкой на флажок "Чужой компьютер" и установленным флажком
     */
    public function testCheckIfSavePasswordWithForeignComputer()
    {
        $block = new Block_LogIn([
            'password_save_type' => Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER
        ]);
        $post = ['foreign_computer' => 1];
        $interface = new LogInInterface();

        $result = $interface->checkIfSavePassword($block, $post);

        $this->assertFalse($result);
    }


    /**
     * Тест проверки, нужно ли сохранять пароль
     * Случай с проверкой на флажок "Чужой компьютер" и не установленным флажком
     */
    public function testCheckIfSavePasswordWithHomeComputer()
    {
        $block = new Block_LogIn([
            'password_save_type' => Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER
        ]);
        $post = [];
        $interface = new LogInInterface();

        $result = $interface->checkIfSavePassword($block, $post);

        $this->assertTrue($result);
    }


    /**
     * Тест опознания пользователя по e-mail и при необходимости добавления его
     * социального профиля
     * Случай, когда пользователь найден
     */
    public function testProcessSocialLoginByEmail()
    {
        $interface = new LogInInterfaceWithSocialMock();
        $profile = $interface->getProfile('testProcessSocialLoginByEmail');

        $result = $interface->processSocialLoginByEmail($profile);

        $this->assertInstanceOf(Auth::class, $result);
        $this->isInstanceOf(User::class, $result->user);
        $user = $result->user;
        $this->assertEquals(1, $user->id);
        $this->assertContains('https://vk.com/test1', $user->social);

        $user->meta_social = ['https://facebook.com/test', 'https://vk.com/test'];
        $user->commit();
    }


    /**
     * Тест опознания пользователя по e-mail и при необходимости добавления его
     * социального профиля
     * Случай, когда пользователь не найден
     */
    public function testProcessSocialLoginByEmailWithNotFound()
    {
        $interface = new LogInInterfaceWithSocialMock();
        $profile = $interface->getProfile('testProcessSocialLoginByEmailWithNotFound');

        $result = $interface->processSocialLoginByEmail($profile);

        $this->assertNull($result);
    }


    /**
     * Тест быстрой регистрации пользователя по профилю в соц. сети
     */
    public function testProcessSocialLoginQuickRegister()
    {
        $interface = new LogInInterfaceWithSocialMock();
        $profile = $interface->getProfile('testProcessSocialLoginQuickRegister');

        $result = $interface->processSocialLoginQuickRegister($profile);

        $this->isInstanceOf(Auth::class, $result);
        $this->isInstanceOf(User::class, $result->user);
        $user = $result->user;
        $this->assertEquals('test_1', $user->login);
        $this->assertEquals('test3@test.org', $user->email);
        $this->assertEquals('Test', $user->last_name);
        $this->assertEquals('User', $user->first_name);
        $this->assertEquals(['https://test-social.com/test3-profile'], $user->social);

        User::delete($user);
    }


    /**
     * Тест быстрой регистрации пользователя по профилю в соц. сети
     * Случай без никнейма
     */
    public function testProcessSocialLoginQuickRegisterWithoutNickName()
    {
        $interface = new LogInInterfaceWithSocialMock();
        $profile = $interface->getProfile('testProcessSocialLoginQuickRegisterWithoutNickName');

        $result = $interface->processSocialLoginQuickRegister($profile);

        $this->isInstanceOf(Auth::class, $result);
        $this->isInstanceOf(User::class, $result->user);
        $user = $result->user;
        $this->assertEquals('test4aaa', $user->login);
        $this->assertEquals('test4@test.org', $user->email);
        $this->assertEquals('Test', $user->last_name);
        $this->assertEquals('User', $user->first_name);
        $this->assertEquals(['https://test-social.com/test4aaa'], $user->social);

        User::delete($user);
    }


    /**
     * Тест обработки интерфейса
     * Случай без данных
     */
    public function testProcessWithoutData()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            [],
            [],
            [],
            [],
            [],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEquals([], $result['localError']);
        $this->assertNull($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с выходом из системы
     */
    public function testProcessWithLogout()
    {
        Controller_Frontend::i()->user = new User(1);
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            ['logout' => 1],
            ['AJAX' => 1],
            [],
            [],
            [],
            []
        );
        $this->assertEquals(1, Controller_Frontend::i()->user->id);

        $result = @$interface->process(); // Модифицирует cookies

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user->id);
        $this->assertEquals([], $result['localError']);
        $this->assertTrue($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с авторизованным пользователем
     */
    public function testProcessWithLoggedInUser()
    {
        Controller_Frontend::i()->user = new User(1);
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1],
            [],
            [],
            [],
            []
        );
        $this->assertEquals(1, Controller_Frontend::i()->user->id);

        $result = @$interface->process(); // Модифицирует cookies

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEquals([], $result['localError']);
        $this->assertTrue($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с авторизацией по соц. сети
     */
    public function testProcessWithLoginBySocialNetwork()
    {
        Controller_Frontend::i()->user = new User();
        $block = Block::spawn(46); // Блок входа в систему
        $block->social_login_type = Block_LogIn::SOCIAL_LOGIN_QUICK_REGISTER;
        $interface = new LogInInterfaceWithSocialMock(
            $block,
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1, 'token' => 'testProcessSocialLoginByToken'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEquals([], $result['localError']);
        $this->assertTrue($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с авторизацией по соц. сети (совпадение e-mail)
     */
    public function testProcessWithLoginBySocialEmail()
    {
        Controller_Frontend::i()->user = new User();
        $block = Block::spawn(46); // Блок входа в систему
        $block->social_login_type = Block_LogIn::SOCIAL_LOGIN_QUICK_REGISTER;
        $interface = new LogInInterfaceWithSocialMock(
            $block,
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1, 'token' => 'testProcessSocialLoginByEmail'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertContains('https://vk.com/test1', $result['User']->social);
        $this->assertEquals([], $result['localError']);
        $this->assertTrue($result['success']);

        $user = $result['User'];
        $user->meta_social = ['https://facebook.com/test', 'https://vk.com/test'];
        $user->commit();
    }


    /**
     * Тест обработки интерфейса
     * Случай с быстрой регистрацией по соц. сети
     */
    public function testProcessWithLoginBySocialQuickRegistration()
    {
        Controller_Frontend::i()->user = new User();
        $block = Block::spawn(46); // Блок входа в систему
        $block->social_login_type = Block_LogIn::SOCIAL_LOGIN_QUICK_REGISTER;
        $interface = new LogInInterfaceWithSocialMock(
            $block,
            new Page(32), // Страница входа в систему
            [],
            [
                'AJAX' => 1,
                'token' => 'testProcessSocialLoginQuickRegister',
                'HTTP_REFERER' => '/cabinet/'
            ],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEquals(Controller_Frontend::i()->user->id, $result['User']->id);
        $this->assertEquals('test_1', $result['User']->login);
        $this->assertEquals('test3@test.org', $result['User']->email);
        $this->assertEquals('Test', $result['User']->last_name);
        $this->assertEquals('User', $result['User']->first_name);
        $this->assertEquals(['https://test-social.com/test3-profile'], $result['User']->social);
        $this->assertEquals([], $result['localError']);
        $this->assertTrue($result['success']);

        User::delete($result['User']);
    }


    /**
     * Тест обработки интерфейса
     * Случай без быстрой регистрацией по соц. сети
     */
    public function testProcessWithNoQuickSocialRegistration()
    {
        Controller_Frontend::i()->user = new User();
        $block = Block::spawn(46); // Блок входа в систему
        $block->social_login_type = Block_LogIn::SOCIAL_LOGIN_ONLY_REGISTERED;
        $interface = new LogInInterfaceWithSocialMock(
            $block,
            new Page(32), // Страница входа в систему
            [],
            [
                'AJAX' => 1,
                'token' => 'testProcessSocialLoginQuickRegister',
                'HTTP_REFERER' => '/cabinet/'
            ],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['Пользователь с такой социальной сетью не найден'],
            $result['localError']
        );
        $this->assertNull($result['success']);

        User::delete($result['User']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с некорректным токеном соц. сети
     */
    public function testProcessWithInvalidSocial()
    {
        Controller_Frontend::i()->user = new User();
        $block = Block::spawn(46); // Блок входа в систему
        $block->social_login_type = Block_LogIn::SOCIAL_LOGIN_QUICK_REGISTER;
        $interface = new LogInInterfaceWithSocialMock(
            $block,
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1, 'token' => 'invalidtoken'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['Не могу получить профиль социальной сети'],
            $result['localError']
        );
        $this->assertNull($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с отсутствием логина
     */
    public function testProcessWithNoLogin()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['login' => 'Необходимо указать логин'],
            $result['localError']
        );
        $this->assertNull($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с отсутствием пароля
     */
    public function testProcessWithNoPassword()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1, 'login' => 'login'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['password' => 'Необходимо указать пароль'],
            $result['localError']
        );
        $this->assertNull($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с неправильными логином и/или паролем
     */
    public function testProcessWithInvalidLoginPassword()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1, 'login' => 'login', 'password' => 'password'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['Неверные имя пользователя и/или пароль'],
            $result['localError']
        );
        $this->assertNull($result['success']);
    }


    /**
     * Тест обработки интерфейса
     * Случай с заблокированным пользователем
     */
    public function testProcessWithBlockedUser()
    {
        $user = new User(2);
        $user->vis = 0;
        $user->commit();

        Controller_Frontend::i()->user = new User();
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            [],
            ['AJAX' => 1, 'login' => 'test2', 'password' => 'test'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEmpty($result['User']->id);
        $this->assertEmpty(Controller_Frontend::i()->user->id);
        $this->assertEquals(
            ['Ваша учетная запись заблокирована'],
            $result['localError']
        );
        $this->assertNull($result['success']);

        $user = new User(2);
        $user->vis = 1;
        $user->commit();
    }


    /**
     * Тест обработки интерфейса
     * Случай нормальной авторизации
     */
    public function testProcessWithOk()
    {
        Controller_Frontend::i()->user = new User();
        $interface = new LogInInterface(
            Block::spawn(46), // Блок входа в систему
            new Page(32), // Страница входа в систему
            ['HTTP_REFERER' => '/cabinet/'],
            ['AJAX' => 1, 'login' => 'test', 'password' => 'test'],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            []
        );

        $result = $interface->process();

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEquals(1, $result['User']->id);
        $this->assertEquals(1, Controller_Frontend::i()->user->id);
        $this->assertEquals([], $result['localError']);
        $this->assertTrue($result['success']);
    }
}
