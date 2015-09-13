<?php
namespace RAAS\CMS\Users;
use \RAAS\Redirector;
use \RAAS\CMS\EditFieldForm;
use \RAAS\CMS\Form as CMSForm;
use \RAAS\StdSub;
use \RAAS\CMS\User;
use \RAAS\CMS\User_Field;

class Sub_Dev extends \RAAS\Abstract_Sub_Controller
{
    protected static $instance;
    
    public function run()
    {
        $this->view->submenu = \RAAS\CMS\ViewSub_Dev::i()->devMenu();
        switch ($this->action) {
            case 'edit_field':
                $this->{$this->action}();
                break;
            case 'fields':
                $this->view->fields(array('Set' => $this->model->dev_fields()));
                break;
            case 'move_up_field': case 'move_down_field': case 'delete_field': case 'show_in_table_field': case 'required_field':
                $Item = new User_Field((int)$this->id);
                $f = str_replace('_field', '', $this->action);
                $url2 .= '&action=fields';
                StdSub::$f($Item, $this->url . $url2);
                break;
            case 'webmaster':
                if (isset($_GET['confirm']) && $_GET['confirm']) {
                    $w = new Webmaster();
                    $w->createCab();
                }
                new Redirector($this->url);
                break;
            default:
                new Redirector(\RAAS\CMS\ViewSub_Dev::i()->url);
                break;
        }
    }


    protected function edit_field()
    {
        $Item = new User_Field((int)$this->id);
        $parentUrl = $this->url . '&action=fields';
        $Form = new EditFieldForm(array('Item' => $Item, 'view' => $this->view, 'parentUrl' => $parentUrl));
        $OUT = $Form->process();
        $this->view->edit_field($OUT);
    }
}