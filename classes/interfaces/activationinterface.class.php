<?php
/**
 * Файл стандартного интерфейса активации учетной записи
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\Controller_Frontend as RAASController_Frontend;
use RAAS\CMS\Auth;
use RAAS\CMS\BlockInterface;
use RAAS\CMS\Page;
use RAAS\CMS\User as CMSUser;

/**
 * Класс стандартного интерфейса активации учетной записи
 */
class ActivationInterface extends BlockInterface
{
    /**
     * Конструктор класса
     * @param ?Block_Activation $block Блок, для которого применяется
     *                                     интерфейс
     * @param ?Page $page Страница, для которой применяется интерфейс
     * @param array $get Поля $_GET параметров
     * @param array $post Поля $_POST параметров
     * @param array $cookie Поля $_COOKIE параметров
     * @param array $session Поля $_SESSION параметров
     * @param array $server Поля $_SERVER параметров
     * @param array $files Поля $_FILES параметров
     */
    public function __construct(
        ?Block_Activation $block = null,
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
        $result = [];
        $user = RAASController_Frontend::i()->user;
        $localError = [];
        if ($user->vis) {
            $localError[] = View_Web::i()->_('ERR_ALREADY_ACTIVATED');
        } elseif ($tmpUser = CMSUser::importByActivationKey(
            isset($this->get['key']) ? $this->get['key'] : ''
        )) {
            $user = $tmpUser;
            $user->vis = 1;
            $user->commit();
            $auth = new Auth($user);
            $auth->setSession();
            RAASController_Frontend::i()->user = $user;
            $result['success'] = true;
        } else {
            $localError[] = View_Web::i()->_('CONFIRMATION_KEY_IS_INVALID');
        }
        $result['localError'] = $localError;
        $result['User'] = $user;

        return $result;
    }
}
