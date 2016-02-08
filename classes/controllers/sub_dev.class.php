<?php
namespace RAAS\CMS\Users;

use \RAAS\Redirector;
use \RAAS\CMS\EditFieldForm;
use \RAAS\CMS\Form as CMSForm;
use \RAAS\StdSub;
use \RAAS\CMS\User;
use \RAAS\CMS\User_Field;
use \RAAS\CMS\Package;
use \RAAS\Application;

class Sub_Dev extends \RAAS\Abstract_Sub_Controller
{
    protected static $instance;
    
    public function run()
    {
        $this->view->submenu = \RAAS\CMS\ViewSub_Dev::i()->devMenu();
        switch ($this->action) {
            case 'edit_field': case 'fields':
                $this->{$this->action}();
                break;
            case 'delete_field': case 'show_in_table_field': case 'required_field':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $parentClassname = 'RAAS\\CMS\\User';
                    $items = User_Field::getSet(array('where' => "classname = '" . Application::i()->SQL->real_escape_string($parentClassname) . "'"));
                } else {
                    $items = array_map(function($x) { return new User_Field((int)$x); }, $ids);
                }
                $items = array_values($items);
                $f = str_replace('_field', '', $this->action);
                $url2 .= '&action=fields';
                StdSub::$f($items, $this->url . $url2);
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


    protected function fields()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['priority']) && is_array($_POST['priority'])) {
                Package::i()->setEntitiesPriority('\RAAS\CMS\User_Field', (array)$_POST['priority']);
            }
        }
        $this->view->fields(array('Set' => $this->model->dev_fields()));
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