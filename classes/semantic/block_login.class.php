<?php
/**
 * Блок входа в систему
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\CMS\Block;

class Block_LogIn extends Block
{
    const ALLOWED_INTERFACE_CLASSNAME = LogInInterface::class;

    const SOCIAL_LOGIN_NONE = 0;
    const SOCIAL_LOGIN_ONLY_REGISTERED = 1;
    const SOCIAL_LOGIN_QUICK_REGISTER = 2;

    const SAVE_PASSWORD_NONE = 0;
    const SAVE_PASSWORD_SAVE_PASSWORD = 1;
    const SAVE_PASSWORD_FOREIGN_COMPUTER = 2;

    protected static $tablename2 = 'cms_users_blocks_login';

    public function commit()
    {
        if (!$this->name) {
            $this->name = Module::i()->view->_('LOG_IN_INTO_THE_SYSTEM');
        }
        parent::commit();
    }


    public function getAddData(): array
    {
        return array(
            'id' => (int)$this->id,
            'email_as_login' => (int)$this->email_as_login,
            'social_login_type' => (int)$this->social_login_type,
            'password_save_type' => (int)$this->password_save_type,
        );
    }
}
