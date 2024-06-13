<?php
/**
 * Файл стандартного интерфейса восстановления пароля
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\Application;
use RAAS\Controller_Frontend as RAASController_Frontend;
use RAAS\View_Web as RAASViewWeb;
use RAAS\CMS\BlockInterface;
use RAAS\CMS\Auth;
use RAAS\CMS\FormInterface;
use RAAS\CMS\Snippet;
use RAAS\CMS\Page;
use RAAS\CMS\User as CMSUser;

/**
 * Класс стандартного интерфейса восстановления пароля
 */
class RecoveryInterface extends FormInterface
{
    use CheckRedirectTrait;

    /**
     * Конструктор класса
     * @param Block_Recovery|null $block Блок, для которого применяется
     *                                   интерфейс
     * @param Page|null $page Страница, для которой применяется интерфейс
     * @param array $get Поля $_GET параметров
     * @param array $post Поля $_POST параметров
     * @param array $cookie Поля $_COOKIE параметров
     * @param array $session Поля $_SESSION параметров
     * @param array $server Поля $_SERVER параметров
     * @param array $files Поля $_FILES параметров
     */
    public function __construct(
        Block_Recovery $block = null,
        Page $page = null,
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $session = [],
        array $server = [],
        array $files = []
    ) {
        BlockInterface::__construct(
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


    /**
     * Обрабатывает интерфейс
     * @param bool $debug Режим отладки
     */
    public function process(bool $debug = false): array
    {
        $result = [];
        $user = RAASController_Frontend::i()->user;
        $localError = [];
        if (($this->get['key'] ?? '') || $user->id) {
            $result['proceed'] = true;
            $invalidKey = false;
            $tmpUser = null;
            if ($this->get['key']) {
                $tmpUser = CMSUser::importByRecoveryKey($this->get['key']);
                if ($tmpUser && $tmpUser->id) {
                    $user = $tmpUser;
                } else {
                    $invalidKey = true;
                }
            }
            if ($invalidKey) {
                $localError[] = View_Web::i()->_('CONFIRMATION_KEY_IS_INVALID');
                $result['key_is_invalid'] = true;
            } elseif (!$user->vis) {
                $localError[] = View_Web::i()->_('YOUR_ACCOUNT_IS_BLOCKED');
                $result['key_is_invalid'] = true;
            } else {
                if ($tmpUser) {
                    RAASController_Frontend::i()->user = $user;
                    $auth = new Auth($user);
                    $auth->setSession();
                }
                if (mb_strtolower($this->server['REQUEST_METHOD'] ?? '') == 'post') {
                    if (!isset($this->post['password']) ||
                        !trim($this->post['password'])
                    ) {
                        $localError['password'] = View_Web::i()->_('PASSWORD_REQUIRED');
                    } elseif ($this->post['password'] != $this->post['password@confirm']) {
                        $localError['password'] = View_Web::i()->_('PASSWORD_DOESNT_MATCH_CONFIRM');
                    } else {
                        $user->password_md5 = Application::i()->md5It($this->post['password']);
                        $user->commit();
                        $referer = null;
                        if (isset($this->post['HTTP_REFERER'])) {
                            $referer = $this->post['HTTP_REFERER'];
                        } elseif (isset($this->get['HTTP_REFERER'])) {
                            $referer = $this->get['HTTP_REFERER'];
                        }
                        if ($referer) {
                            $result['success'][$this->block->id] = $this->checkRedirect(
                                $this->post,
                                $this->server,
                                $referer,
                                $debug
                            );
                        } else {
                            $result['success'][$this->block->id] = true;
                        }
                    }
                }
            }
        } elseif (isset($this->post['login']) && trim($this->post['login'])) {
            if ($tmpUser = CMSUser::importByLoginOrEmail(trim($this->post['login']))) {
                if (!$tmpUser->vis) {
                    $localError['login'] = View_Web::i()->_('YOUR_ACCOUNT_IS_BLOCKED');
                } elseif ($tmpUser->email) {
                    $this->notifyRecovery(
                        $tmpUser,
                        $this->page,
                        $this->block->config
                    );
                    $result['success'] = true;
                } else {
                    $localError['login'] = View_Web::i()->_('NO_EMAIL_OF_THIS_USER');
                }
            } else {
                $localError['login'] = View_Web::i()->_('USER_WITH_THIS_LOGIN_IS_NOT_FOUND');
            }
        }
        $result['localError'] = $localError;
        $result['User'] = $user;

        return $result;
    }


    /**
     * Уведомление пользователя о восстановлении пароля
     * @param User $user Пользователь
     * @param Page $page Текущая страница
     * @param array $config Конфигурация блока
     * @param bool $debug Режим отладки
     * @return [
     *             'emails' => [
     *                 'emails' => array<string> e-mail адреса,
     *                 'subject' => string Тема письма,
     *                 'message' => string Тело письма,
     *                 'from' => string Поле "от",
     *                 'fromEmail' => string Обратный адрес
     *             ],
     *         ]|null Набор отправляемых писем (только в режиме отладки)
     */
    public function notifyRecovery(
        CMSUser $user,
        Page $page,
        array $config = [],
        $debug = false
    ) {
        $template = new Snippet((int)$config['notification_id']);
        if (!$template->id) {
            return;
        }
        $addresses = $this->parseUserAddresses($user);

        $notificationData = [
            'Item' => $user,
            'User' => $user,
            'Page' => $page,
            'config' => $config,
            'recoveryInterface' => $this,
            'referer' => ($this->get['HTTP_REFERER'] ?? '')
        ];

        $subject = $this->getEmailRecoverySubject();
        $message = $this->getMessageBody(
            $template,
            array_merge($notificationData, ['SMS' => false])
        );
        $fromName = $this->getFromName();
        $fromEmail = $this->getFromEmail();
        $debugMessages = [];

        if ($emails = $addresses['emails']) {
            if ($debug) {
                $debugMessages['emails'] = [
                    'emails' => $emails,
                    'subject' => $subject,
                    'message' => $message,
                    'from' => $fromName,
                    'fromEmail' => $fromEmail,
                ];
            } else {
                Application::i()->sendmail(
                    $emails,
                    $subject,
                    $message,
                    $fromName,
                    $fromEmail
                );
            }
        }

        if ($debug) {
            return $debugMessages;
        }
    }


    /**
     * Получает заголовок e-mail сообщения
     * @return string
     */
    public function getEmailRecoverySubject()
    {
        $host = $this->server['HTTP_HOST'] ?? '';
        if (function_exists('idn_to_utf8')) {
            $host = idn_to_utf8($host);
        }
        $host = mb_strtoupper((string)$host);
        $subject = date(RAASViewWeb::i()->_('DATETIMEFORMAT')) . ' '
                 . sprintf(View_Web::i()->_('PASSWORD_RECOVERY_ON_SITE'), $host);
        return $subject;
    }
}
