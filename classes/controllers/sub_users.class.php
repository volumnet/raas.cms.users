<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\User;
use \RAAS\CMS\Group;
use \RAAS\StdSub;
use \RAAS\Application;

class Sub_Users extends \RAAS\Abstract_Sub_Controller
{
    protected static $instance;
    
    public function run()
    {
        switch ($this->action) {
            case 'url':
                return '?p=' . $this->packageName . '&module=' . $this->moduleName;
                break;
            case 'edit': case 'edit_group':
                $this->{$this->action}();
                break;
            case 'delete': case 'chvis':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $items = User::getSet();
                } else {
                    $items = array_map(function($x) { return new User((int)$x); }, $ids);
                    $items = array_values($items);
                }
                $action = $this->action;
                StdSub::$action($items, $this->url);
                break;
            case 'delete_group': 
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $pids = (array)$_GET['pid'];
                    $pids = array_filter($pids, 'trim');
                    $pids = array_map('intval', $pids);
                    if ($pids) {
                        $items = Group::getSet(array('where' => "pid IN (" . implode(", ", $pids) . ")"));
                    }
                } else {
                    $items = array_map(function($x) { return new Group((int)$x); }, $ids);
                }
                $items = array_values($items);
                StdSub::delete($items, $this->url);
                break;
            case 'add_group': 
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $items = User::getSet();
                } else {
                    $items = array_map(function($x) { return new User((int)$x); }, $ids);
                    $items = array_values($items);
                }
                $Group = new Group(isset($this->nav['gid']) ? (int)$this->nav['gid'] : 0);
                StdSub::associate($items, $this->url . '&id=' . (int)$Group->id, true, $Group->id, $Group);
                break;
            case 'del_group':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $items = User::getSet();
                } else {
                    $items = array_map(function($x) { return new User((int)$x); }, $ids);
                    $items = array_values($items);
                }
                $Group = new Group(isset($this->nav['gid']) ? (int)$this->nav['gid'] : 0);
                StdSub::deassociate($items, $this->url . '&id=' . (int)$Group->id, true, $Group->id, $Group);
                break;
            default:
                $this->showlist();
                break;
        }
    }
    
    
    protected function showlist()
    {
        $Group = new Group($this->id);
        foreach (array('sort', 'order') as $var) {
            $OUT[$key] = isset($this->nav[$key]) ? $this->nav[$key] : null;
        }
        $OUT = $this->model->showlist($Group, $this->nav);
        if (!$OUT['sort']) {
            $OUT['sort'] = 'login';
            $OUT['order'] = 'asc';
        } elseif (!$OUT['order']) {
            $OUT['order'] = 'asc';
        }
        $OUT['Group'] = $Group;
        $this->view->showlist($OUT);
    }
    
    
    protected function edit()
    {
        $Item = new User((int)$this->id);
        $Item->visit();
        $Form = new EditUserForm(array('Item' => $Item, 'view' => $this->view));
        $OUT = $Form->process();
        $this->view->edit_user($OUT, 'getUserContextMenu');
    }
    
    
    protected function edit_group()
    {
        $Item = new Group((int)$this->id);
        if (!$Item->id) {
            $Item->pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
        }
        $Form = new EditGroupForm(array('Item' => $Item));
        $this->view->edit_group($Form->process(), 'getGroupContextMenu');
    }
}