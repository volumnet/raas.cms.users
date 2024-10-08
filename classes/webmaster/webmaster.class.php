<?php
namespace RAAS\CMS\Users;

use RAAS\Application;
use RAAS\CMS\Block_PHP;
use RAAS\CMS\CMSAccess;
use RAAS\CMS\Form;
use RAAS\CMS\Form_Field;
use RAAS\CMS\Page;
use RAAS\CMS\Snippet;
use RAAS\CMS\Snippet_Folder;
use RAAS\CMS\User_Field;
use RAAS\CMS\Webmaster as CMSWebmaster;

class Webmaster extends CMSWebmaster
{
    protected static $instance;

    public function __get($var)
    {
        switch ($var) {
            case 'Site':
            case 'interfacesFolder':
            case 'widgetsFolder':
                return parent::__get($var);
                break;
            default:
                return Module::i()->__get($var);
                break;
        }
    }


    /**
     * Создаем стандартные сниппеты
     */
    public function checkStdInterfaces()
    {
        $interfaces = [];
        $interfaces['__raas_users_register_notify'] = $this->checkSnippet(
            $this->interfacesFolder,
            '__raas_users_register_notify',
            'users/register_notification.php'
        );
        $interfaces['__raas_users_recovery_notify'] = $this->checkSnippet(
            $this->interfacesFolder,
            '__raas_users_recovery_notify',
            'users/recovery_notification.php'
        );
        return $interfaces;
    }


    /**
     * Добавим виджеты
     * @return array[Snippet] Массив созданных или существующих виджетов
     */
    public function createWidgets()
    {
        $widgets = [];
        $widgetsData = [
            'register' => View_Web::i()->_('REGISTRATION'),
            'activation' => View_Web::i()->_('ACTIVATION'),
            'login' => View_Web::i()->_('LOG_IN'),
            'recovery' => View_Web::i()->_('PASSWORD_RECOVERY'),
            'user_ajax' => View_Web::i()->_('USER_AJAX'),
            'menu_user' => View_Web::i()->_('USER_MENU_BLOCK'),
        ];
        foreach ($widgetsData as $url => $name) {
            $urn = explode('/', $url);
            $urn = $urn[count($urn) - 1];
            $widget = Snippet::importByURN($urn);
            if (!($widget && $widget->id)) {
                $widget = $this->createSnippet(
                    $urn,
                    $name,
                    (int)$this->widgetsFolder->id,
                    Module::i()->resourcesDir . '/widgets/' . $url . '.tmp.php',
                    [
                        'WIDGET_NAME' => $name,
                        'WIDGET_URN' => $urn,
                        'WIDGET_CSS_CLASSNAME' => str_replace('_', '-', $urn)
                    ]
                );
            }
            $widgets[$urn] = $widget;
        }

        return $widgets;
    }


