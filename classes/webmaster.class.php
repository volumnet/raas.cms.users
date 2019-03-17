<?php
namespace RAAS\CMS\Users;

use RAAS\Application;
use RAAS\CMS\Form;
use RAAS\CMS\Form_Field;
use RAAS\CMS\Page;
use RAAS\CMS\Snippet;
use RAAS\CMS\Snippet_Folder;
use RAAS\CMS\User_Field;
use RAAS\CMS\Webmaster as CMSWebmaster;

class Webmaster extends CMSWebmaster
{
    protected static $snippets = [
        'register' => 'REGISTRATION',
        'activation' => 'ACTIVATION',
        'login' => 'LOG_IN_INTO_THE_SYSTEM',
        'recovery' => 'PASSWORD_RECOVERY'
    ];
    protected static $notifications = [
        'register' => 'REGISTRATION',
        'recovery' => 'PASSWORD_RECOVERY'
    ];
    protected static $instance;

    public function __get($var)
    {
        switch ($var) {
            case 'Site':
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
        $interfacesFolder = Snippet_Folder::importByURN('__raas_interfaces');
        $interfacesData = [];
        foreach (self::$snippets as $urn => $name) {
            $interfacesData['__raas_users_' . $urn . '_interface'] = [
                'name' => $name . '_STANDARD_INTERFACE',
                'description' => file_get_contents(
                    $this->resourcesDir . '/interfaces/' .
                    $urn . '_interface.php'
                ),
            ];
        }
        foreach (self::$notifications as $urn => $name) {
            $interfacesData['__raas_users_' . $urn . '_notify'] = [
                'name' => $name . '_STANDARD_NOTIFICATION',
                'description' => file_get_contents(
                    $this->resourcesDir . '/interfaces/' .
                    $urn . '_notification.php'
                ),
            ];
        }
        foreach ($interfacesData as $interfaceURN => $interfaceData) {
            $interfaces[$interfaceURN] = $this->checkSnippet(
                $interfacesFolder,
                $interfaceURN,
                $interfaceData['name'],
                $interfaceData['description']
            );
        }
        return $interfaces;
    }


    public function createCab()
    {
        // Добавим виджеты
        $VF = Snippet_Folder::importByURN('__RAAS_views');
        foreach (self::$snippets as $urn => $name) {
            $temp = Snippet::importByURN($urn);
            if (!$temp->id) {
                $S = new Snippet();
                $S->name = $this->view->_($name);
                $S->urn = $urn;
                $S->pid = $VF->id;
                $f = $this->resourcesDir . '/' . $urn . '.tmp.php';
                $S->description = file_get_contents($f);
                $S->commit();
            }
        }

        $F = new User_Field();
        $F->pid = $MT->id;
        $F->name = $this->view->_('YOUR_NAME');
        $F->urn = 'full_name';
        $F->datatype = 'text';
        $F->show_in_table = 1;
        $F->commit();

        $F = new User_Field();
        $F->pid = $MT->id;
        $F->name = $this->view->_('PHONE');
        $F->urn = 'phone';
        $F->datatype = 'text';
        $F->show_in_table = 1;
        $F->commit();


        $S = Snippet::importByURN('__RAAS_users_register_notify');

        $this->createForms([
            [
                'name' => $this->view->_('REGISTRATION_FORM'),
                'urn' => 'register',
                'interface_id' => (int)Snippet::importByURN('__RAAS_users_register_notify')->id,
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
        ]);

        $Site = $this->Site;
        $Site->rollback();

        $register = $this->createPage(
            [
                'name' => $this->view->_('REGISTRATION'),
                'urn' => 'register',
                'cache' => 0,
                'response_code' => 200
            ],
            $Site
        );
        $activation = $this->createPage(
            [
                'name' => $this->view->_('ACTIVATION'),
                'urn' => 'activate',
                'cache' => 0,
                'response_code' => 200
            ],
            $Site
        );
        $login = $this->createPage(
            [
                'name' => $this->view->_('LOG_IN_INTO_THE_SYSTEM'),
                'urn' => 'login',
                'cache' => 0,
                'response_code' => 200
            ],
            $Site
        );
        $recovery = $this->createPage(
            [
                'name' => $this->view->_('PASSWORD_RECOVERY'),
                'urn' => 'recovery',
                'cache' => 0,
                'response_code' => 200
            ],
            $Site
        );

        $FRM = Form::getSet([
            'where' => "name = '" . $this->SQL->real_escape_string(
                $this->view->_('REGISTRATION_FORM')
            ) . "'"
        ]);
        $I = Snippet::importByURN('__RAAS_users_register_interface');
        $S = Snippet::importByURN('register');
        $B = new Block_Register();
        $B->location = 'content';
        $B->vis = 1;
        $B->author_id = $B->editor_id = Application::i()->user->id;
        $B->cats = [$register->id];
        $B->form_id = $FRM ? $FRM[0]->id : 0;
        $B->email_as_login = 0;
        $B->notify_about_edit = 0;
        $B->allow_edit_social = 0;
        $B->activation_type = Block_Register::ACTIVATION_TYPE_USER;
        $B->allowed_to = Block_Register::ALLOW_TO_ALL;
        $B->widget_id = $S->id;
        $B->interface_id = $I->id;
        $B->commit();

        $I = Snippet::importByURN('__RAAS_users_login_interface');
        $S = Snippet::importByURN('login');
        $B = new Block_LogIn();
        $B->location = 'content';
        $B->vis = 1;
        $B->author_id = $B->editor_id = Application::i()->user->id;
        $B->cats = [$login->id];
        $B->email_as_login = 1;
        $B->social_login_type = Block_Login::SOCIAL_LOGIN_NONE;
        $B->password_save_type = Block_Login::SAVE_PASSWORD_SAVE_PASSWORD;
        $B->widget_id = $S->id;
        $B->interface_id = $I->id;
        $B->commit();

        $I = Snippet::importByURN('__RAAS_users_recovery_interface');
        $S = Snippet::importByURN('recovery');
        $N = Snippet::importByURN('__RAAS_users_recovery_notify');
        $B = new Block_Recovery();
        $B->location = 'content';
        $B->vis = 1;
        $B->author_id = $B->editor_id = Application::i()->user->id;
        $B->cats = [$recovery->id];
        $B->notification_id = $N->id;
        $B->widget_id = $S->id;
        $B->interface_id = $I->id;
        $B->commit();

        $I = Snippet::importByURN('__RAAS_users_activation_interface');
        $S = Snippet::importByURN('activation');
        $B = new Block_Activation();
        $B->location = 'content';
        $B->vis = 1;
        $B->author_id = $B->editor_id = Application::i()->user->id;
        $B->cats = [$activation->id];
        $B->widget_id = $S->id;
        $B->interface_id = $I->id;
        $B->commit();
    }
}
