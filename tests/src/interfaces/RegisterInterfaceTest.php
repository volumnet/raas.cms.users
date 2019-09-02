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
use RAAS\CMS\Page;
use RAAS\CMS\Package;
use RAAS\CMS\SocialProfile;
use RAAS\CMS\ULogin;
use RAAS\CMS\User;
use RAAS\CMS\User_Field;

/**
 * Класс теста стандартного интерфейса регистрации
 */
class MaterialInterfaceTest extends BaseDBTest
{
    /**
     * Тест регистрации по токену соц. сети
     * (случай с неверным токеном)
     */
    public function testProcessSocialTokenWithInvalidToken()
    {
        $interface = new RegisterInterfaceWithSocialMock();

        $result = $interface->processSocialToken(
            new User(),
            ['token' => 'invalidtoken'],
            [],
            [],
            true
        );

        $this->assertEmpty($result);
        $this->assertEquals([], $interface->session['confirmedSocial']);
    }


    /**
     * Тест регистрации по токену соц. сети
     * (случай с корректным токеном с AJAX)
     */
    public function testProcessSocialTokenWithAJAX()
    {
        $interface = new RegisterInterfaceWithSocialMock();

        $result = $interface->processSocialToken(
            new User(1),
            ['token' => 'sntoken', 'AJAX' => 1],
            [],
            [],
            true
        );

        $this->assertEquals(
            ['https://vk.com/test'],
            $interface->session['confirmedSocial']
        );
        $this->assertEquals('https://vk.com/test', $result['social']);
        $this->assertEquals(SocialProfile::SN_VK, $result['socialNetwork']);
    }


    /**
     * Тест регистрации по токену соц. сети
     * (случай с корректным токеном без AJAX)
     */
    public function testProcessSocialTokenWithOk()
    {
        $user = new User(1);
        $user->deleteSocial('https://vk.com/test');
        $user = new User(1);
        $this->assertNotContains(['https://facebook.com/test'], $user->social);

        $uLogin = new ULogin();
        $reflection = new ReflectionClass($uLogin);
        $reflectionProperty = $reflection->getProperty('profile');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($uLogin, 'https://vk.com/test');
        $interface = $this->getMockBuilder(RegisterInterface::class)
            ->setMethods(['getProfile'])
            ->getMock();
        $interface->expects($this->once())
            ->method('getProfile')
            ->with('sntoken')
            ->willReturn($uLogin);

        $result = $interface->processSocialToken(
            new User(1),
            ['token' => 'sntoken'],
            [],
            ['REQUEST_URI' => '/register/'],
            true
        );

        $user = new User(1);
        $this->assertContains('https://vk.com/test', $user->social);
        $this->assertEquals('/register/', $result['redirect']);
    }


    /**
     * Тест применения редиректа
     * (случай с AJAX)
     */
    public function testCheckRedirectWithAJAX()
    {
        $interface = new RegisterInterface();

        $result = $interface->checkRedirect(['AJAX' => true], [], null, true);

        $this->assertTrue($result);
    }


    /**
     * Тест применения редиректа
     * (случай с явным реферером)
     */
    public function testCheckRedirectWithReferer()
    {
        $interface = new RegisterInterface();

        $result = $interface->checkRedirect([], [], '/referer/', true);

        $this->assertEquals('/referer/', $result);
    }


    /**
     * Тест применения редиректа
     * (случай с HTTP-реферером)
     */
    public function testCheckRedirectWithHTTPReferer()
    {
        $interface = new RegisterInterface();

        $result = $interface->checkRedirect(
            [],
            ['REQUEST_URI' => '/register/'],
            null,
            true
        );

        $this->assertEquals('/register/', $result);
    }


    /**
     * Тест генерации пароля
     */
    public function testGeneratePass()
    {
        $interface = new RegisterInterface();

        $result = $interface->generatePass(10);

        $this->assertRegExp('/[A-Za-z0-9]{10}/umi', $result);
    }