    /**
     * Создает личный кабинет
     */
    public function createCab()
    {
        ini_set('max_execution_time', 3600);
        $interfaces = $this->checkStdInterfaces();
        $widgets = $this->createWidgets();

        $lastNameField = new User_Field([
            'name' => $this->view->_('LAST_NAME'),
            'urn' => 'last_name',
            'datatype' => 'text',
            'show_in_table' => false,
        ]);
        $lastNameField->commit();

        $firstNameField = new User_Field([
            'name' => $this->view->_('FIRST_NAME'),
            'urn' => 'first_name',
            'datatype' => 'text',
            'show_in_table' => true,
        ]);
        $firstNameField->commit();

        $secondNameField = new User_Field([
            'name' => $this->view->_('SECOND_NAME'),
            'urn' => 'second_name',
            'datatype' => 'text',
            'show_in_table' => false,
        ]);
        $secondNameField->commit();

        $phoneField = new User_Field([
            'name' => $this->view->_('PHONE'),
            'urn' => 'phone',
            'datatype' => 'text',
            'show_in_table' => true,
        ]);
        $phoneField->commit();

        $pagesSet = Page::getSet([
            'where' => "urn = 'ajax' AND pid = " . (int)$this->Site->id
        ]);
        $ajax = array_shift($pagesSet);

        $this->createForms([
            [
                'name' => $this->view->_('REGISTRATION_FORM'),
                'urn' => 'register',
                'interface_id' => (int)Snippet::importByURN('__raas_users_register_notify')->id,
                'fields' => [
                    [
                        'name' => $this->view->_('EMAIL'),
                        'urn' => 'email',
                        'datatype' => 'email',
                        'required' => true,
                        'show_in_table' => true,
                    ],
                    [
                        'name' => $this->view->_('PASSWORD'),
                        'urn' => 'password',
                        'datatype' => 'password',
                        'required' => true,
                        'show_in_table' => true,
                    ],
                    [
                        'name' => $this->view->_('LAST_NAME'),
                        'urn' => 'last_name',
                        'datatype' => 'text',
                        'required' => true,
                        'show_in_table' => false,
                    ],
                    [
                        'name' => $this->view->_('FIRST_NAME'),
                        'urn' => 'first_name',
                        'datatype' => 'text',
                        'required' => true,
                        'show_in_table' => true,
                    ],
                    [
                        'name' => $this->view->_('SECOND_NAME'),
                        'urn' => 'second_name',
                        'datatype' => 'text',
                        'show_in_table' => false,
                    ],
                    [
                        'name' => $this->view->_('PHONE'),
                        'urn' => 'phone',
                        'datatype' => 'tel',
                        'show_in_table' => true,
                    ],
                    [
                        'vis' => 1,
                        'name' => View_Web::i()->_('AGREE_PRIVACY_POLICY'),
                        'urn' => 'agree',
                        'required' => true,
                        'datatype' => 'checkbox',
                    ],
                ],
            ],
            [
                'name' => $this->view->_('EDIT_PROFILE'),
                'urn' => 'edit_profile',
                'interface_id' => (int)Snippet::importByURN('__raas_users_register_notify')->id,
                'fields' => [
                    [
                        'name' => $this->view->_('EMAIL'),
                        'urn' => 'email',
                        'datatype' => 'email',
                        'required' => true,
                        'show_in_table' => true,
                    ],
                    [
                        'name' => $this->view->_('PASSWORD'),
                        'urn' => 'password',
                        'datatype' => 'password',
                        'required' => true,
                        'show_in_table' => true,
                    ],
                    [
                        'name' => $this->view->_('LAST_NAME'),
                        'urn' => 'last_name',
                        'datatype' => 'text',
                        'required' => true,
                        'show_in_table' => false,
                    ],
                    [
                        'name' => $this->view->_('FIRST_NAME'),
                        'urn' => 'first_name',
                        'datatype' => 'text',
                        'required' => true,
                        'show_in_table' => true,
                    ],
                    [
                        'name' => $this->view->_('SECOND_NAME'),
                        'urn' => 'second_name',
                        'datatype' => 'text',
                        'show_in_table' => false,
                    ],
                    [
                        'name' => $this->view->_('PHONE'),
                        'urn' => 'phone',
                        'datatype' => 'tel',
                        'show_in_table' => true,
                    ],
                ],
            ],
        ]);

        $register = $this->createRegister();
        $profile = $this->createEditProfile();
        $activation = $this->createActivation();
        $login = $this->createLogIn();
        $recovery = $this->createRecovery();

        if ((Snippet::importByURN('my_orders')->id ?? null) && class_exists('RAAS\\CMS\\Shop\\MyOrdersInterface')) {
            $myOrders = $this->createMyOrders();
        }

        $this->createBlock(
            new Block_PHP(['name' => View_Web::i()->_('USER_MENU')]),
            'menu_user',
            null,
            'menu_user',
            $this->site,
            true
        );
        $this->createAJAXUser($ajax);
    }


    /**
     * Создает страницу регистрации
     * @return Page Созданная или существующая страница
     */
    public function createRegister()
    {
        $set = Page::getSet([
            'where' => "urn = 'register' AND pid = " . (int)$this->Site->id
        ]);
        if ($set) {
            $register = $set[0];
            $register->trust();
        } else {
            $register = $this->createPage(
                [
                    'name' => $this->view->_('REGISTRATION'),
                    'urn' => 'register',
                    'cache' => 0,
                    'response_code' => 200
                ],
                $this->Site
            );
            $access = new CMSAccess([
                'page_id' => $register->id,
                'to_type' => CMSAccess::TO_ALL,
                'allow' => 0,
                'priority' => 0,
            ]);
            $access->commit();
            $access = new CMSAccess([
                'page_id' => $register->id,
                'to_type' => CMSAccess::TO_UNREGISTERED,
                'allow' => 1,
                'priority' => 1,
            ]);
            $access->commit();

            $this->createBlock(
                new Block_Register([
                    'form_id' => Form::importByURN('register')->id,
                    'email_as_login' => true,
                    'notify_about_edit' => false,
                    'allow_edit_social' => true,
                    'activation_type' => Block_Register::ACTIVATION_TYPE_USER,
                    'allowed_to' => Block_Register::ALLOW_TO_ALL,
                ]),
                'content',
                RegisterInterface::class,
                'register',
                $register
            );
        }
        return $register;
    }


    /**
     * Создает страницу редактирования профиля
     * @return Page Созданная или существующая страница
     */
    public function createEditProfile()
    {
        $set = Page::getSet([
            'where' => "urn = 'profile' AND pid = " . (int)$this->Site->id
        ]);
        if ($set) {
            $profile = $set[0];
            $profile->trust();
        } else {
            $profile = $this->createPage(
                [
                    'name' => $this->view->_('EDIT_PROFILE'),
                    'urn' => 'profile',
                    'cache' => 0,
                    'response_code' => 200
                ],
                $this->Site
            );
            $access = new CMSAccess([
                'page_id' => $profile->id,
                'to_type' => CMSAccess::TO_ALL,
                'allow' => 0,
                'priority' => 0,
            ]);
            $access->commit();
            $access = new CMSAccess([
                'page_id' => $profile->id,
                'to_type' => CMSAccess::TO_REGISTERED,
                'allow' => 1,
                'priority' => 1,
            ]);
            $access->commit();

            $this->createBlock(
                new Block_Register([
                    'name' => $this->view->_('EDIT_PROFILE'),
                    'form_id' => Form::importByURN('edit_profile')->id,
                    'email_as_login' => true,
                    'notify_about_edit' => false,
                    'allow_edit_social' => true,
                    'activation_type' => Block_Register::ACTIVATION_TYPE_USER,
                    'allowed_to' => Block_Register::ALLOW_TO_ALL,
                ]),
                'content',
                RegisterInterface::class,
                'register',
                $profile
            );
        }
        return $profile;
    }


