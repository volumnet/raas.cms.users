<?php
/**
 * Файл стандартного интерфейса входа в систему
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\Application;
use RAAS\Controller_Frontend as RAASController_Frontend;
use RAAS\View_Web as RAASViewWeb;
use RAAS\CMS\BlockInterface;
use RAAS\CMS\Auth;
use RAAS\CMS\Page;
use RAAS\CMS\SocialProfile;
use RAAS\CMS\ULogin;
use RAAS\CMS\User as CMSUser;

/**
 * Класс стандартного интерфейса входа в систему
 */
class LogInInterface extends BlockInterface
{
    use CheckRedirectTrait;

    /**
     * Конструктор класса
     * @param ?Block_LogIn $block Блок, для которого применяется
     *                                интерфейс
     * @param ?Page $page Страница, для которой применяется интерфейс
     * @param array $get Поля $_GET параметров
     * @param array $post Поля $_POST параметров
     * @param array $cookie Поля $_COOKIE параметров
     * @param array $session Поля $_SESSION параметров
     * @param array $server Поля $_SERVER параметров
     * @param array $files Поля $_FILES параметров
     */
    public function __construct(
        ?Block_LogIn $block = null,
        ?Page $page = null,
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $session = [],
        array $server = [],
        array $files = []
    ) {
        parent::__construct(
            $block,
            $page,
            $get,
            $post,
            $cookie,
            $session,
            $server,
            $files
        );
    }


    public function process(): array
    {
        $user = RAASController_Frontend::i()->user;
        $auth = new Auth($user);
        $localError = [];
        $noredirect = false;
        if ($this->get['logout'] ?? null) {
            $auth->logout();
        } elseif ($user->id) {
            // Ничего не делаем
        } elseif (mb_strtolower($this->server['REQUEST_METHOD'] ?? '') == 'post') {
            if (isset($this->post['token']) && $this->block->social_login_type) {
                if ($socialProfile = $this->getProfile($this->post['token'])) {
                    if ($auth->loginBySocialNetwork($socialProfile->profile)) {
                        // Ничего не делаем
                    } elseif ($socialProfile->email &&
                        ($socialLoginByEmailResult = $this->processSocialLoginByEmail($socialProfile))
                    ) {
                        $auth = $socialLoginByEmailResult;
                    } elseif ($this->block->social_login_type == Block_LogIn::SOCIAL_LOGIN_QUICK_REGISTER) {
                        $auth = $this->processSocialLoginQuickRegister($socialProfile);
                    } else {
                        $localError[] = View_Web::i()->_('ERR_USER_WITH_THIS_SOCIAL_NETWORK_IS_NOT_FOUND');
                    }
                } else {
                    $localError[] = View_Web::i()->_('ERR_CANT_CONNECT_TO_SOCIAL_NETWORK');
                }
            } elseif (!isset($this->post['login'])) {
                $localError['login'] = View_Web::i()->_('LOGIN_REQUIRED');
            } elseif (!isset($this->post['password'])) {
                $localError['password'] = View_Web::i()->_('PASSWORD_REQUIRED');
            } else {
                $val = $auth->login(
                    trim($this->post['login']),
                    $this->post['password'],
                    $this->checkIfSavePassword($this->block, $this->post)
                );
                if ($val === -1) {
                    $localError[] = View_Web::i()->_('YOUR_ACCOUNT_IS_BLOCKED');
                } elseif ($val) {
                    // Ничего не делаем
                } else {
                    $localError[] = RAASViewWeb::i()->_('INVALID_LOGIN_OR_PASSWORD');
                }
            }
        } else {
            $noredirect = true;
        }
        $result = [
            'User' => $auth->user,
            'localError' => $localError,
        ];
        if (!$noredirect && !$localError) {
            RAASController_Frontend::i()->user = $auth->user;
            if (isset($this->post['HTTP_REFERER'])) {
                $referer = $this->post['HTTP_REFERER'];
            } elseif (isset($this->get['HTTP_REFERER'])) {
                $referer = $this->get['HTTP_REFERER'];
            } else {
                $referer = '/';
            }
            $result['success'][$this->block->id] = $this->checkRedirect(
                $this->post,
                $this->server,
                $referer
            );
        }
        return $result;
    }


    /**
     * Пытается опознать пользователя по e-mail и при необходимости добавить его
     * социальный профиль
     * @param SocialProfile $socialProfile Профиль в соц. сети
     * @return Auth|null Auth, если опознан, null в противном случае
     */
    public function processSocialLoginByEmail(SocialProfile $socialProfile)
    {
        $sqlQuery = "SELECT *
                       FROM " . CMSUser::_tablename() . "
                      WHERE email = ?
                      LIMIT 1";
        $sqlResult = CMSUser::_SQL()->getline([
            $sqlQuery,
            [$socialProfile->email]
        ]);
        if ($sqlResult) {
            $user = new CMSUser($sqlResult);
            $user->meta_social = array_values(array_unique(array_merge(
                (array)$user->social,
                [$socialProfile->profile]
            )));
            $user->commit();
            $auth = new Auth($user);
            $auth->setSession();
            return $auth;
        }
        return null;
    }


    /**
     * Регистрирует пользователя по профилю в соц. сети
     * @param SocialProfile $socialProfile Профиль в соц. сети
     * @return Auth авторизатор
     */
    public function processSocialLoginQuickRegister(SocialProfile $socialProfile)
    {
        $user = new CMSUser();
        $user->vis = 1;
        $user->meta_social = [$socialProfile->profile];
        if ($socialProfile->email) {
            $user->email = $socialProfile->email;
        }
        if ($socialProfile->nickname) {
            $login = $socialProfile->nickname;
        } elseif ($socialProfile->profile) {
            $login = basename($socialProfile->profile);
        }
        while ($user->checkLoginExists($login)) {
            $login = Application::i()->getNewURN($login);
        }
        if ($this->block && $this->block->email_as_login) {
            $user->login = $user->email;
        }
        if (!$user->login) {
            $user->login = $login;
        }
        $user->commit();
        $userFieldsURNs = ['last_name', 'first_name', 'full_name', 'phone'];
        foreach ($userFieldsURNs as $userFieldsURN) {
            if ($field = ($user->fields[$userFieldsURN] ?? null)) {
                $field->deleteValues();
                $field->addValue($socialProfile->$userFieldsURN);
            }
        }
        $auth = new Auth($user);
        $auth->setSession();
        return $auth;
    }


    /**
     * Проверяет, нужно ли сохранять пароль
     * @param Block_LogIn $block Текущий блок
     * @param array $post Поля $_POST параметров
     * @return bool
     */
    public function checkIfSavePassword(Block_LogIn $block, array $post = [])
    {
        $passwordSaveType = $block->password_save_type;
        if (($passwordSaveType == Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD) &&
            isset($post['save_password'])
        ) {
            return true;
        } elseif (($passwordSaveType == Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER) &&
            !isset($post['foreign_computer'])
        ) {
            return true;
        }
        return false;
    }


    /**
     * Получает профиль пользователя в соц. сети по токену
     * @param string $token Токен
     * @return SocialProfile
     */
    public function getProfile($token)
    {
        // @codeCoverageIgnoreStart
        return ULogin::getProfile($token);
        // @codeCoverageIgnoreEnd
    }
}