    /**
     * Тест получения заголовок e-mail сообщения
     * @return string
     */
    public function testGetEmailRegisterSubject()
    {
        $interface = new RegisterInterface(
            null,
            null,
            [],
            [],
            [],
            [],
            ['HTTP_HOST' => 'xn--d1acufc.xn--p1ai']
        );

        $result = $interface->getEmailRegisterSubject();

        $this->assertContains(date('d.m.Y H:i'), $result);
        $this->assertContains('Регистрация на сайте ДОМЕН.РФ', $result);
    }


    /**
     * Тест получения списка адресов пользователя
     * (случай с явным полем phone)
     */
    public function testParseUserAddressesWithPhoneField()
    {
        $interface = new RegisterInterface();

        $result = $interface->parseUserAddresses(new User(1));

        $this->assertEquals(['test@test.org'], $result['emails']);
        $this->assertEquals(['+79990000000'], $result['smsPhones']);
    }


    /**
     * Тест получения списка адресов пользователя
     * (случай с определением телефона по типу поля)
     */
    public function testParseUserAddressesWithoutPhoneField()
    {
        $phoneField = new User_Field(37);
        $phoneField->urn = 'tel';
        $phoneField->datatype = 'phone';
        $phoneField->commit();

        $interface = new RegisterInterface();

        $result = $interface->parseUserAddresses(new User(1));

        $this->assertEquals(['test@test.org'], $result['emails']);
        $this->assertEquals(['+79990000000'], $result['smsPhones']);

        $phoneField->urn = 'phone';
        $phoneField->datatype = 'text';
        $phoneField->commit();
    }


    /**
     * Тест проверки правильности заполнения формы
     */
    public function testCheckRegisterForm()
    {
        $imageField = new Form_Field([
            'pid' => 4,
            'name' => 'Изображение',
            'urn' => 'image',
            'datatype' => 'image',
            'required' => 1,
        ]);
        $imageField->commit();
        $interface = new RegisterInterface();

        $result = $interface->checkRegisterForm(
            new User(),
            new Form(4),
            ['login' => 'test', 'email' => 'test@test.org', '_name' => 'aaa'],
            [],
            []
        );

        $this->assertEquals(
            [
                'password' => 'Необходимо заполнить поле «Пароль»',
                'full_name' => 'Необходимо заполнить поле «Ваше имя»',
                'image' => 'Необходимо заполнить поле «Изображение»',
                'login' => 'Пользователь с таким логином уже существует',
                'email' => 'Пользователь с таким адресом электронной почты уже существует',
                '_name' => 'Код с картинки указан неверно',
            ],
            $result
        );

        User_Field::delete($imageField);
    }


    /**
     * Тест проверки правильности заполнения формы
     * (случай без поля e-mail)
     */
    public function testCheckRegisterFormWithoutLoginField()
    {
        $loginField = new Form_Field(38);
        $loginField->urn = 'username';
        $loginField->required = 0;
        $loginField->commit();
        $interface = new RegisterInterface();

        $result = $interface->checkRegisterForm(
            new User(),
            new Form(4),
            [
                'full_name' => 'aaa',
                'email' => 'test',
                'password' => 'pass',
                'password@confirm' => 'pass'
            ],
            [],
            []
        );

        $this->assertEquals(
            ['email' => 'Пользователь с таким логином уже существует'],
            $result
        );

        $loginField->urn = 'login';
        $loginField->required = 1;
        $loginField->commit();
    }


