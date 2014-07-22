<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\User;

class ViewSub_Users extends \RAAS\Abstract_Sub_View
{
    protected static $instance;
    
    public function showlist(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new UsersTable(
            array_merge($IN, array('view' => $this, 'editAction' => 'edit', 'ctxMenu' => 'getUserContextMenu', 'sort' => $IN['sort'], 'order' => $IN['order']))
        );
        $this->assignVars($IN);
        $this->title = $this->_('USERS');
        $this->contextmenu = array(array('name' => $this->_('CREATE_USER'), 'href' => $this->url . '&action=edit', 'icon' => 'plus'));
        $this->template = $IN['Table']->template;
    }


    public function getUserContextMenu(User $Item, $i = 0, $c = 0) 
    {
        return $this->stdView->stdContextMenu($Item, 0, 0, 'edit', '', 'delete');
    }
}