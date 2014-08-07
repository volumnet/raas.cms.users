<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\User;
use \RAAS\CMS\User_Field;
use \RAAS\CMS\Block_Type;
use \RAAS\Controller_Frontend;
use \RAAS\IContext;
use \RAAS\CMS\Page;

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


    public function showlist($search_string = null, $sort = 'login', $order = 'asc', $page = 1)
    {
        $temp = User_Field::getSet(array('where' => "show_in_table"));
        $columns = array();
        foreach ($temp as $row) {
            $columns[$row->urn] = $row;
        }
        unset($temp);

        $order = strtolower($order) == 'desc' ? 'DESC' : 'ASC';
        $SQL_query = "SELECT SQL_CALC_FOUND_ROWS tU.* FROM " . User::_tablename() .  " AS tU ";
        if (isset($columns[$sort]) && ($col = $columns[$sort])) {
            $SQL_query .= " LEFT JOIN " . User_Field::_dbprefix() . User_Field::data_table . " AS tSort ON tSort.pid = tU.id AND tSort.fid = " . (int)$col->id;
        }
       
        $SQL_query .= " WHERE 1 ";
        if (isset($search_string) && $search_string) {
            $SQL_query .= " AND (SELECT COUNT(*) FROM " . User_Field::_dbprefix() . User_Field::data_table . " WHERE pid = tU.id AND value LIKE '%" . $this->SQL->escape_like($search_string) . "%') > 0 ";
        }
        
        $SQL_query .= " GROUP BY tU.id ORDER BY tU.new DESC, ";
        if (isset($columns[$sort])) {
            $SQL_query .= " tSort.value ";
        } elseif (in_array($sort, array('post_date', 'login', 'email'))) {
            $SQL_query .= $sort;
        } else {
            $sort = 'login';
            $SQL_query .= $sort;
        }
        $SQL_query .= " " . $order;
        $Pages = new \SOME\Pages((int)$page ?: 1, $this->registryGet('rowsPerPage'));
        $Set = User::getSQLSet($SQL_query, $Pages);
        return array('Set' => $Set, 'Pages' => $Pages, 'columns' => $columns, 'sort' => $sort, 'order' => $order);
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
                $this->registrySet('activation_notify', file_get_contents($this->resourcesDir . '/activation_notify.php'));
            }
            if ($this->registryGet('automatic_notification') === null) {
                $this->registrySet('automatic_notification', self::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION);
            }
        }
        parent::install();
    }
}