    /**
     * Тест уведомления о заполненной форме
     * (случай отправки администратору)
     */
    public function testNotifyRegisterWithAdmin()
    {
        $form = new Form(4);
        $form->email = 'test@test.org, [79990000000@sms.test.org], [+79990000000]';
        $form->commit();
        $block = Block::spawn(45);
        $page = new Page(30);
        $interface = new RegisterInterface();
        Package::i()->registrySet(
            'sms_gate',
            'http://smsgate/{{PHONE}}/{{TEXT}}/'
        );
        Controller_Frontend::i()->exportLang(Application::i(), $page->lang);
        Controller_Frontend::i()->exportLang(Package::i(), $page->lang);
        Controller_Frontend::i()->exportLang(Module::i(), $page->lang);

        $result = $interface->notifyRegister(
            new User(1),
            $form,
            new Page(30),
            $block->config,
            true,
            true
        );

        $this->assertEquals(['test@test.org'], $result['emails']['emails']);
        $this->assertContains(
            'Регистрация на сайте',
            $result['emails']['subject']
        );
        $this->assertContains('<div>', $result['emails']['message']);
        $this->assertContains(
            'Телефон: +7 999 000-00-00',
            $result['emails']['message']
        );
        $this->assertContains('/admin/', $result['emails']['message']);
        $this->assertContains('Администрация сайта', $result['emails']['from']);
        $this->assertContains('info@', $result['emails']['fromEmail']);
        $this->assertEquals(
            ['79990000000@sms.test.org'],
            $result['smsEmails']['emails']
        );
        $this->assertContains(
            'Регистрация на сайте',
            $result['smsEmails']['subject']
        );
        $this->assertNotContains('<div>', $result['smsEmails']['message']);
        $this->assertContains(
            'Администрация сайта',
            $result['smsEmails']['from']
        );
        $this->assertContains('info@', $result['smsEmails']['fromEmail']);
        $this->assertContains(
            'Телефон: +7 999 000-00-00',
            $result['smsEmails']['message']
        );
        $this->assertContains(
            'smsgate/%2B79990000000/',
            $result['smsPhones'][0]
        );
        $this->assertContains(
            urlencode('Телефон: +7 999 000-00-00'),
            $result['smsPhones'][0]
        );

        $form->email = '';
        $form->commit();
        Package::i()->registrySet('sms_gate', '');
    }


    /**
     * Тест уведомления о заполненной форме
     * (случай отправки пользователю)
     */
    public function testNotifyRegisterWithUser()
    {
        $form = new Form(4);
        $block = Block::spawn(45);
        $page = new Page(30);
        $interface = new RegisterInterface();

        Package::i()->registrySet(
            'sms_gate',
            'http://smsgate/{{PHONE}}/{{TEXT}}/'
        );
        Controller_Frontend::i()->exportLang(Application::i(), $page->lang);
        Controller_Frontend::i()->exportLang(Package::i(), $page->lang);
        Controller_Frontend::i()->exportLang(Module::i(), $page->lang);

        $result = $interface->notifyRegister(
            new User(1),
            $form,
            $page,
            $block->config,
            false,
            true
        );

        $this->assertEquals(['test@test.org'], $result['emails']['emails']);
        $this->assertContains(
            'Регистрация на сайте',
            $result['emails']['subject']
        );
        $this->assertContains('<div>', $result['emails']['message']);
        $this->assertContains(
            'Телефон: +7 999 000-00-00',
            $result['emails']['message']
        );
        $this->assertContains('/activate/', $result['emails']['message']);
        $this->assertContains('Администрация сайта', $result['emails']['from']);
        $this->assertContains('info@', $result['emails']['fromEmail']);
        $this->assertEmpty($result['smsEmails']);
        $this->assertContains(
            'smsgate/%2B79990000000/',
            $result['smsPhones'][0]
        );
        $this->assertContains(
            urlencode('Телефон: +7 999 000-00-00'),
            $result['smsPhones'][0]
        );

        Package::i()->registrySet('sms_gate', '');
    }


    /**
     * Тест уведомления о заполненной форме
     * (случай без интерфейса формы регистрации)
     */
    public function testNotifyRegisterWithoutFormInterface()
    {
        $form = new Form(4);
        $block = Block::spawn(45);
        $page = new Page(30);
        $interface = new RegisterInterface();
        $form->interface_id = 0;

        $result = $interface->notifyRegister(
            new User(1),
            $form,
            $page,
            $block->config,
            false,
            true
        );

        $this->assertEmpty($result);
    }


