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
        $interfacesData = [
            '__raas_users_register_interface' => [
                'name' => 'REGISTRATION_STANDARD_INTERFACE',
                'filename' => 'register_interface',
            ],
            '__raas_users_activation_interface' => [
                'name' => 'ACTIVATION_STANDARD_INTERFACE',
                'filename' => 'activation_interface',
            ],
            '__raas_users_login_interface' => [
                'name' => 'LOG_IN_INTO_THE_SYSTEM_STANDARD_INTERFACE',
                'filename' => 'login_interface',
            ],
            '__raas_users_recovery_interface' => [
                'name' => 'PASSWORD_RECOVERY_STANDARD_INTERFACE',
                'filename' => 'recovery_interface',
            ],
            '__raas_users_register_notify' => [
                'name' => 'REGISTRATION_STANDARD_NOTIFICATION',
                'filename' => 'register_notification',
            ],
            '__raas_users_recovery_notify' => [
                'name' => 'PASSWORD_RECOVERY_STANDARD_NOTIFICATION',
                'filename' => 'recovery_notification',
            ],
        ];
        foreach ($interfacesData as $interfaceURN => $interfaceData) {
            $interfaces[$interfaceURN] = $this->checkSnippet(
                $this->interfacesFolder,
                $interfaceURN,
                $interfaceData['name'],
                file_get_contents(
                    Module::i()->resourcesDir .
                    '/interfaces/' . $interfaceData['filename'] . '.php'
                )
            );
        }
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
            'menu_user' => View_Web::i()->_('USER_MENU'),
            'menu_user_block' => View_Web::i()->_('USER_MENU_BLOCK'),
        ];
        foreach ($widgetsData as $url => $name) {
            $urn = explode('/', $url);
            $urn = $urn[count($urn) - 1];
            $widget = Snippet::importByURN($urn);
            if (!$widget->id) {
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
        $interfaces = $this->checkStdInterfaces();
        $widgets = $this->createWidgets();

        $F = new User_Field([
            'name' => $this->view->_('YOUR_NAME'),
            'urn' => 'full_name',
            'datatype' => 'text',
            'show_in_table' => 1,
        ]);
        $F->commit();

        $F = new User_Field([
            'name' => $this->view->_('PHONE'),
            'urn' => 'phone',
            'datatype' => 'text',
            'show_in_table' => 1,
        ]);
        $F->commit();

        $ajax = array_shift(Page::getSet([
            'where' => "urn = 'ajax' AND pid = " . (int)$this->Site->id
        ]));

        $this->createForms(
            [
                [
                    'name' => $this->view->_('REGISTRATION_FORM'),
                    'urn' => 'register',
                    'interface_id' => (int)Snippet::importByURN('__raas_users_register_notify')->id,
                    'fields' => [
                        [
                            'name' => $this->view->_('LOGIN'),
                            'urn' => 'login',
                            'datatype' => 'text',
                            'required' => 1,
                            'show_in_table' => 1,
                        ],
                        [
                            'name' => $this->view->_('PASSWORD'),
                            'urn' => 'password',
                            'datatype' => 'password',
                            'required' => 1,
                            'show_in_table' => 1,
                        ],
                        [
                            'name' => $this->view->_('EMAIL'),
                            'urn' => 'email',
                            'datatype' => 'text',
                            'show_in_table' => 1,
                        ],
                        [
                            'name' => $this->view->_('YOUR_NAME'),
                            'urn' => 'full_name',
                            'required' => 1,
                            'datatype' => 'text',
                            'show_in_table' => 1,
                        ],
                        [
                            'name' => $this->view->_('PHONE'),
                            'urn' => 'phone',
                            'datatype' => 'text',
                            'show_in_table' => 1,
                        ],
                    ],
                ],
                [
                    'name' => $this->view->_('EDIT_PROFILE'),
                    'urn' => 'edit_profile',
                    'interface_id' => (int)Snippet::importByURN('__raas_users_register_notify')->id,
                    'fields' => [
                        [
                            'name' => $this->view->_('PASSWORD'),
                            'urn' => 'password',
                            'datatype' => 'password',
                            'required' => 1,
                            'show_in_table' => 1,
                        ],
                        [
                            'name' => $this->view->_('EMAIL'),
                            'urn' => 'email',
                            'datatype' => 'text',
                            'show_in_table' => 1,
                        ],
                        [
                            'name' => $this->view->_('YOUR_NAME'),
                            'urn' => 'full_name',
                            'required' => 1,
                            'datatype' => 'text',
                            'show_in_table' => 1,
                        ],
                        [
                            'name' => $this->view->_('PHONE'),
                            'urn' => 'phone',
                            'datatype' => 'text',
                            'show_in_table' => 1,
                        ],
                    ],
                ],
            ]
        );

        $register = $this->createRegister();
        $profile = $this->createEditProfile();
        $activation = $this->createActivation();
        $login = $this->createLogIn();
        $recovery = $this->createRecovery();

        if (Snippet::importByURN('my_orders')->id &&
            Snippet::importByURN('__raas_my_orders_interface')->id
        ) {
            $myOrders = $this->createMyOrders();
        }

        $this->createBlock(
            new Block_PHP(['name' => View_Web::i()->_('USER_MENU')]),
            'menu_user',
            null,
            'menu_user_block',
            $this->site,
            true
        );
        $this->createAJAXUserMenu($ajax);
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
                    'email_as_login' => 0,
                    'notify_about_edit' => 0,
                    'allow_edit_social' => 0,
                    'activation_type' => Block_Register::ACTIVATION_TYPE_USER,
                    'allowed_to' => Block_Register::ALLOW_TO_ALL,
                ]),
                'content',
                '__raas_users_register_interface',
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
                    'email_as_login' => 0,
                    'notify_about_edit' => 0,
                    'allow_edit_social' => 0,
                    'activation_type' => Block_Register::ACTIVATION_TYPE_USER,
                    'allowed_to' => Block_Register::ALLOW_TO_ALL,
                ]),
                'content',
                '__raas_users_register_interface',
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
                '__raas_users_activation_interface',
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
                    'email_as_login' => 1,
                    'social_login_type' => Block_Login::SOCIAL_LOGIN_NONE,
                    'password_save_type' => Block_Login::SAVE_PASSWORD_SAVE_PASSWORD,
                ]),
                'content',
                '__raas_users_login_interface',
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
                '__raas_users_recovery_interface',
                'recovery',
                $recovery
            );
        }
        return $recovery;
    }


    /**
     * Создает страницу пользовательского меню для AJAX
     * @param Page $ajax Страница AJAX
     * @return Page Созданная или существующая страница
     */
    public function createAJAXUserMenu(Page $ajax)
    {
        $set = Page::getSet([
            'where' => "urn = 'user_menu' AND pid = " . (int)$ajax->id
        ]);
        if ($set) {
            $userMenu = $set[0];
            $userMenu->trust();
        } else {
            $userMenu = $this->createPage(
                [
                    'name' => $this->view->_('USER_MENU'),
                    'urn' => 'user_menu',
                    'template' => 0,
                    'cache' => 0,
                    'response_code' => 200
                ],
                $ajax
            );
            $this->createBlock(
                new Block_PHP(),
                '',
                null,
                'menu_user',
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
                '__raas_my_orders_interface',
                'my_orders',
                $myOrders
            );
        }
        return $myOrders;
    }
}
