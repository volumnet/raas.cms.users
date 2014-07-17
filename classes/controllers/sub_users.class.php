<?php
namespace RAAS\CMS\Users;
use \RAAS\User;
use \RAAS\StdSub;

class Sub_Users extends \RAAS\Abstract_Sub_Controller
{
    protected static $instance;
    
    public function run()
    {
        switch ($this->action) {
            case 'edit':
                $this->{$this->action}();
                break;
            case 'delete':
                $Item = new User($this->id);
                StdSub::delete($Item, $this->url);
                break;
            default:
                $this->showlist();
                break;
        }
    }
    
    
    protected function showlist()
    {
        $IN = $this->model->showlist();
        $Set = $IN['Set'];
        $Pages = $IN['Pages'];
        $Item = $IN['Parent'];
        
        $OUT['Item'] = $Item;
        $OUT['columns'] = $IN['columns'];
        $OUT['Set'] = $Set;
        $OUT['Pages'] = $Pages;
        $OUT['search_string'] = isset($_GET['search_string']) ? (string)$_GET['search_string'] : '';
        $this->view->showlist($OUT);
    }
    
    
    protected function edit()
    {
        $Item = new User((int)$this->id);
        $Form = new EditUserForm(array('Item' => $Item, 'view' => $this->view));
        $this->view->edit($Form->process());
    }
}