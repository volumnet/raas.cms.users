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
                $action = $this->action;
                $Item = new User($this->id);
                StdSub::$action($Item, $this->url);
                break;
            case 'delete_group': 
                $Item = new Group((int)$this->id);
                StdSub::delete($Item, $this->url);
                break;
            case 'add_group': 
                $Group = new Group(isset($this->nav['gid']) ? (int)$this->nav['gid'] : 0);
                StdSub::associate(new User((int)$this->id), $this->url . '&id=' . (int)$Group->id, true, $Group->id, $Group);
                break;
            case 'del_group':
                $Group = new Group(isset($this->nav['gid']) ? (int)$this->nav['gid'] : 0);
                StdSub::deassociate(new User((int)$this->id), $this->url . '&id=' . (int)$Group->id, true, $Group->id, $Group);
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