    /**
     * Тест обработки формы
     */
    public function testProcessRegisterForm()
    {
        $langField = new Form_Field(['urn' => 'lang', 'datatype' => 'text', 'pid' => 4]);
        $langField->commit();
        $ipField = new User_Field(['urn' => 'ip', 'datatype' => 'text']);
        $ipField->commit();
        $dateField = new User_Field(['urn' => 'date', 'datatype' => 'date']);
        $dateField->commit();
        $fullNameField = new User_Field(['urn' => 'full_name', 'datatype' => 'text']);
        $fullNameField->commit();

        $form = new Form(4); // Обратная связь
        $form->email = 'test@test.org';
        $block = Block::spawn(45);
        $block->allow_edit_social = 1;
        $page = new Page(30); // Главная
        $user = new User();
        $post = [
            'login' => 'testuser',
            'password' => 'pass',
            'full_name' => 'Test User 123',
            'phone' => '+7 999 111-11-11',
            'email' => 'test123@test.org',
            'lang' => 'en',
            'social' => [
                'https://vk.com/user123',
            ],
        ];
        $interface = new RegisterInterface();

        $result = $interface->processRegisterForm(
            $user,
            $block,
            $form,
            $page,
            $post,
            ['confirmedSocial' => ['https://vk.com/user123']],
            ['REMOTE_ADDR' => '127.0.0.1'],
            []
        );

        $this->assertInstanceOf(User::class, $result['User']);
        $this->assertEquals($user, $result['User']);

        $user->reload();
        $this->assertEquals('testuser', $user->login);
        $this->assertEquals('pass', $user->password);
        $this->assertEquals('Test User 123', $user->full_name);
        $this->assertEquals('test123@test.org', $user->email);
        $this->assertEquals('en', $user->lang);
        $this->assertContains('https://vk.com/user123', $user->social);
        $this->assertEmpty($interface->session['confirmedSocial']);
        $this->assertEquals('127.0.0.1', $user->ip);
        $this->assertEquals(date('Y-m-d'), $user->date);
        $this->assertEquals(30, $user->page_id);
        $this->assertEquals(30, $user->page->id);
        $this->assertEquals(0, $user->vis);
        $this->assertEquals(1, $user->new);

        User_Field::delete($langField);
        User_Field::delete($ipField);
        User_Field::delete($dateField);
        User_Field::delete($fullNameField);
        User::delete($user);
    }


    /**
     * Тест обработки формы
     * (случай с генерацией пароля и автоактивацией, с e-mail вместо логина)
     */
    public function testProcessRegisterFormWithPasswordAutogenAndActivatedAndNotLoginField()
    {
        $block = Block::spawn(45);
        $block->activation_type = Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED;
        $block->email_as_login = 1;
        $page = new Page(30); // Главная
        $user = new User();
        $loginField = new Form_Field(38);
        $loginField->urn = 'aaa';
        $loginField->required = 0;
        $loginField->commit();
        $post = [
            'full_name' => 'Test User 123',
            'phone' => '+7 999 111-11-11',
            'email' => 'test123@test.org',
        ];
        $interface = new RegisterInterface();

        $result = $interface->processRegisterForm(
            $user,
            $block,
            $block->Register_Form,
            $page,
            $post,
            [],
            [],
            []
        );

        $this->assertEquals(5, mb_strlen($user->password));
        $this->assertEquals(1, $user->vis);
        $this->assertEquals('test123@test.org', $user->login);
        $this->assertEquals('ru', $user->lang);

        $loginField->urn = 'login';
        $loginField->required = 1;
        $loginField->commit();
        User::delete($user);
    }


