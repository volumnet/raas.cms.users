<?php
/**
 * Раздел "Пользователи"
 */
namespace RAAS\CMS\Users;

use RAAS\Abstract_Sub_Controller as RAASAbstractSubController;
use RAAS\Redirector;
use RAAS\StdSub;
use RAAS\CMS\Group;
use RAAS\CMS\User;

/**
 * Класс раздела "Пользователи"
 */
class Sub_Users extends RAASAbstractSubController
{
    protected static $instance;

    public function run()
    {
        switch ($this->action) {
            case 'url':
                return '?p=' . $this->packageName .
                       '&module=' . $this->moduleName;
                break;
            case 'edit':
            case 'edit_group':
                $this->{$this->action}();
                break;
            case 'delete':
            case 'chvis':
            case 'vis':
            case 'invis':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $items = User::getSet();
                } else {
                    $items = array_map(function ($x) {
                        return new User((int)$x);
                    }, $ids);
                    $items = array_values($items);
                }
                $action = $this->action;
                $notifyItems = $notifyActive = $notifyBlock = [];
                if (in_array($action, ['chvis', 'vis', 'invis']) &&
                    ($an = Module::i()->registryGet('automatic_notification'))
                ) {
                    $notifyItems = array_filter($items, function ($x) {
                        return $x->email;
                    });
                    // Уведомление об активации
                    if (in_array($an, [
                        Module::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION,
                        Module::AUTOMATIC_NOTIFICATION_BOTH
                    ])) {
                        if (in_array($action, ['chvis', 'vis'])) {
                            $notifyActive = array_filter(
                                $notifyItems,
                                function ($x) {
                                    return !$x->vis;
                                }
                            );
                        }
                    }
                    // Уведомление о блокировке
                    if ($an == Module::AUTOMATIC_NOTIFICATION_BOTH) {
                        if (in_array($action, ['chvis', 'invis'])) {
                            $notifyBlock = array_filter(
                                $notifyItems,
                                function ($x) {
                                    return $x->vis;
                                }
                            );
                        }
                    }
                    $notifyItems = array_merge($notifyActive, $notifyBlock);
                }
                $items = StdSub::getItems($items);
                foreach ($items as $row) {
                    switch ($action) {
                        case 'delete':
                            $classname = get_class($row);
                            $classname::delete($row);
                            break;
                        case 'vis':
                            $row->vis = 1;
                            $row->commit();
                            break;
                        case 'invis':
                            $row->vis = 0;
                            $row->commit();
                            break;
                        case 'chvis':
                            $row->vis = (int)!$row->vis;
                            $row->commit();
                            break;
                    }
                }
                foreach ($notifyItems as $row) {
                    Module::i()->sendNotification($row);
                }
                new Redirector('history:back');
                break;
            case 'delete_group':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $pids = (array)$_GET['pid'];
                    $pids = array_filter($pids, 'trim');
                    $pids = array_map('intval', $pids);
                    if ($pids) {
                        $items = Group::getSet([
                            'where' => "pid IN (" . implode(", ", $pids) . ")"
                        ]);
                    }
                } else {
                    $items = array_map(function ($x) {
                        return new Group((int)$x);
                    }, $ids);
                }
                $items = array_values($items);
                StdSub::delete($items, $this->url);
                break;
            case 'add_group':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $items = User::getSet();
                } else {
                    $items = array_map(function ($x) {
                        return new User((int)$x);
                    }, $ids);
                    $items = array_values($items);
                }
                $Group = new Group(
                    isset($this->nav['gid']) ?
                    (int)$this->nav['gid'] :
                    0
                );
                StdSub::associate(
                    $items,
                    $this->url . '&id=' . (int)$Group->id,
                    true,
                    $Group->id,
                    $Group
                );
                break;
            case 'del_group':
                $ids = (array)$_GET['id'];
                if (in_array('all', $ids, true)) {
                    $items = User::getSet();
                } else {
                    $items = array_map(function ($x) {
                        return new User((int)$x);
                    }, $ids);
                    $items = array_values($items);
                }
                $Group = new Group(
                    isset($this->nav['gid']) ?
                    (int)$this->nav['gid'] :
                    0
                );
                StdSub::deassociate(
                    $items,
                    $this->url . '&id=' . (int)$Group->id,
                    true,
                    $Group->id,
                    $Group
                );
                break;
            default:
                $this->showlist();
                break;
        }
    }


    /**
     * Список пользователей и групп
     */
    protected function showlist()
    {
        $Group = new Group($this->id);
        foreach (['sort', 'order'] as $key) {
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


    /**
     * Редактирование пользователя
     */
    protected function edit()
    {
        $Item = new User((int)$this->id);
        $Item->visit();
        $Form = new EditUserForm(['Item' => $Item, 'view' => $this->view]);
        $activeBillingTypeId = null;
        if ($_POST['billing_transaction_amount'] ?? false) {
            foreach ((array)$_POST['billing_transaction_amount'] as $billingTypeId => $billingAmount) {
                if ((float)$billingAmount &&
                    trim($_POST['billing_transaction_name'][$billingTypeId])
                ) {
                    $billingType = new BillingType($billingTypeId);
                    $billingType->transact(
                        $Item,
                        (float)$billingAmount,
                        trim($_POST['billing_transaction_name'][$billingTypeId])
                    );
                    $activeBillingTypeId = $billingTypeId;
                }
            }
            if ($activeBillingTypeId) {
                new Redirector('history:back#billing' . $activeBillingTypeId);
            }
        }
        $OUT = $Form->process();
        $this->view->edit_user($OUT, 'getUserContextMenu');
    }


    /**
     * Редактирование группы
     */
    protected function edit_group()
    {
        $Item = new Group((int)$this->id);
        if (!$Item->id) {
            $Item->pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
        }
        $Form = new EditGroupForm(['Item' => $Item]);
        $this->view->edit_group($Form->process(), 'getGroupContextMenu');
    }
}
