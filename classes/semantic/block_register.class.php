<?php
namespace RAAS\CMS\Users;

use RAAS\User as RAASUser;
use RAAS\CMS\Block;
use RAAS\Controller_Frontend as RAASController_Frontend;
use RAAS\CMS\Form;
use RAAS\CMS\Page;

class Block_Register extends Block
{
    const ACTIVATION_TYPE_ADMINISTRATOR = 0;
    const ACTIVATION_TYPE_USER = 1;
    const ACTIVATION_TYPE_ALREADY_ACTIVATED = 2;

    const ALLOW_TO_UNREGISTERED = -1;
    const ALLOW_TO_ALL = 0;
    const ALLOW_TO_REGISTERED = 1;

    protected static $tablename2 = 'cms_users_blocks_register';

    protected static $references = [
        'Register_Form' => [
            'FK' => 'form_id',
            'classname' => Form::class,
            'cascade' => false
        ],
        'author' => [
            'FK' => 'author_id',
            'classname' => RAASUser::class,
            'cascade' => false
        ],
        'editor' => [
            'FK' => 'editor_id',
            'classname' => RAASUser::class,
            'cascade' => false
        ],
    ];

    protected static $cognizableVars = [
        'Edit_Form',
    ];

    public function commit()
    {
        if (!$this->name) {
            $this->name = Module::i()->view->_('REGISTRATION');
        }
        parent::commit();
    }


    public function process(Page $Page, $nocache = false)
    {
        if ($this->allow_to != static::ALLOW_TO_ALL) {
            $r = (bool)RAASController_Frontend::i()->user->id;
            if (($r && ($this->allow_to == static::ALLOW_TO_UNREGISTERED)) ||
                (!$r && ($this->allow_to == static::ALLOW_TO_REGISTERED))
            ) {
                if (trim($this->redirect_url)) {
                    header('Location: ' . trim($this->redirect_url));
                    exit;
                } else {
                    return;
                }
            }
        }
        parent::process($Page);
    }


    public function getAddData()
    {
        return [
            'id' => (int)$this->id,
            'form_id' => (int)$this->form_id,
            'email_as_login' => (int)$this->email_as_login,
            'notify_about_edit' => (int)$this->notify_about_edit,
            'allow_edit_social' => (int)$this->allow_edit_social,
            'activation_type' => (int)$this->activation_type,
            'allow_to' => (int)$this->allow_to,
            'redirect_url' => trim($this->redirect_url),
        ];
    }
}