    /**
     * Тест обработки
     */
    public function testProcess()
    {
        $block = Block::spawn(45);
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User();
        $post = [
            'login' => 'testuser',
            'password' => 'pass',
            'password@confirm' => 'pass',
            'full_name' => 'Test User 123',
            'phone' => '+7 999 111-11-11',
            'email' => 'test123@test.org',
            'form_signature' => md5('form445')
        ];
        $postWithoutPassword = $post;
        unset(
            $postWithoutPassword['password'],
            $postWithoutPassword['password@confirm']
        );

        $interface = new RegisterInterface(
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
        $this->assertTrue($result['success'][45]);
        $this->assertEquals($postWithoutPassword, $result['DATA']);
        $this->assertInstanceof(Form::class, $result['Form']);
        $this->assertEquals(4, $result['Form']->id);
        $this->assertEquals($user, $result['User']);
    }


    /**
     * Тест обработки
     * (случай с существующим пользователем)
     */
    public function testProcessWithUser()
    {
        $block = Block::spawn(45);
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User(1);
        $post = [
            'login' => 'test',
            'full_name' => 'Test User',
            'phone' => '+7 999 000-00-00',
            'email' => 'test@test.org',
            'form_signature' => md5('form445'),
            'AJAX' => 1,
        ];

        $interface = new RegisterInterface(
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

        $this->assertTrue($result['success'][45]);
    }


    /**
     * Тест обработки
     * (случай регистрации через соц. сеть)
     */
    public function testProcessWithSocialRegister()
    {
        $block = Block::spawn(45);
        $block->allow_edit_social = 1;
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User();
        $post = [
            'token' => 'sntoken',
            'AJAX' => 1,
        ];

        $interface = new RegisterInterfaceWithSocialMock(
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

        $this->assertEquals(
            ['https://vk.com/test'],
            $interface->session['confirmedSocial']
        );
        $this->assertEquals('https://vk.com/test', $result['social']);
        $this->assertEquals(SocialProfile::SN_VK, $result['socialNetwork']);
    }


    /**
     * Тест обработки
     * (случай без отправки формы)
     */
    public function testProcessWithNoFormData()
    {
        $block = Block::spawn(45);
        $block->allow_edit_social = 1;
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User();
        $post = [];

        $interface = new RegisterInterface(
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
        $this->assertEmpty($result['DATA']['login']);
        $this->assertEmpty($result['DATA']['email']);
        $this->assertEmpty($result['DATA']['password']);
        $this->assertEmpty($result['DATA']['password_md5']);
        $this->assertEmpty($result['DATA']['password@confirm']);
        $this->assertInstanceof(Form::class, $result['Form']);
        $this->assertEquals(4, $result['Form']->id);
        $this->assertEquals($user, $result['User']);
        $this->assertEquals('Регистрация', $page->getH1());
        $this->assertEmpty($result['social']);
    }


    /**
     * Тест обработки
     * (случай с существующим пользователем и без отправки формы)
     */
    public function testProcessWithUserAndNoFormData()
    {
        $block = Block::spawn(45);
        $block->allow_edit_social = 1;
        $page = new Page(30); // Главная
        $user = Controller_Frontend::i()->user = new User(1);
        $post = [];
        $postWithoutPassword = $post;
        unset(
            $postWithoutPassword['password'],
            $postWithoutPassword['password@confirm']
        );

        $interface = new RegisterInterface(
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
        $this->assertEquals('test', $result['DATA']['login']);
        $this->assertEquals('test@test.org', $result['DATA']['email']);
        $this->assertEmpty($result['DATA']['password']);
        $this->assertEmpty($result['DATA']['password_md5']);
        $this->assertEmpty($result['DATA']['password@confirm']);
        $this->assertInstanceof(Form::class, $result['Form']);
        $this->assertEquals(4, $result['Form']->id);
        $this->assertEquals($user, $result['User']);
        $this->assertEquals('Редактирование профиля', $page->getH1());
        $this->assertEquals('Редактирование профиля', $page->meta_title);
        $this->assertContains('https://vk.com/test', $result['DATA']['social']);
    }
}
