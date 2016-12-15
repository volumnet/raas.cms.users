<?php
namespace RAAS\CMS\Users;

use \RAAS\Application;
use \RAAS\FormTab;
use \RAAS\Field as RAASField;
use \RAAS\Option;
use \RAAS\CMS\Package;
use \RAAS\CMS\User;
use \RAAS\CMS\Group;
use \RAAS\Controller_Frontend;
use \RAAS\CMS\CMSAccess;

class EditUserForm extends \RAAS\Form
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


    public function __construct(array $params = array())
    {
        $view = $this->view;
        $t = $this;
        unset($params['view']);
        $Item = isset($params['Item']) ? $params['Item'] : null;

        $defaultParams = array(
            'Item' => $Item,
            'parentUrl' => $this->url,
            'caption' => $Item->id ? $this->view->_('EDITING_USER') : $this->view->_('CREATING_USER'),
            'children' => array(),
            'template' => 'edit',
            'export' => function ($Form) use ($t) {
                $oldVis = (int)$Form->Item->vis;
                $Form->exportDefault();
                $Form->Item->new = 0;
                $newVis = (int)$Form->Item->vis;
                if (($an = Module::i()->registryGet('automatic_notification')) && $Form->Item->email) {
                    if (!$oldVis && $newVis && in_array($an, array(Module::AUTOMATIC_NOTIFICATION_ONLY_ACTIVATION, Module::AUTOMATIC_NOTIFICATION_BOTH))) {
                        // Уведомление об активации
                        Module::i()->sendNotification($Form->Item);
                    } elseif ($oldVis && !$newVis && ($an == Module::AUTOMATIC_NOTIFICATION_BOTH)) {
                        // Уведомление о блокировке
                        Module::i()->sendNotification($Form->Item);
                    }
                }
            },
        );

        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
        $this->children['common'] = $this->getCommonTab();
        $this->children['groups'] = $this->getGroupsTab();
    }


    private function getCommonTab()
    {
        $tabChildren = array();
        $t = $this;
        $Item = $this->Item;
        $CONTENT = array();
        $CONTENT['languages'] = array();
        foreach ($this->view->availableLanguages as $key => $val) {
            $CONTENT['languages'][] = array('value' => $key, 'caption' => $val);
        }

        // Логин
        $Field = new RAASField(array('name' => 'login', 'caption' => $this->view->_('LOGIN'), 'required' => 'required'));
        $Field->required = 'required';
        $Field->check = function ($Field) use ($t) {
            $localError = $Field->getErrors();
            if (!$localError) {
                if ($Field->Form->Item->checkLoginExists($_POST[$Field->name])) {
                    $localError[] = array('name' => 'INVALID', 'value' => $Field->name, 'description' => $t->view->_('ERR_LOGIN_EXISTS'));
                }
            }
            return $localError;
        };
        $tabChildren['login'] = $Field;

        // Пароль
        $Field = new RAASField(array(
            'type' => 'password',
            'name' => 'password',
            'caption' => $this->view->_('PASSWORD'),
            'confirm' => true,
            'export' => function ($Field) use ($t) {
                if ($_POST[$Field->name]) {
                    $Field->Form->Item->password_md5 = \RAAS\Application::i()->md5It(trim($_POST[$Field->name]));
                }
            }
        ));
        if (!$Item->id) {
            $Field->required = 'required';
        }
        $tabChildren['password'] = $Field;

        // E-mail
        $Field = new RAASField(array('type' => 'email', 'name' => 'email', 'caption' => $this->view->_('EMAIL')));
        $Field->check = function ($Field) use ($t) {
            $localError = $Field->getErrors();
            if (!$localError) {
                if ($Field->Form->Item->checkEmailExists($_POST[$Field->name])) {
                    $localError[] = array('name' => 'INVALID', 'value' => $Field->name, 'description' => $t->view->_('ERR_EMAIL_EXISTS'));
                }
            }
            return $localError;
        };
        $tabChildren['email'] = $Field;

        // Активирован
        $Field = new RAASField(array('type' => 'checkbox', 'name' => 'vis', 'caption' => $this->view->_('ACTIVATED'), 'template' => 'edit.vis.tmp.php'));
        $tabChildren['vis'] = $Field;

        // Язык
        $Field = new RAASField(array(
            'type' => 'select', 'name' => 'lang', 'caption' => $this->view->_('LANGUAGE'), 'children' => $CONTENT['languages'], 'default' => $this->view->language
        ));
        $tabChildren['lang'] = $Field;


        // Кастомные поля
        foreach ($Item->fields as $row) {
            $tabChildren[$row->urn] = $row->Field;
        }

        // Социальные сети
        $tabChildren['social'] = new RAASField(array(
            'type' => 'text',
            'name' => 'social',
            'multiple' => true,
            'caption' => $this->view->_('SOCIAL_NETWORKS'),
            'export' => function ($Field) use ($t) {
                $Field->Form->Item->meta_social = isset($_POST[$Field->name]) ? (array)$_POST[$Field->name] : array();
            }
        ));

        $tab = new FormTab(array('name' => 'common', 'caption' => $this->view->_('EDIT_USER'), 'children' => $tabChildren));
        return $tab;
    }


    public function getGroupsTab()
    {
        $t = $this;
        $g = new Group();
        $tab = new FormTab(array(
            'name' => 'groups',
            'caption' => $this->view->_('GROUPS'),
            'children' => array(
                'groups' => array(
                    'type' => 'checkbox',
                    'name' => 'groups',
                    'multiple' => 'multiple',
                    'children' => array('Set' => $g->children),
                    'import' => function ($Field) use ($t) {
                        return $Field->Form->Item->groups_ids;
                    },
                    'oncommit' => function ($Field) use ($t) {
                        $SQL_query = "DELETE FROM cms_users_groups_assoc WHERE uid = " . (int)$Field->Form->Item->id;
                        $t->Item->_SQL()->query($SQL_query);
                        $arr = array();
                        foreach ((array)$_POST[$Field->name] as $val) {
                            if ((int)$val) {
                                $arr[] = array('uid' => (int)$Field->Form->Item->id, 'gid' => (int)$val);
                            }
                        }
                        $t->Item->_SQL()->add("cms_users_groups_assoc", $arr);
                        CMSAccess::refreshMaterialsAccessCache($t->Item);
                    }
                )
            )
        ));
        return $tab;
    }
}
