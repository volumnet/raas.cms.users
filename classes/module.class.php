<?php
/**
 * Модуль "Пользователи"
 */
namespace RAAS\CMS\Users;

use SOME\Pages;
use RAAS\Application;
use RAAS\Controller_Frontend;
use RAAS\Module as RAASModule;
use RAAS\CMS\Block_Type;
use RAAS\CMS\Group;
use RAAS\CMS\Package;
use RAAS\CMS\User;
use RAAS\CMS\User_Field;

/**
 * Класс модуля "Пользователи"
 */
class Module extends RAASModule
{
    /**
     * Не уведомлять пользователя об активации/блокировке
     */
    const AUTOMATIC_NOTIFICATION_NONE = 0;

    /**
     * Уведомлять пользователя только об активации
     */
    const AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION = 1;

    /**
     * Уведомлять пользователя об активации и блокировке
     */
    const AUTOMATIC_NOTIFICATION_BOTH = 2;

    protected static $instance;

    /**
     * Поля пользователей
     * @return array<User_Field>
     */
    public function dev_fields()
    {
        return User_Field::getSet();
    }


    /**
     * Возвращает список пользователей и подгрупп в группе
     * @param Group $group Родительская группа
     * @param [
     *            'order' => 'asc'|'desc' Порядок сортировки,
     *            'sort' => string Поле сортировки,
     *            'search_string' => string Строка поиска,
     *            'group_only' => bool|int Возвращать пользователей только
     *                                     из группы при полнотекстовом поиске,
     *            'page' => int Номер страницы в постраничной разбивке,
     *        ] $in Входные данные
     * @return [
     *             'Set' => array<User> Список пользователей,
     *             'GSet' => array<Group> Список подгрупп,
     *             'billingTypes' => array<Billing_Type> Все типы биллинга,
     *             'Pages' => Pages Постраничная разбивка,
     *             'columns' => array<User_Field> Поля для отображения,
     *             'sort' => string Поле сортировки,
     *             'order' => 'asc'|'desc' Порядок сортировки,
     *         ]
     */
    public function showlist(Group $group, array $in)
    {
        $temp = User_Field::getSet(['where' => "show_in_table"]);
        $columns = [];
        foreach ($temp as $row) {
            $columns[$row->urn] = $row;
        }
        unset($temp);

        $order = strtolower($in['order']) == 'desc' ? 'DESC' : 'ASC';
        $billingTypes = BillingType::getSet();
        $sqlQuery = "SELECT SQL_CALC_FOUND_ROWS tU.* ";
        foreach ($billingTypes as $billingType) {
            $sqlQuery .= ", IFNULL(tBA" . (int)$billingType->id . ".balance, 0) AS balance" . (int)$billingType->id;
        }
        $sqlQuery .= " FROM " . User::_tablename()
                  .  "   AS tU ";
        $sqlBind = [];
        if (isset($columns[$in['sort']]) && ($col = $columns[$in['sort']])) {
            $sqlQuery .= " LEFT JOIN " . User_Field::_dbprefix() . User_Field::data_table
                      .  "   AS tSort
                             ON tSort.pid = tU.id
                            AND tSort.fid = ?";
            $sqlBind[] = (int)$col->id;
        }
        foreach ($billingTypes as $billingType) {
            $sqlQuery .= " LEFT JOIN " . User::_dbprefix() . "cms_users_billing_accounts
                             AS tBA" . (int)$billingType->id . "
                             ON tBA" . (int)$billingType->id . ".uid = tU.id
                            AND tBA" . (int)$billingType->id . ".billing_type_id = ?";
            $sqlBind[] = (int)$billingType->id;
        }
        if ($group->id && (!isset($in['search_string'])) ||
            isset($in['group_only'])
        ) {
            $sqlQuery .= " LEFT JOIN " . User::_dbprefix() . "cms_users_groups_assoc
                             AS tUGA
                             ON tUGA.uid = tU.id";
        }

        $sqlQuery .= " WHERE 1 ";
        if ($group->id &&
            (!isset($in['search_string']) || isset($in['group_only']))
        ) {
            $sqlQuery .= " AND tUGA.gid = ?";
            $sqlBind[] = (int)$group->id;
        }
        if (isset($in['search_string']) && $in['search_string']) {
            $sqlQuery .= " AND (
                                   tU.login LIKE ?
                                OR tU.email LIKE ?
                                OR (
                                        (
                                            SELECT COUNT(*)
                                              FROM " . User_Field::_dbprefix() . User_Field::data_table
                      .  "                   WHERE pid = tU.id
                                               AND value LIKE ?
                                        ) > 0
                                )
                            ) ";
            for ($i = 0; $i < 3; $i++) {
                $sqlBind[] = '%' . $in['search_string'] . '%';
            }
        }

