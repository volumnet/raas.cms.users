<?php
/**
 * Форма редактирования пользователя
 */
namespace RAAS\CMS\Users;

use RAAS\Application;
use RAAS\Controller_Frontend;
use RAAS\Field as RAASField;
use RAAS\Form as RAASForm;
use RAAS\FormTab;
use RAAS\Option;
use RAAS\CMS\CMSAccess;
use RAAS\CMS\Group;
use RAAS\CMS\Package;
use RAAS\CMS\User;
use RAAS\CMS\Shop\Order;
use RAAS\CMS\Shop\View_Web as ShopViewWeb;

/**
 * Класс формы редактирования пользователя
 * @property-read ViewSub_Users $view Представление
 */
class EditUserForm extends RAASForm
{
    protected $_view;

    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Users::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = [])
    {
        $view = $this->view;
        $t = $this;
        unset($params['view']);
        $item = isset($params['Item']) ? $params['Item'] : null;

        $defaultParams = [
            'Item' => $item,
            'parentUrl' => $this->url,
            'caption' => $item->id
                      ?  ($item->full_name ? ($item->full_name . ' (' . $item->login . ')') : $item->login)
                      :  $this->view->_('CREATING_USER'),
            'children' => [],
            'template' => 'edit',
            'export' => function ($Form) use ($t) {
                $oldVis = (int)$Form->Item->vis;
                $Form->exportDefault();
                $Form->Item->new = 0;
                $newVis = (int)$Form->Item->vis;
                $an = Module::i()->registryGet('automatic_notification');
                if ($an && $Form->Item->email) {
                    if (!$oldVis &&
                        $newVis &&
                        in_array(
                            $an,
                            [
                                Module::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION,
                                Module::AUTOMATIC_NOTIFICATION_BOTH
                            ]
                        )
                    ) {
                        // Уведомление об активации
                        Module::i()->sendNotification($Form->Item);
                    } elseif ($oldVis &&
                        !$newVis &&
                        ($an == Module::AUTOMATIC_NOTIFICATION_BOTH)
                    ) {
                        // Уведомление о блокировке
                        Module::i()->sendNotification($Form->Item);
                    }
                }
            },
        ];

        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
        $this->children['common'] = $this->getCommonTab();
        $this->children['groups'] = $this->getGroupsTab();
        if ($item->id) {
            if (class_exists('RAAS\CMS\Shop\Order')) {
                $this->children['orders'] = $this->getOrdersTab($this->Item);
            }
            $billingTypes = BillingType::getSet();
            foreach ($billingTypes as $billingType) {
                $this->children['billing' . (int)$billingType->id] = $this->getBillingTab(
                    $this->Item,
                    $billingType
                );
            }
        }
    }


    /**
     * Получает основную вкладку редактирования пользователя
     * @return FormTab
     */
    protected function getCommonTab()
    {
        $tabChildren = [];
        $t = $this;
        $item = $this->Item;
        $CONTENT = [];
        $CONTENT['languages'] = [];
        foreach ($this->view->availableLanguages as $key => $val) {
            $CONTENT['languages'][] = ['value' => $key, 'caption' => $val];
        }

        // Логин
        $field = new RAASField([
            'name' => 'login',
            'caption' => $this->view->_('LOGIN'),
            'required' => 'required'
        ]);
        $field->required = 'required';
        $field->check = function ($field) use ($t) {
            $localError = $field->getErrors();
            if (!$localError) {
                if ($field->Form->Item->checkLoginExists($_POST[$field->name])) {
                    $localError[] = [
                        'name' => 'INVALID',
                        'value' => $field->name,
                        'description' => $t->view->_('ERR_LOGIN_EXISTS')
                    ];
                }
            }
            return $localError;
        };
        $tabChildren['login'] = $field;

        // Пароль
        $field = new RAASField([
            'type' => 'password',
            'name' => 'password',
            'caption' => $this->view->_('PASSWORD'),
            'confirm' => true,
            'export' => function ($field) use ($t) {
                if ($_POST[$field->name]) {
                    $field->Form->Item->password_md5 = Application::i()->md5It(
                        trim($_POST[$field->name])
                    );
                }
            }
        ]);
        if (!$item->id) {
            $field->required = 'required';
        }
        $tabChildren['password'] = $field;

        // E-mail
        $field = new RAASField([
            'type' => 'email',
            'name' => 'email',
            'caption' => $this->view->_('EMAIL')
        ]);
        $field->check = function ($field) use ($t) {
            $localError = $field->getErrors();
            if (!$localError) {
                if ($field->Form->Item->checkEmailExists($_POST[$field->name])) {
                    $localError[] = [
                        'name' => 'INVALID',
                        'value' => $field->name,
                        'description' => $t->view->_('ERR_EMAIL_EXISTS')
                    ];
                }
            }
            return $localError;
        };
        $tabChildren['email'] = $field;

        // Активирован
        $field = new RAASField([
            'type' => 'checkbox',
            'name' => 'vis',
            'caption' => $this->view->_('ACTIVATED'),
            'template' => 'edit.vis.tmp.php'
        ]);
        $tabChildren['vis'] = $field;

        // Язык
        $field = new RAASField([
            'type' => 'select',
            'name' => 'lang',
            'caption' => $this->view->_('LANGUAGE'),
            'children' => $CONTENT['languages'],
            'default' => $this->view->language
        ]);
        $tabChildren['lang'] = $field;


        // Кастомные поля
        foreach ($item->fields as $row) {
            $tabChildren[$row->urn] = $row->Field;
        }

        // Социальные сети
        $tabChildren['social'] = new RAASField([
            'type' => 'text',
            'name' => 'social',
            'multiple' => true,
            'caption' => $this->view->_('SOCIAL_NETWORKS'),
            'export' => function ($field) use ($t) {
                $field->Form->Item->meta_social = isset($_POST[$field->name])
                                                ? (array)$_POST[$field->name]
                                                : [];
            }
        ]);

        $tab = new FormTab([
            'name' => 'common',
            'caption' => $this->view->_('EDIT_USER'),
            'children' => $tabChildren
        ]);
        return $tab;
    }


    /**
     * Получает вкладку "Группы"
     * @return FormTab
     */
    public function getGroupsTab()
    {
        $t = $this;
        $g = new Group();
        $tab = new FormTab([
            'name' => 'groups',
            'caption' => $this->view->_('GROUPS'),
            'children' => [
                'groups' => [
                    'type' => 'checkbox',
                    'name' => 'groups',
                    'multiple' => 'multiple',
                    'children' => ['Set' => $g->children],
                    'import' => function ($field) use ($t) {
                        return $field->Form->Item->groups_ids;
                    },
                    'oncommit' => function ($field) use ($t) {
                        $sqlQuery = "DELETE FROM cms_users_groups_assoc
                                      WHERE uid = ?";
                        $t->Item->_SQL()->query([
                            $sqlQuery,
                            (int)$field->Form->Item->id
                        ]);
                        $arr = [];
                        foreach ((array)$_POST[$field->name] as $val) {
                            if ((int)$val) {
                                $arr[] = [
                                    'uid' => (int)$field->Form->Item->id,
                                    'gid' => (int)$val
                                ];
                            }
                        }
                        $t->Item->_SQL()->add("cms_users_groups_assoc", $arr);
                        CMSAccess::refreshMaterialsAccessCache($t->Item);
                    }
                ]
            ],
        ]);
        return $tab;
    }


    /**
     * Получает вкладку "Заказы"
     * @param User $user Пользователь
     * @return FormTab
     */
    private function getOrdersTab(User $user)
    {
        $ordersTable = new UserOrdersTable([
            'Item' => $user,
            'Set' => Order::getSet([
                'select' => [
                    "(
                        SELECT SUM(tOG.amount)
                          FROM " . Order::_dbprefix() . "cms_shop_orders_goods
                            AS tOG
                         WHERE tOG.order_id = Order.id
                     ) AS c",
                    "(
                        SELECT SUM(tOG.realprice * tOG.amount)
                          FROM " . Order::_dbprefix() . "cms_shop_orders_goods
                            AS tOG
                         WHERE tOG.order_id = Order.id
                     ) AS total_sum",
                ],
                'where' => "uid = " . (int)$user->id,
                'orderBy' => "post_date DESC",
            ]),
            'columns' => [],
        ]);
        $tab = new FormTab([
            'name' => 'orders',
            'caption' => ShopViewWeb::i()->_('ORDERS'),
            'meta' => ['Table' => $ordersTable],
            'template' => 'user_orders.inc.php'
        ]);
        return $tab;
    }


    /**
     * Получает вкладку счета биллинга
     * @param User $user Пользователь
     * @param BillingType $billingType Тип биллинга
     * @return FormTab
     */
    protected function getBillingTab(User $user, BillingType $billingType)
    {
        $billingTable = new BillingTransactionsTable([
            'Set' => BillingTransaction::getSet([
                'where' => [
                    'uid = ' . (int)$user->id,
                    'billing_type_id = ' . (int)$billingType->id,
                ],
                'orderBy' => 'post_date DESC'
            ])
        ]);
        $tab = new FormTab([
            'name' => 'billing' . (int)$billingType->id,
            'caption' => $billingType->name,
            'meta' => [
                'table' => $billingTable,
                'billingType' => $billingType,
                'user' => $user,
            ],
            'template' => 'billing_tab.inc.php',
        ]);
        return $tab;
    }

}
