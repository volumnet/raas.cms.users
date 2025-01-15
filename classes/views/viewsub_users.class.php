<?php
namespace RAAS\CMS\Users;

use \RAAS\CMS\User;
use \RAAS\CMS\Group;

class ViewSub_Users extends \RAAS\Abstract_Sub_View
{
    protected static $instance;

    public function edit_user(array $IN = array())
    {
        $this->path[] = array('name' => $this->_('USERS'), 'href' => $this->url);
        $this->submenu = $this->getGroupsMenu(new Group(), new Group());
        $this->js[] = $this->package->view->publicURL . '/field.inc.js';
        $this->stdView->stdEdit($IN, 'getUserContextMenu');
    }


    public function edit_group(array $IN = array())
    {
        $this->getGroupsPath($IN['Item']);
        $this->submenu = $this->getGroupsMenu(new Group(), $IN['Item']->parent);
        $this->stdView->stdEdit($IN, 'getGroupContextMenu');
    }


    public function showlist(array $IN = array())
    {
        $view = $this;
        $IN['Table'] = new UsersTable(
            array_merge($IN, array('view' => $this, 'editAction' => 'edit', 'ctxMenu' => 'getUserContextMenu', 'sort' => $IN['sort'], 'order' => $IN['order']))
        );
        $IN['GroupsTable'] = new GroupsTable(array('Set' => $IN['GSet'], 'Group' => $IN['Group']));

        $this->assignVars($IN);
        $this->getGroupsPath($IN['Group']);
        $this->submenu = $this->getGroupsMenu(new Group(), $IN['Group']);
        $this->contextmenu[] = array(
            'href' => $this->url . '&action=edit' . ((int)$IN['Group']->id ? '&pid=' . (int)$IN['Group']->id : ''),
            'name' => $this->_('CREATE_USER'),
            'icon' => 'plus'
        );
        $this->contextmenu[] = array(
            'href' => $this->url . '&action=edit_group' . ((int)$IN['Group']->id ? '&pid=' . (int)$IN['Group']->id : ''),
            'name' => $this->_('ADD_GROUP2'),
            'icon' => 'plus'
        );
        if ($IN['Group']->id) {
            $this->contextmenu[] = array(
                'href' => $this->url . '&action=edit_group&id=' . (int)$IN['Group']->id, 'name' => $this->_('EDIT_GROUP2'), 'icon' => 'edit'
            );
            $this->contextmenu[] = array(
                'href' => $this->url . '&action=delete_group&id=' . (int)$IN['Group']->id,
                'name' => $this->_('DELETE_GROUP'),
                'icon' => 'remove',
                'onclick' => "return confirm('" . $this->_('DELETE_GROUP_TEXT') . "')"
            );
        }
        $this->title = $IN['Group']->id ? htmlspecialchars($IN['Group']->name) : $this->_('USERS');
        $this->template = 'showlist';
    }


    public function getUserContextMenu(User $Item, ?Group $Group = null)
    {
        $arr = array();
        if ($Item->id) {
            $edit = ($this->action == 'edit');
            if (!$edit) {
                $arr[] = array('href' => $this->url . '&action=edit&id=' . (int)$Item->id, 'name' => $this->_('EDIT'), 'icon' => 'edit');
            }
            if (isset($Group->id) && $Group->id) {
                if (in_array($Group->id, $Item->groups_ids)) {
                    $arr[] = array(
                        'href' => $this->url . '&action=del_group&id=' . (int)$Item->id . '&gid=' . (int)$Group->id . ($edit ? '' : '&back=1'),
                        'name' => $this->_('DELETE_FROM_GROUP'),
                        'icon' => 'remove-circle'
                    );
                } else {
                    $arr[] = array(
                        'href' => $this->url . '&action=add_group&id=' . (int)$Item->id . '&gid=' . (int)$Group->id . ($edit ? '' : '&back=1'),
                        'name' => $this->_('ADD_TO_GROUP'),
                        'icon' => 'ok-circle'
                    );
                }
            }
            $arr[] = array(
                'href' => $this->url . '&action=delete&id=' . (int)$Item->id . ($edit ? '' : '&back=1'),
                'name' => $this->_('DELETE'),
                'icon' => 'remove',
                'onclick' => 'return confirm(\'' . $this->_('DELETE_USER_TEXT') . '\')'
              );
        }
        $arr[] = array(
            'name' => $Item->vis ? $this->_('ACTIVE') : '<span class="muted">' . $this->_('INACTIVE') . '</span>',
            'href' => $this->url . '&action=chvis&id=' . (int)$Item->id . '&back=1',
            'icon' => $Item->vis ? 'ok' : '',
            'title' => $this->_($Item->vis ? 'BLOCK_USER' : 'ACTIVATE')
        );
        return $arr;
    }


    public function getAllUsersContextMenu(Group $Group)
    {
        $arr = array();
        if ($Group->id) {
            $arr[] = array(
                'name' => $this->_('ADD_TO_GROUP'),
                'href' => $this->url . '&action=add_group&gid=' . (int)$Group->id . '&back=1',
                'icon' => 'ok-circle',
                'title' => $this->_('ADD_TO_GROUP')
            );
            $arr[] = array(
                'name' => $this->_('DELETE_FROM_GROUP'),
                'href' => $this->url . '&action=del_group&gid=' . (int)$Group->id . '&back=1',
                'icon' => 'remove-circle',
                'title' => $this->_('DELETE_FROM_GROUP')
            );
        }
        $arr[] = array(
            'name' => $this->_('ACTIVATE'),
            'href' => $this->url . '&action=vis&back=1',
            'icon' => 'ok-circle',
            'title' => $this->_('ACTIVATE')
        );
        $arr[] = array(
            'name' => $this->_('BLOCK_USER'),
            'href' => $this->url . '&action=invis&back=1',
            'icon' => 'ban-circle',
            'title' => $this->_('BLOCK_USER')
        );
        $arr[] = array(
            'name' => $this->_('DELETE'),
            'href' => $this->url . '&action=delete&back=1',
            'icon' => 'remove',
            'onclick' => 'return confirm(\'' . $this->_('DELETE_MULTIPLE_TEXT') . '\')'
        );
        return $arr;
    }


    public function getGroupContextMenu(Group $Item)
    {
        return $this->stdView->stdContextMenu($Item, 0, 0, 'edit_group', '', 'delete_group');
    }


    public function getAllGroupsContextMenu()
    {
        $arr = array();
        $arr[] = array(
            'name' => $this->_('DELETE'),
            'href' => $this->url . '&action=delete_group&back=1',
            'icon' => 'remove',
            'onclick' => 'return confirm(\'' . $this->_('DELETE_MULTIPLE_TEXT') . '\')'
        );
        return $arr;
    }


    private function getGroupsMenu(Group $node, Group $current)
    {
        $submenu = array();
        foreach ($node->children as $row) {
            $submenu[] = array('name' => htmlspecialchars($row->name), 'href' => $this->url . '&id=' . (int)$row->id, 'submenu' => $this->getGroupsMenu($row, $current));
        }
        return $submenu;
    }

    private function getGroupsPath(Group $Group)
    {
        if ($Group->id || $Group->pid) {
            $this->path[] = array('name' => $this->_('USERS'), 'href' => $this->url . '#groups');
            if ($Group->parents) {
                foreach ($Group->parents as $row) {
                    $this->path[] = array('name' => htmlspecialchars($row->name), 'href' => $this->url . '&id=' . (int)$row->id . '#groups');
                }
            }
        }
    }
}
