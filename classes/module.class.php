<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\User;

class Module extends \RAAS\Module
{
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
        
        $SQL_query .= " GROUP BY tU.id ORDER BY tU.vis ASC, ";
        if (isset($columns[$sort])) {
            $SQL_query .= " tSort.value ";
        } elseif (in_array($sort, array('post_date', 'login', 'email'))) {
            $SQL_query .= $sort;
        } else {
            $SQL_query .= 'login';
        }
        $SQL_query .= " " . $order;
        $Pages = new \SOME\Pages((int)$page ?: 1, $this->registryGet('rowsPerPage'));
        $Set = User::getSQLSet($SQL_query, $Pages);
        return array('Set' => $Set, 'Pages' => $Pages, 'columns' => $columns);
    }

}