        $sqlQuery .= " GROUP BY tU.id ORDER BY tU.new DESC, ";
        if (isset($columns[$in['sort']])) {
            $sqlQuery .= " tSort.value ";
        } elseif (in_array($in['sort'], ['post_date', 'login', 'email']) ||
            preg_match('/^balance\\d+$/', $in['sort'])
        ) {
            $sqlQuery .= $in['sort'];
        } else {
            $in['sort'] = 'login';
            $sqlQuery .= $in['sort'];
        }
        $sqlQuery .= " " . $order;
        $Pages = new Pages(
            (int)$in['page'] ?: 1,
            Application::i()->registryGet('rowsPerPage')
        );
        $Set = User::getSQLSet([$sqlQuery, $sqlBind], $Pages);

        $GSet = $group->getChildSet('children');
        return [
            'Set' => $Set,
            'GSet' => $GSet,
            'billingTypes' => $billingTypes,
            'Pages' => $Pages,
            'columns' => $columns,
            'sort' => $in['sort'],
            'order' => $order
        ];
    }


    /**
     * Возвращает количество новых пользователей
     * @return int
     */
    public function newUsers()
    {
        $sqlQuery = "SELECT COUNT(*) FROM " . User::_tablename() . " WHERE new";
        $c = (int)$this->SQL->getvalue($sqlQuery);
        return $c;
    }


    /**
     * Получает текст уведомления об активации/блокировке
     * @param User $user Пользователь
     * @param bool|null $active Активен ли пользователь
     *                          (в этом случае уведомление об активации) или нет
     *                          (в этом случае уведомление о блокировке).
     *                          Если null, берется из параметра $user
     * @return string
     */
    public function getActivationNotification(User $user, $active = null)
    {
        $snippet = $this->registryGet('activation_notify');
        $User = $user;
        if ($active === null) {
            $active = (bool)$user->vis;
        }
        ob_start();
        eval('?' . '>' . $snippet);
        $text = ob_get_contents();
        ob_end_clean();
        return $text;
    }


    /**
     * Отправить уведомление об активации/блокировке пользователю
     * @param User $user Пользователь
     */
    public function sendNotification(User $user)
    {
        $lang = $user->lang ? $user->lang : $this->view->language;
        Controller_Frontend::i()->exportLang(Application::i(), $lang);
        Controller_Frontend::i()->exportLang(Package::i(), $lang);
        foreach (Package::i()->modules as $row) {
            Controller_Frontend::i()->exportLang($row, $lang);
        }
        $text = $this->getActivationNotification($user);
        $subject = sprintf(
            $this->view->_(
                $user->vis ?
                'ACTIVATION_NOTIFICATION' :
                'BLOCK_NOTIFICATION'
            ),
            $_SERVER['HTTP_HOST']
        );
        Application::i()->sendmail(
            trim($user->email),
            trim($subject),
            trim($text),
            (
                $this->view->_('ADMINISTRATION_OF_SITE') . ' ' .
                $_SERVER['HTTP_HOST']
            ),
            'info@' . $_SERVER['HTTP_HOST']
        );
    }


    /**
     * Возвращает пользователей по полнотекстовому поиску
     * @param string $search Строка поиска
     * @param int $limit Максимальное количество возвращаемых пользователей
     * @return array<User>
     */
    public function getUsersBySearch($search, $limit = 10)
    {
        $sqlQuery = "SELECT tU.*
                       FROM " . User::_tablename()
                  . "    AS tU
                       JOIN " . User_Field::_dbprefix() . User_Field::data_table
                  . "    AS tD
                         ON tD.pid = tU.id
                       JOIN " . User_Field::_tablename()
                  . "    AS tF
                         ON tF.classname = ?
                        AND tF.id = tD.fid
                      WHERE (
                               tU.login LIKE ?
                            OR tU.email LIKE ?
                            OR tD.value LIKE ?
                        )
                   GROUP BY tU.id
                   ORDER BY tU.login
                      LIMIT ?";
        $sqlBind = [User::class];
        for ($i = 0; $i < 3; $i++) {
            $sqlBind[] = '%' . $search . '%';
        }
        $sqlBind[] = (int)$limit;
        $Set = User::getSQLSet([$sqlQuery, $sqlBind]);
        return $Set;
    }


    /**
     * Регистрирует типы блоков
     */
    public function registerBlockTypes()
    {
        Block_Type::registerType(
            Block_Register::class,
            ViewBlockRegister::class,
            EditBlockRegisterForm::class
        );
        Block_Type::registerType(
            Block_LogIn::class,
            ViewBlockLogIn::class,
            EditBlockLogInForm::class
        );
        Block_Type::registerType(
            Block_Activation::class,
            ViewBlockActivation::class,
            EditBlockActivationForm::class
        );
        Block_Type::registerType(
            Block_Recovery::class,
            ViewBlockRecovery::class,
            EditBlockRecoveryForm::class
        );
    }


    public function install()
    {
        if (!$this->registryGet('installDate') ||
            !$this->registryGet('baseVersion') ||
            ($this->registryGet('baseVersion') != $this->version)
        ) {
            if (!trim($this->registryGet('activation_notify'))) {
                $this->registrySet('activation_notify', file_get_contents(
                    $this->resourcesDir .
                    '/interfaces/activation_notification.php'
                ));
            }
            if ($this->registryGet('automatic_notification') === null) {
                $this->registrySet(
                    'automatic_notification',
                    self::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION
                );
            }
        }
        parent::install();
    }
}