    /**
     * Создает страницу активации
     * @return Page Созданная или существующая страница
     */
    public function createActivation()
    {
        $set = Page::getSet([
            'where' => "urn = 'activation' AND pid = " . (int)$this->Site->id
        ]);
        if ($set) {
            $activation = $set[0];
            $activation->trust();
        } else {
            $activation = $this->createPage(
                [
                    'name' => $this->view->_('ACTIVATION'),
                    'urn' => 'activate',
                    'cache' => 0,
                    'response_code' => 200
                ],
                $this->Site
            );
            $this->createBlock(
                new Block_Activation(),
                'content',
                ActivationInterface::class,
                'activation',
                $activation
            );
        }
        return $activation;
    }


    /**
     * Создает страницу входа в систему
     * @return Page Созданная или существующая страница
     */
    public function createLogIn()
    {
        $set = Page::getSet([
            'where' => "urn = 'login' AND pid = " . (int)$this->Site->id
        ]);
        if ($set) {
            $login = $set[0];
            $login->trust();
        } else {
            $login = $this->createPage(
                [
                    'name' => $this->view->_('LOG_IN'),
                    'urn' => 'login',
                    'cache' => 0,
                    'response_code' => 200
                ],
                $this->Site
            );
            $this->createBlock(
                new Block_LogIn([
                    'email_as_login' => true,
                    'social_login_type' => Block_Login::SOCIAL_LOGIN_QUICK_REGISTER,
                    'password_save_type' => Block_Login::SAVE_PASSWORD_SAVE_PASSWORD,
                ]),
                'content',
                LogInInterface::class,
                'login',
                $login
            );
        }
        return $login;
    }


    /**
     * Создает страницу восстановления пароля
     * @return Page Созданная или существующая страница
     */
    public function createRecovery()
    {
        $set = Page::getSet([
            'where' => "urn = 'recovery' AND pid = " . (int)$this->Site->id
        ]);
        if ($set) {
            $recovery = $set[0];
            $recovery->trust();
        } else {
            $recovery = $this->createPage(
                [
                    'name' => $this->view->_('PASSWORD_RECOVERY'),
                    'urn' => 'recovery',
                    'cache' => 0,
                    'response_code' => 200
                ],
                $this->Site
            );
            $this->createBlock(
                new Block_Recovery([
                    'notification_id' => Snippet::importByURN('__raas_users_recovery_notify')->id,
                ]),
                'content',
                RecoveryInterface::class,
                'recovery',
                $recovery
            );
        }
        return $recovery;
    }


    /**
     * Создает страницу пользователя для AJAX
     * @param Page $ajax Страница AJAX
     * @return Page Созданная или существующая страница
     */
    public function createAJAXUser(Page $ajax)
    {
        $set = Page::getSet([
            'where' => "urn = 'user' AND pid = " . (int)$ajax->id
        ]);
        if ($set) {
            $userMenu = $set[0];
            $userMenu->trust();
        } else {
            $userMenu = $this->createPage(
                [
                    'name' => $this->view->_('USER'),
                    'urn' => 'user',
                    'template' => 0,
                    'cache' => 0,
                    'mime' => 'application/json',
                    'response_code' => 200,
                ],
                $ajax
            );
            $this->createBlock(
                new Block_PHP(['name' => $this->view->_('USER')]),
                '',
                null,
                'user_ajax',
                $userMenu
            );
        }
        return $userMenu;
    }


    /**
     * Создает страницу истории заказов
     * @return Page Созданная или существующая страница
     */
    public function createMyOrders()
    {
        $set = Page::getSet([
            'where' => "urn = 'my-orders' AND pid = " . (int)$this->Site->id
        ]);
        if ($set) {
            $myOrders = $set[0];
            $myOrders->trust();
        } else {
            $myOrders = $this->createPage(
                [
                    'name' => $this->view->_('MY_ORDERS'),
                    'urn' => 'my-orders',
                    'cache' => 0,
                    'response_code' => 200
                ],
                $this->Site
            );
            $access = new CMSAccess([
                'page_id' => $myOrders->id,
                'to_type' => CMSAccess::TO_ALL,
                'allow' => 0,
                'priority' => 0,
            ]);
            $access->commit();
            $access = new CMSAccess([
                'page_id' => $myOrders->id,
                'to_type' => CMSAccess::TO_REGISTERED,
                'allow' => 1,
                'priority' => 1,
            ]);
            $access->commit();

            $this->createBlock(
                new Block_PHP(),
                'content',
                'RAAS\\CMS\\Shop\\MyOrdersInterface',
                'my_orders',
                $myOrders
            );
        }
        return $myOrders;
    }
}
