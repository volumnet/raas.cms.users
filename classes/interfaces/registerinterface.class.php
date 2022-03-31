<?php
/**
 * Файл стандартного интерфейса регистрации
 */
namespace RAAS\CMS\Users;

use Mustache_Engine;
use Pelago\Emogrifier\CssInliner;
use SOME\Text;
use RAAS\Application;
use RAAS\Controller_Frontend as RAASControllerFrontend;
use RAAS\View_Web as RAASViewWeb;
use RAAS\CMS\AbstractInterface;
use RAAS\CMS\Form;
use RAAS\CMS\FormInterface;
use RAAS\CMS\Material;
use RAAS\CMS\Material_Type;
use RAAS\CMS\Package;
use RAAS\CMS\Page;
use RAAS\CMS\ULogin;
use RAAS\CMS\User;

/**
 * Класс стандартного интерфейса регистрации
 */
class RegisterInterface extends FormInterface
{
    use CheckRedirectTrait;

    /**
     * Конструктор класса
     * @param Block_Register|null $block Блок, для которого применяется
     *                               интерфейс
     * @param Page|null $page Страница, для которой применяется интерфейс
     * @param array $get Поля $_GET параметров
     * @param array $post Поля $_POST параметров
     * @param array $cookie Поля $_COOKIE параметров
     * @param array $session Поля $_SESSION параметров
     * @param array $server Поля $_SERVER параметров
     * @param array $files Поля $_FILES параметров
     */
    public function __construct(
        Block_Register $block = null,
        Page $page = null,
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $session = [],
        array $server = [],
        array $files = []
    ) {
        AbstractInterface::__construct(
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


    public function process()
    {
        $result = [];
        // 2019-08-21, AVS: пока не помню, для чего создается новый пользователь,
        // но вероятно в этом есть какой-то смысл
        $uid = RAASControllerFrontend::i()->user->id;
        $user = new User($uid);
        $new = !$user->id;
        if ($user->id && ($this->page->urn == 'register')) {
            // @deprecated Для совместимости со старыми сайтами, где страница
            // редактирования профиля совпадала со страницей регистрации
            $form = $this->block->Edit_Form->id
                  ? $this->block->Edit_Form
                  : $this->block->Register_Form;
            $this->page->h1 = $this->page->meta_title
                            = 'Редактирование профиля';
        } else {
            $form = $this->block->Register_Form;
        }
        foreach ($form->fields as $fieldURN => $field) {
            if ($user->id && $field->datatype == 'password') {
                $field->required = false;
            }
        }

        if ($form->id) {
            if ($this->block->allow_edit_social && isset($this->post['token'])) {
                $result = array_merge($result, $this->processSocialToken(
                    $user,
                    $this->post,
                    $this->session,
                    $this->server
                ));
            } elseif ($this->isFormProceed(
                $this->block,
                $form,
                $this->server['REQUEST_METHOD'],
                $this->post
            )) {
                $localError = $this->checkRegisterForm(
                    $user,
                    $form,
                    $this->post,
                    $this->session,
                    $this->files
                );

                if (!$localError) {
                    $result = array_merge($result, $this->processRegisterForm(
                        $user,
                        $this->block,
                        $form,
                        $this->page,
                        $this->post,
                        $this->session,
                        $this->server,
                        $this->files
                    ));

                    $result['success'][(int)$this->block->id] = true;
                }
                $result['DATA'] = $this->post;
                $result['localError'] = $localError;
            } else {
                $result['DATA'] = $user->getArrayCopy();
                unset($result['DATA']['password_md5']);
                $material = $this->getUserMaterial($form, $user, $new);
                $materialId = $material->id;
                foreach ($form->fields as $fieldURN => $formField) {
                    if ($user->id && isset($user->fields[$fieldURN])) {
                        $userField = $user->fields[$fieldURN];
                        $result['DATA'][$fieldURN] = $userField->getValues();
                    } elseif ($materialId && isset($material->fields[$fieldURN])) {
                        $materialField = $material->fields[$fieldURN];
                        $result['DATA'][$fieldURN] = $materialField->getValues();
                    } elseif ($materialId &&
                        in_array($fieldURN, ['_name_', '_description_'])
                    ) {
                        $result['DATA'][$fieldURN] = $material->{trim($fieldURN, '_')};
                    } elseif (!$user->id) {
                        $result['DATA'][$fieldURN] = $formField->defval;
                    }
                }
                if ($this->block->allow_edit_social) {
                    $result['DATA']['social'] = $user->social;
                }
                $result['localError'] = [];
            }
        }
        unset($result['DATA']['password'], $result['DATA']['password@confirm']);
        $result['Form'] = $form;
        $result['User'] = $user;

        return $result;
    }


    /**
     * Регистрация по токену соц. сети
     * @param User $user Пользователь
     * @param array $post Данные $_POST-полей
     * @param array $session Данные $_SESSION-полей
     * @param array $server Данные $_SERVER-полей
     * @return [
     *             'social' => string Адрес профиля в соц. сети,
     *             'socialNetwork' => int Идентификатор соц. сети -
     *                                    константа вида SocialProfile::SN_...,
     *             'redirect' =>? string В режиме отладки - адрес редиректа
     *         ]
     */
    public function processSocialToken(
        User $user,
        array $post = [],
        array $session = [],
        array $server = [],
        $debug = false
    ) {
        $result = [];
        if (!isset($session['confirmedSocial'])) {
            $_SESSION['confirmedSocial'] =
            $session['confirmedSocial'] =
            $this->session['confirmedSocial'] = [];
        }
        if ($profile = $this->getProfile($post['token'])) {
            if ($post['AJAX']) {
                $_SESSION['confirmedSocial'][] = $profile->profile;
                $_SESSION['confirmedSocial'] = array_values(
                    array_unique((array)$_SESSION['confirmedSocial'])
                );
                $this->session['confirmedSocial'] =
                $session['confirmedSocial'] = $_SESSION['confirmedSocial'];
                $result['social'] = $profile->profile;
                $result['socialNetwork'] = $profile->socialNetwork;
            } else {
                $user->addSocial($profile->profile);
                $url = $server['REQUEST_URI'];
                if ($debug) {
                    $result['redirect'] = $url;
                } else {
                    header('Location: ' . $url);
                    exit;
                }
            }
        }
        return $result;
    }


    /**
     * Получает профиль пользователя в соц. сети по токену
     * @param string $token Токен
     * @return SocialProfile
     */
    public function getProfile($token)
    {
        return ULogin::getProfile($token);
    }


    /**
     * Проверяет правильность заполнения формы
     * @param User $user Текущий пользователь
     * @param Form $form Форма регистрации
     * @param array $post Данные $_POST-полей
     * @param array $files Данные $_FILES-полей
     * @param array $files Данные $_SESSION-полей
     * @return array<string[] URN поля => string Текстовое описание ошибки>
     */
    public function checkRegisterForm(
        User $user,
        Form $form,
        array $post = [],
        array $session = [],
        array $files = []
    ) {
        $localError = [];
        foreach ($form->fields as $fieldURN => $field) {
            switch ($field->datatype) {
                case 'file':
                case 'image':
                    if ($fieldError = $this->checkFileField($field, $files)) {
                        $localError[$fieldURN] = $fieldError;
                    }
                    break;
                default:
                    if (($fieldURN != 'agree') || !$user->id) {
                        if ($fieldError = $this->checkRegularField($field, $post)) {
                            $localError[$fieldURN] = $fieldError;
                        }
                    }
                    break;
            }
        }
        // Проверка на антиспам
        if (!$user->id &&
            ($fieldError = $this->checkAntispamField($form, $post, $session))
        ) {
            $localError[$form->antispam_field_name] = $fieldError;
        }

        if (isset($post['login']) && $post['login'] && isset($form->fields['login'])) {
            if ($user->checkLoginExists(trim($post['login']))) {
                $localError['login'] = RAASViewWeb::i()->_('ERR_LOGIN_EXISTS');
            }
        }
        if (isset($post['email']) && $post['email'] && isset($form->fields['email'])) {
            if ($user->checkEmailExists(trim($post['email']))) {
                $localError['email'] = View_Web::i()->_('ERR_EMAIL_EXISTS');
            } elseif (!isset($form->fields['login'])) {
                if ($user->checkLoginExists(trim($post['email']))) {
                    $localError['email'] = RAASViewWeb::i()->_('ERR_LOGIN_EXISTS');
                }
            }
        }
        return $localError;
    }


    /**
     * Обрабатывает форму
     * @param User $user Текущий пользователь
     * @param Block_Register $block Текущий блок
     * @param Form $form Форма регистрации
     * @param Page $page Текущая страница
     * @param array $post Данные $_POST-полей
     * @param array $session Данные $_SESSION-полей
     * @param array $server Данные $_SERVER-полей
     * @param array $files Данные $_FILES-полей
     * @return [
     *             'User' =>? User Созданный или обновленный пользователь,
     *             'Material' =>? Material Созданный материал
     *         ]
     */
    public function processRegisterForm(
        User $user,
        Block_Register $block,
        Form $form,
        Page $page,
        array $post = [],
        array $session = [],
        array $server = [],
        array $files = []
    ) {
        $result = [];
        $user->page_id = (int)$page->id;
        $user->page = $page;
        $this->processUserData($user, $server);
        if ($new = !$user->id) {
            $user->vis = (int)($block->activation_type == Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED);
            $user->new = 1;
        }

        if ($form->fields['email']) {
            $val = $user->email = trim($post['email']);
            if ($val && $block->email_as_login) {
                $user->login = $val;
            }
        }
        if ($form->fields['login'] && !$block->email_as_login) {
            if ($val = trim($post['login'])) {
                $user->login = $val;
            }
        }
        if ($form->fields['password'] &&
            ($val = trim($post['password']))
        ) {
            $user->password = $val;
        } elseif ($new) {
            $val = $user->password = $this->generatePass();
        }
        if ($val) {
            $user->password_md5 = Application::i()->md5It($val);
        }

        if ($form->fields['lang'] && ($val = trim($post['lang']))) {
            $user->lang = $val;
        } else {
            $user->lang = $page->lang;
        }
        if ($block->allow_edit_social &&
            $post['social'] &&
            $session['confirmedSocial']
        ) {
            $arr = [];
            foreach ((array)$post['social'] as $val) {
                $val = trim($val);
                if ($val && in_array($val, array_merge(
                    (array)$session['confirmedSocial'],
                    (array)$user->social
                ))) {
                    $arr[] = $val;
                }
            }
            unset($_SESSION['confirmedSocial']);
            unset($this->session['confirmedSocial']);
            unset($session['confirmedSocial']);
            $user->meta_social = $arr;
        }
        $user->commit();
        $result['User'] = $user;

        $this->processObjectFields($user, $form, $post, $files);
        $this->processObjectDates($user, $post);
        $this->processObjectUserData($user, $server);
        if ($material = $this->processUserMaterial(
            $user,
            $form,
            $new,
            $page,
            $post,
            $server,
            $files
        )) {
            $result['Material'] = $material;
        }

        if ($new || $block->notify_about_edit) {
            $this->notifyRegister($user, $form, $page, $block->config, true);
        }
        if ($new) {
            $this->notifyRegister($user, $form, $page, $block->config, false);
        }
        return $result;
    }


    /**
     * Обрабатывает пользовательский материал
     * @param User $user Пользователь
     * @param Form $form Форма
     * @param bool $new Новый пользователь
     *                  (т.к. пользователь уже сохранен, с ID#)
     * @param array $post Данные $_POST-полей
     * @param array $server Данные $_SERVER-полей
     * @param array $files Данные $_FILES-полей
     * @return Material|null
     */
    public function processUserMaterial(
        User $user,
        Form $form,
        $new,
        Page $page,
        array $post = [],
        array $server = [],
        array $files = []
    ) {
        if ($material = $this->getUserMaterial($form, $user, $new)) {
            $newMaterial = !$material->id;
            $materialType = $form->Material_Type;
            $materialField = $this->getMaterialTypeField(
                $form->Material_Type,
                $user
            );
            if ($newMaterial && !$materialType->global_type) {
                $material->cats = [(int)$page->id];
            }
            $this->processObject($material, $form, $post, $server, $files);
            if ($newMaterial) {
                $materialField->addValue($material->id);
            }
            return $material;
        }
        return null;
    }


    /**
     * Получает поле пользователя по типу материала
     * @param Material_Type $materialType Тип материала
     * @param User $user Пользователь
     * @return User_Field|null
     */
    public function getMaterialTypeField(
        Material_Type $materialType,
        User $user
    ) {
        $mTypeId = $materialType->id;
        $materialFields = array_filter(
            $user->fields,
            function ($field) use ($mTypeId) {
                return ($field->datatype == 'material') &&
                       ($field->source == $mTypeId);
            }
        );
        if ($materialFields) {
            return array_shift($materialFields);
        }
        return null;
    }


    /**
     * Получает пользовательский материал
     * @param Form $form Текущая форма
     * @param User $user Пользователь
     * @param bool $new Новый пользователь
     *                  (т.к. пользователь уже сохранен, с ID#)
     * @return Material|null
     */
    public function getUserMaterial(Form $form, User $user, $new = null)
    {
        if ($new === null) {
            $new = !$user->id;
        }
        $mTypeId = $form->Material_Type->id;
        $materialField = $this->getMaterialTypeField(
            $form->Material_Type,
            $user
        );
        if (!($mTypeId && $materialField->id)) {
            return null;
        }
        $material = null;
        if (!$new) {
            $materials = $materialField->getValues(true);
            if ($materials) {
                $material = array_shift($materials);
            }
        }
        if (!$material) {
            $material = $this->getRawMaterial($form);
        }
        return $material;
    }


    /**
     * Генерирует пароль
     * @param int $length Длина пароля, в символах
     * @return string
     */
    public function generatePass($length = 5)
    {
        $text = '';
        for ($i = 0; $i < $length; $i++) {
            $x = rand(0, 61);
            if ($x < 10) {
                $c = (string)(int)$x;
            } elseif ($x < 36) {
                $c = chr((int)$x - 10 + 65);
            } else {
                $c = chr((int)$x - 36 + 97);
            }
            $text .= $c;
        }
        return $text;
    }


    /**
     * Уведомление о заполненной форме
     * @param User $user Пользователь
     * @param Form $form Форма регистрации
     * @param Page $page Текущая страница
     * @param array $config Конфигурация блока
     * @param bool $forAdmin Уведомление для администратора
     *                       (если нет, то для пользователя)
     * @param bool $debug Режим отладки
     * @return array<
     *             ('emails'|'smsEmails')[] => [
     *                 'emails' => array<string> e-mail адреса,
     *                 'subject' => string Тема письма,
     *                 'message' => string Тело письма,
     *                 'from' => string Поле "от",
     *                 'fromEmail' => string Обратный адрес
     *             ],
     *             'smsPhones' => array<string URL SMS-шлюза>
     *         >|null Набор отправляемых писем либо URL SMS-шлюза
     *                            (только в режиме отладки)
     */
    public function notifyRegister(
        User $user,
        Form $form,
        Page $page,
        array $config = [],
        $forAdmin = false,
        $debug = false
    ) {
        if (!$form->Interface->id) {
            return;
        }
        if ($forAdmin) {
            $addresses = $this->parseFormAddresses($form);
        } else {
            $addresses = $this->parseUserAddresses($user);
        }
        $template = $form->Interface;

        $notificationData = [
            'Item' => $user,
            'User' => $user,
            'Form' => $form,
            'config' => $config,
            'ADMIN' => $forAdmin,
            'forUser' => !$forAdmin,
            'registerInterface' => $this,
        ];

        $subject = $this->getEmailRegisterSubject();
        $message = $this->getMessageBody(
            $template,
            array_merge($notificationData, ['SMS' => false])
        );
        $smsMessage = $this->getMessageBody(
            $template,
            array_merge($notificationData, ['SMS' => true])
        );
        $fromName = $this->getFromName();
        $fromEmail = $this->getFromEmail();
        $debugMessages = [];
        $attachments = $this->getRegisterAttachments($user, $material, $forAdmin);

        $processEmbedded = $this->processEmbedded($message);
        $message = $processEmbedded['message'];
        $embedded = (array)$processEmbedded['embedded'];

        $message = CssInliner::fromHtml($message)->inlineCss()->render();

        if ($emails = $addresses['emails']) {
            if ($debug) {
                $debugMessages['emails'] = [
                    'emails' => $emails,
                    'subject' => $subject,
                    'message' => $message,
                    'from' => $fromName,
                    'fromEmail' => $fromEmail,
                    'attachments' => $attachments,
                    'embedded' => $embedded,
                ];
            } else {
                Application::i()->sendmail(
                    $emails,
                    $subject,
                    $message,
                    $fromName,
                    $fromEmail,
                    true,
                    $attachments,
                    $embedded
                );
            }
        }

        if ($smsEmails = $addresses['smsEmails']) {
            if ($debug) {
                $debugMessages['smsEmails'] = [
                    'emails' => $smsEmails,
                    'subject' => $subject,
                    'message' => $smsMessage,
                    'from' => $fromName,
                    'fromEmail' => $fromEmail,
                ];
            } else {
                Application::i()->sendmail(
                    $smsEmails,
                    $subject,
                    $smsMessage,
                    $fromName,
                    $fromEmail,
                    false
                );
            }
        }

        if (Application::i()->prod && ($smsPhones = $addresses['smsPhones'])) {
            if ($urlTemplate = Package::i()->registryGet('sms_gate')) {
                $m = new Mustache_Engine();
                foreach ($smsPhones as $phone) {
                    $url = $m->render($urlTemplate, [
                        'PHONE' => urlencode($phone),
                        'TEXT' => urlencode($smsMessage)
                    ]);
                    if ($debug) {
                        $debugMessages['smsPhones'][] = $url;
                    } else {
                        $result = file_get_contents($url);
                    }
                }
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
    public function getEmailRegisterSubject()
    {
        $host = $this->server['HTTP_HOST'];
        if (function_exists('idn_to_utf8')) {
            $host = idn_to_utf8($host);
        }
        $host = mb_strtoupper($host);
        $subject = date(RAASViewWeb::i()->_('DATETIMEFORMAT')) . ' '
                 . sprintf(View_Web::i()->_('REGISTRATION_ON_SITE'), $host);
        return $subject;
    }


    /**
     * Получает вложения для письма
     * @param User $user Пользователь
     * @param Material $material Созданный материал
     * @param bool $forAdmin Уведомление для администратора
     *                       (если нет, то для пользователя)
     * @return array <pre>array<[
     *     'tmp_name' => string Путь к реальному файлу,
     *     'type' => string MIME-тип файла,
     *     'name' => string Имя файла
     * ]></pre>
     */
    public function getRegisterAttachments(
        User $feedback,
        Material $material = null,
        $forAdmin = true
    ) {
        return [];
    }
}
