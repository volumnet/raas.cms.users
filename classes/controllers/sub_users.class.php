<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\User;
use \RAAS\StdSub;
use \RAAS\Application;

class Sub_Users extends \RAAS\Abstract_Sub_Controller
{
    protected static $instance;
    
    public function run()
    {
        switch ($this->action) {
            case 'edit':
                $this->{$this->action}();
                break;
            case 'delete': case 'chvis':
                $action = $this->action;
                $Item = new User($this->id);
                StdSub::$action($Item, $this->url);
                break;
            default:
                $this->showlist();
                break;
        }
    }
    
    
    protected function showlist()
    {
        foreach (array('sort', 'order') as $var) {
            if (isset($_GET[$var])) {
                $_COOKIE[$var] = $_GET[$var];
                setcookie($var, $_COOKIE[$var], time() + Application::i()->registryGet('cookieLifetime') * 86400, '/');
            }
        }
        $OUT = $this->model->showlist(
            isset($_GET['search_string']) ? $_GET['search_string'] : '', 
            isset($_COOKIE['sort']) ? $_COOKIE['sort'] : 'login',
            isset($_COOKIE['order']) ? $_COOKIE['order'] : 'asc',
            isset($_GET['page']) ? (int)$_GET['page'] : 1
        );
        $OUT['search_string'] = isset($_GET['search_string']) ? (string)$_GET['search_string'] : '';
        $this->view->showlist($OUT);
    }
    
    
    protected function edit()
    {
        $Item = new User((int)$this->id);
        $Item->visit();
        $Form = new EditUserForm(array('Item' => $Item, 'view' => $this->view));
        $OUT = $Form->process();
        $this->view->stdView->stdEdit($OUT, 'getUserContextMenu');
    }
}