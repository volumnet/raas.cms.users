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
        foreach (array('sort', 'order') as $var) {
            if (isset($_GET[$var])) {
                $_COOKIE[$var] = $_GET[$var];
                setcookie($var, $_COOKIE[$var], time() + Application::i()->registryGet('cookieLifetime') * 86400, '/');
            }
        }
        $IN = $this->model->showlist(
            isset($_GET['search_string']) ? $_GET['search_string'] : '', 
            isset($_COOKIE['sort']) ? $_COOKIE['sort'] : 'login',
            isset($_COOKIE['order']) ? $_COOKIE['order'] : 'asc',
            isset($_GET['page']) ? (int)$_GET['page'] : 1
        );
        $Set = $IN['Set'];
        $Pages = $IN['Pages'];
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
        $this->view->stdView->stdEdit($Form->process(), 'getUserContextMenu');
    }
}