<?php
/**
 * Контроллер раздела "Разработка"
 */
namespace RAAS\CMS\Users;

use RAAS\Application;
use RAAS\Abstract_Sub_Controller as RAASAbstractSubController;
use RAAS\Redirector;
use RAAS\StdSub;
use RAAS\CMS\EditFieldForm;
use RAAS\CMS\EditFieldGroupForm;
use RAAS\CMS\Package;
use RAAS\CMS\User;
use RAAS\CMS\User_Field;
use RAAS\CMS\UserFieldGroup;
use RAAS\CMS\ViewSub_Dev as CMSViewSubDev;

/**
 * Класс контроллера раздела "Разработка"
 */
class Sub_Dev extends RAASAbstractSubController
{
    protected static $instance;

    public function run()
    {
        $this->view->submenu = CMSViewSubDev::i()->devMenu();
        switch ($this->action) {
            case 'edit_field':
            case 'fields':
                $this->{$this->action}();
                break;
            case 'edit_fieldgroup':
                $this->editFieldGroup();
                break;
            case 'move_field_to_group':
                $this->moveFieldToGroup();
                break;
            case 'chvis_field':
            case 'vis_field':
            case 'invis_field':
            case 'delete_field':
            case 'show_in_table_field':
            case 'required_field':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $items = User_Field::getSet([
                        'where' => "classname = '" . Application::i()->SQL->real_escape_string(User::class) . "'"
                    ]);
                } else {
                    $items = array_map(function ($x) {
                        return new User_Field((int)$x);
                    }, $ids);
                }
                $items = array_values($items);
                $f = str_replace('_field', '', $this->action);
                $url2 .= '&action=fields';
                StdSub::$f($items, $this->url . $url2);
                break;
            case 'delete_fieldgroup':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new UserFieldGroup((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::delete($items, $this->url . '&action=fields');
                break;
            case 'webmaster':
                if (isset($_GET['confirm']) && $_GET['confirm']) {
                    $w = new Webmaster();
                    $w->createCab();
                }
                new Redirector($this->url);
                break;
            case 'billing_types':
                $this->view->billingTypes(['Set' => BillingType::getSet()]);
                break;
            case 'edit_billing_type':
                $this->editBillingType();
                break;
            case 'delete_billing_type':
                $ids = (array)$_GET['id'];
                $items = array_map(function ($x) {
                    return new BillingType((int)$x);
                }, $ids);
                $items = array_values($items);
                StdSub::delete($items, $this->url . '&action=billing_types');
                break;
            default:
                new Redirector(CMSViewSubDev::i()->url);
                break;
        }
    }


    /**
     * Поля пользователей
     */
    protected function fields()
    {
        if ((is_array($_POST['priority'] ?? null)) ||
            (is_array($_POST['fieldgrouppriority'] ?? null))
        ) {
            if (is_array($_POST['priority'] ?? null)) {
                Package::i()->setEntitiesPriority(User_Field::class, (array)$_POST['priority']);
            }
            if (is_array($_POST['fieldgrouppriority'] ?? null)) {
                Package::i()->setEntitiesPriority(
                    UserFieldGroup::class,
                    (array)$_POST['fieldgrouppriority']
                );
            }
            new Redirector('history:back');
            exit;
        }
        $this->view->fields(['Set' => $this->model->dev_fields()]);
    }


    /**
     * Редактирование поля пользователей
     */
    protected function edit_field()
    {
        $item = new User_Field((int)$this->id);
        $parentUrl = $this->url . '&action=fields';
        $Form = new EditFieldForm([
            'Item' => $item,
            'view' => $this->view,
            'parentUrl' => $parentUrl,
            'meta' => [
                'Parent' => new User(),
            ],
        ]);
        $out = $Form->process();
        $this->view->edit_field($out);
    }


    /**
     * Редактирование группы полей
     */
    protected function editFieldGroup()
    {
        $item = new UserFieldGroup((int)$this->id);
        $parentUrl = $this->url . '&action=fields';
        $form = new EditFieldGroupForm([
            'Item' => $item,
            'meta' => [
                'parentUrl' => $parentUrl
            ]
        ]);
        $out = $form->process();
        $this->view->editFieldGroup($out);
    }





    /**
     * Размещение полей в группе
     */
    protected function moveFieldToGroup()
    {
        $items = [];
        $ids = (array)$_GET['id'];
        if (in_array('all', $ids, true)) {
            $items = User_Field::getSet();
        } else {
            $items = array_map(function ($x) {
                return new User_Field((int)$x);
            }, $ids);
        }
        $items = array_values($items);
        $item = isset($items[0]) ? $items[0] : new User_Field();

        if ($items) {
            if (isset($_GET['gid'])) {
                foreach ($items as $row) {
                    $row->gid = $_GET['gid'];
                    $row->commit();
                }
                new Redirector(($_GET['back'] ?? null) ? 'history:back' : $this->url . '&action=fields');
            } else {
                $this->view->moveFieldToGroup([
                    'Item' => $item,
                    'items' => $items,
                ]);
                return;
            }
        }
    }


    /**
     * Редактирование типа биллинга
     */
    protected function editBillingType()
    {
        $item = new BillingType((int)$this->id);
        $form = new EditBillingTypeForm(['Item' => $item]);
        $out = $form->process();
        $this->view->editBillingType($out);
    }
}
