<?php
namespace RAAS\CMS\Users;

use \RAAS\CMS\User;
use \RAAS\CMS\Group;
use \RAAS\CMS\User_Field;
use \RAAS\CMS\Block_Type;
use \RAAS\Controller_Frontend;
use \RAAS\IContext;
use \RAAS\CMS\Page;
use RAAS\Application;
use RAAS\CMS\Package;

class Module extends \RAAS\Module
{
    const AUTOMATIC_NOTIFICATION_NONE = 0;
    const AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION = 1;
    const AUTOMATIC_NOTIFICATION_BOTH = 2;

    protected static $instance;

    public function __get($var)
    {
        switch ($var) {
            default:
                return parent::__get($var);
                break;
        }
    }


    public function dev_fields()
    {
        return User_Field::getSet();
    }


    public function showlist(Group $Group, array $IN)
    {
        $temp = User_Field::getSet(array('where' => "show_in_table"));
        $columns = array();
        foreach ($temp as $row) {
            $columns[$row->urn] = $row;
        }
        unset($temp);

        $order = strtolower($IN['order']) == 'desc' ? 'DESC' : 'ASC';
        $SQL_query = "SELECT SQL_CALC_FOUND_ROWS tU.* FROM " . User::_tablename() .  " AS tU ";
        if (isset($columns[$IN['sort']]) && ($col = $columns[$IN['sort']])) {
            $SQL_query .= " LEFT JOIN " . User_Field::_dbprefix() . User_Field::data_table . " AS tSort ON tSort.pid = tU.id AND tSort.fid = " . (int)$col->id;
        }
        if (($Group->id && (!isset($IN['search_string'])) || isset($IN['group_only']))) {
            $SQL_query .= " LEFT JOIN " . User::_dbprefix() . "cms_users_groups_assoc AS tUGA ON tUGA.uid = tU.id";
        }

        $SQL_query .= " WHERE 1 ";
        if (($Group->id && (!isset($IN['search_string'])) || isset($IN['group_only']))) {
            $SQL_query .= " AND tUGA.gid = " . (int)$Group->id;
        }
        if (isset($IN['search_string']) && $IN['search_string']) {
            $SQL_query .= " AND (
                                   tU.login LIKE '%" . $this->SQL->escape_like($IN['search_string']) . "%'
                                OR tU.email LIKE '%" . $this->SQL->escape_like($IN['search_string']) . "%'
                                OR ((SELECT COUNT(*) FROM " . User_Field::_dbprefix() . User_Field::data_table . " WHERE pid = tU.id AND value LIKE '%" . $this->SQL->escape_like($IN['search_string']) . "%') > 0)
                            ) ";
        }

        $SQL_query .= " GROUP BY tU.id ORDER BY tU.new DESC, ";
        if (isset($columns[$IN['sort']])) {
            $SQL_query .= " tSort.value ";
        } elseif (in_array($IN['sort'], array('post_date', 'login', 'email'))) {
            $SQL_query .= $IN['sort'];
        } else {
            $IN['sort'] = 'login';
            $SQL_query .= $IN['sort'];
        }
        $SQL_query .= " " . $order;
        $Pages = new \SOME\Pages((int)$IN['page'] ?: 1, Application::i()->registryGet('rowsPerPage'));
        $Set = User::getSQLSet($SQL_query, $Pages);

        $GSet = $Group->getChildSet('children');
        return array('Set' => $Set, 'GSet' => $GSet, 'Pages' => $Pages, 'columns' => $columns, 'sort' => $IN['sort'], 'order' => $order);
    }


    public function newUsers()
    {
        $SQL_query = "SELECT COUNT(*) FROM " . User::_tablename() . " WHERE new";
        $c = (int)$this->SQL->getvalue($SQL_query);
        return $c;
    }


    public function getActivationNotification(User $User, $active = null)
    {
        $snippet = $this->registryGet('activation_notify');
        if ($active === null) {
            $active = (bool)$User->vis;
        }
        ob_start();
        eval('?' . '>' . $snippet);
        $text = ob_get_contents();
        ob_end_clean();
        return $text;
    }


    public function sendNotification(User $User)
    {
        $lang = $User->lang ? $User->lang : $this->view->language;
        Controller_Frontend::i()->exportLang(Application::i(), $lang);
        Controller_Frontend::i()->exportLang(Package::i(), $lang);
        foreach (Package::i()->modules as $row) {
            Controller_Frontend::i()->exportLang($row, $lang);
        }
        $text = $this->getActivationNotification($User);
        $subject = sprintf($this->view->_($User->vis ? 'ACTIVATION_NOTIFICATION' : 'BLOCK_NOTIFICATION'), $_SERVER['HTTP_HOST']);
        Application::i()->sendmail(trim($User->email), trim($subject), trim($text), $this->view->_('ADMINISTRATION_OF_SITE') . ' ' . $_SERVER['HTTP_HOST'], 'info@' . $_SERVER['HTTP_HOST']);
    }


    public function getUsersBySearch($search, $limit = 10)
    {
        $SQL_query = "SELECT tU.* FROM " . User::_tablename() . " AS tU
                        JOIN " . User_Field::_dbprefix() . User_Field::data_table . " AS tD ON tD.pid = tU.id
                        JOIN " . User_Field::_tablename() . " AS tF ON tF.classname = 'RAAS\\\\CMS\\\\User' AND tF.id = tD.fid
                       WHERE (
                                tU.login LIKE '%" . $this->SQL->escape_like($search) . "%'
                             OR tU.email LIKE '%" . $this->SQL->escape_like($search) . "%'
                             OR tD.value LIKE '%" . $this->SQL->escape_like($search) . "%'
                        ) ";
        $SQL_query .= " GROUP BY tU.id ORDER BY tU.login LIMIT " . (int)$limit;
        $Set = User::getSQLSet($SQL_query);
        return $Set;
    }


    public function registerBlockTypes()
    {
        Block_Type::registerType('RAAS\\CMS\\Users\\Block_Register', 'RAAS\\CMS\\Users\\ViewBlockRegister', 'RAAS\\CMS\\Users\\EditBlockRegisterForm');
        Block_Type::registerType('RAAS\\CMS\\Users\\Block_LogIn', 'RAAS\\CMS\\Users\\ViewBlockLogIn', 'RAAS\\CMS\\Users\\EditBlockLogInForm');
        Block_Type::registerType('RAAS\\CMS\\Users\\Block_Activation', 'RAAS\\CMS\\Users\\ViewBlockActivation', 'RAAS\\CMS\\Users\\EditBlockActivationForm');
        Block_Type::registerType('RAAS\\CMS\\Users\\Block_Recovery', 'RAAS\\CMS\\Users\\ViewBlockRecovery', 'RAAS\\CMS\\Users\\EditBlockRecoveryForm');
    }


    public function install()
    {
        if (!$this->registryGet('installDate')) {
            if (!trim($this->registryGet('activation_notify'))) {
                $this->registrySet('activation_notify', file_get_contents(
                    $this->resourcesDir .
                    '/interfaces/activation_notification.php'
                ));
            }
            if ($this->registryGet('automatic_notification') === null) {
                $this->registrySet('automatic_notification', self::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION);
            }
        }
        parent::install();
    }
}
