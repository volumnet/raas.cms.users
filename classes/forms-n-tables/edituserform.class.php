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
        $Item = isset($params['Item']) ? $params['Item'] : null;

        $defaultParams = [
            'Item' => $Item,
            'parentUrl' => $this->url,
            'caption' => $Item->id
                      ?  $this->view->_('EDITING_USER')
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


    /**
     * Получает основную вкладку редактирования пользователя
     * @return FormTab
     */
    private function getCommonTab()
    {
        $tabChildren = [];
        $t = $this;
        $Item = $this->Item;
        $CONTENT = [];
        $CONTENT['languages'] = [];
        foreach ($this->view->availableLanguages as $key => $val) {
            $CONTENT['languages'][] = ['value' => $key, 'caption' => $val];
        }

        // Логин
        $Field = new RAASField([
            'name' => 'login',
            'caption' => $this->view->_('LOGIN'),
            'required' => 'required'
        ]);
        $Field->required = 'required';
        $Field->check = function ($Field) use ($t) {
            $localError = $Field->getErrors();
            if (!$localError) {
                if ($Field->Form->Item->checkLoginExists($_POST[$Field->name])) {
                    $localError[] = [
                        'name' => 'INVALID',
                        'value' => $Field->name,
                        'description' => $t->view->_('ERR_LOGIN_EXISTS')
                    ];
                }
            }
            return $localError;
        };
        $tabChildren['login'] = $Field;

        // Пароль
        $Field = new RAASField([
            'type' => 'password',
            'name' => 'password',
            'caption' => $this->view->_('PASSWORD'),
            'confirm' => true,
            'export' => function ($Field) use ($t) {
                if ($_POST[$Field->name]) {
                    $Field->Form->Item->password_md5 = Application::i()->md5It(
                        trim($_POST[$Field->name])
                    );
                }
            }
        ]);
        if (!$Item->id) {
            $Field->required = 'required';
        }
        $tabChildren['password'] = $Field;

        // E-mail
        $Field = new RAASField([
            'type' => 'email',
            'name' => 'email',
            'caption' => $this->view->_('EMAIL')
        ]);
        $Field->check = function ($Field) use ($t) {
            $localError = $Field->getErrors();
            if (!$localError) {
                if ($Field->Form->Item->checkEmailExists($_POST[$Field->name])) {
                    $localError[] = [
                        'name' => 'INVALID',
                        'value' => $Field->name,
                        'description' => $t->view->_('ERR_EMAIL_EXISTS')
                    ];
                }
            }
            return $localError;
        };
        $tabChildren['email'] = $Field;

        // Активирован
        $Field = new RAASField([
            'type' => 'checkbox',
            'name' => 'vis',
            'caption' => $this->view->_('ACTIVATED'),
            'template' => 'edit.vis.tmp.php'
        ]);
        $tabChildren['vis'] = $Field;

        // Язык
        $Field = new RAASField([
            'type' => 'select',
            'name' => 'lang',
            'caption' => $this->view->_('LANGUAGE'),
            'children' => $CONTENT['languages'],
            'default' => $this->view->language
        ]);
        $tabChildren['lang'] = $Field;


        // Кастомные поля
        foreach ($Item->fields as $row) {
            $tabChildren[$row->urn] = $row->Field;
        }

        // Социальные сети
        $tabChildren['social'] = new RAASField([
            'type' => 'text',
            'name' => 'social',
            'multiple' => true,
            'caption' => $this->view->_('SOCIAL_NETWORKS'),
            'export' => function ($Field) use ($t) {
                $Field->Form->Item->meta_social = isset($_POST[$Field->name])
                                                ? (array)$_POST[$Field->name]
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
                    'import' => function ($Field) use ($t) {
                        return $Field->Form->Item->groups_ids;
                    },
                    'oncommit' => function ($Field) use ($t) {
                        $sqlQuery = "DELETE FROM cms_users_groups_assoc
                                      WHERE uid = ?";
                        $t->Item->_SQL()->query([
                            $sqlQuery,
                            (int)$Field->Form->Item->id
                        ]);
                        $arr = [];
                        foreach ((array)$_POST[$Field->name] as $val) {
                            if ((int)$val) {
                                $arr[] = [
                                    'uid' => (int)$Field->Form->Item->id,
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
