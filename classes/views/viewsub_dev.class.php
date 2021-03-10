<?php
/**
 * Представление раздела "Разработка"
 */
namespace RAAS\CMS\Users;

use RAAS\Abstract_Sub_View as RAASAbstractSubView;
use RAAS\CMS\User_Field;

/**
 * Класс представления раздела "Разработка"
 */
class ViewSub_Dev extends RAASAbstractSubView
{
    protected static $instance;

    /**
     * Список полей пользователей
     * @param [
     *            'Set' => array<User_Field> Список полей
     *        ] $in Входные данные
     */
    public function fields(array $in = [])
    {
        $in['Table'] = new FieldsTable(array_merge($in, [
            'view' => $this,
            'editAction' => 'edit_field',
            'ctxMenu' => 'getFieldContextMenu'
        ]));
        $this->assignVars($in);
        $this->title = $this->_('USERS_FIELDS');
        $this->path[] = [
            'name' => $this->_('DEVELOPMENT'),
            'href' => $this->url
        ];
        $this->contextmenu = [[
            'name' => $this->_('CREATE_FIELD'),
            'href' => $this->url . '&action=edit_field',
            'icon' => 'plus'
        ]];
        $this->template = $in['Table']->template;
    }


    /**
     * Редактирование поля пользователей
     * @param [
     *            'Item' => User_Field Поле для редактирования,
     *            'meta' => [
     *                'parentUrl' => string URL родительской страницы
     *            ],
     *            'localError' =>? array<[
     *                'name' => string Тип ошибки,
     *                'value' => string URN поля, к которому относится ошибка,
     *                'description' => string Описание ошибки,
     *            ]> Ошибки,
     *            'Form' => EditFieldForm Форма редактирования,
     *        ] $in Входные данные
     */
    public function edit_field(array $in = [])
    {
        $this->js[] = Module::i()->parent->view->publicURL
                    . '/dev_edit_field.js';
        $this->path[] = [
            'name' => $this->_('DEVELOPMENT'),
            'href' => $this->url
        ];
        $this->path[] = [
            'name' => $this->_('USERS_FIELDS'),
            'href' => $this->url . '&action=fields'
        ];
        $this->stdView->stdEdit($in, 'getFieldContextMenu');
    }


    /**
     * Список типов биллинга
     * @param [
     *            'Set' => array<BillingType> Список типов биллинга
     *        ] $in Входные данные
     */
    public function billingTypes(array $in = [])
    {
        $in['Table'] = new BillingTypesTable(array_merge($in, [
            'view' => $this,
            'editAction' => 'edit_billing_type',
            'ctxMenu' => 'getBillingTypeContextMenu'
        ]));
        $this->assignVars($in);
        $this->title = $this->_('BILLING_TYPES');
        $this->path[] = [
            'name' => $this->_('DEVELOPMENT'),
            'href' => $this->url
        ];
        $this->contextmenu = [[
            'name' => $this->_('CREATE_BILLING_TYPE'),
            'href' => $this->url . '&action=edit_billing_type',
            'icon' => 'plus'
        ]];
        $this->template = $in['Table']->template;
    }


    /**
     * Редактирование типа биллинга
     * @param [
     *            'Item' => BillingType Тип биллинга для редактирования,
     *            'localError' =>? array<[
     *                'name' => string Тип ошибки,
     *                'value' => string URN поля, к которому относится ошибка,
     *                'description' => string Описание ошибки,
     *            ]> Ошибки,
     *            'Form' => EditBillingTypeForm Форма редактирования,
     *        ] $in Входные данные
     */
    public function editBillingType(array $in = [])
    {
        $this->assignVars($in);
        $this->path[] = [
            'name' => $this->_('DEVELOPMENT'),
            'href' => $this->url,
        ];
        $this->path[] = [
            'name' => $this->_('BILLING_TYPES'),
            'href' => $this->url . '&action=billing_types',
        ];
        $this->title = $in['Form']->caption;
        $this->template = $in['Form']->template;
        $this->contextmenu = $this->getBillingTypeContextMenu($in['Item']);
    }


    /**
     * Меню разработки (левое)
     * @return array<[
     *             'href' ?=> string Ссылка,
     *             'name' => string Заголовок пункта
     *             'active' ?=> bool Пункт меню активен,
     *             'class' ?=> string Класс пункта меню,
     *             'submenu' => *рекурсивно*,
     *         ]>
     */
    public function devMenu()
    {
        $submenu = [];
        $submenu[] = [
            'href' => $this->url . '&action=fields',
            'name' => $this->_('USERS_FIELDS'),
            'active' => (
                in_array($this->action, ['fields', 'edit_field']) &&
                !$this->moduleName
            )
        ];
        return $submenu;
    }


    /**
     * Возвращает контекстное меню поля пользователей
     * @param User_Field $field Поле для получения меню
     * @param int $i Порядок поля в списке
     * @param int $c Количество полей
     * @return array<[
     *             'href' ?=> string Ссылка,
     *             'name' => string Заголовок пункта
     *             'icon' ?=> string Наименование иконки,
     *             'title' ?=> string Всплывающая подсказка
     *             'onclick' ?=> string JavaScript-команда при клике,
     *         ]>
     */
    public function getFieldContextMenu(User_Field $field, $i = 0, $c = 0)
    {
        $arr = [];
        if ($field->id) {
            $arr[] = [
                'name' => $field->vis
                       ?  $this->_('VISIBLE')
                       :  '<span class="muted">' . $this->_('INVISIBLE') . '</span>',
                'href' => $this->url . '&action=chvis_field&id='
                       .  (int)$field->id . '&back=1',
                'icon' => $field->vis ? 'ok' : '',
                'title' => $this->_($field->vis ? 'HIDE' : 'SHOW')
            ];
            $arr[] = [
                'name' => $this->_('SHOW_IN_TABLE'),
                'href' => $this->url . '&action=show_in_table_field&id='
                       .  (int)$field->id . '&back=1',
                'icon' => $field->show_in_table ? 'ok' : '',
            ];
            $arr[] = [
                'name' => $this->_('REQUIRED'),
                'href' => $this->url . '&action=required_field&id='
                       .  (int)$field->id . '&back=1',
                'icon' => $field->required ? 'ok' : '',
            ];
        }
        $arr = array_merge(
            $arr,
            $this->stdView->stdContextMenu(
                $field,
                $i,
                $c,
                'edit_field',
                'fields',
                'delete_field'
            )
        );
        return $arr;
    }


    /**
     * Возвращает контекстное меню списка полей пользователей
     * @return array<[
     *             'href' ?=> string Ссылка,
     *             'name' => string Заголовок пункта
     *             'icon' ?=> string Наименование иконки,
     *             'title' ?=> string Всплывающая подсказка
     *             'onclick' ?=> string JavaScript-команда при клике,
     *         ]>
     */
    public function getAllFieldsContextMenu()
    {
        $arr = [];
        $arr[] = [
            'name' => $this->_('SHOW'),
            'href' => $this->url . '&action=vis_field&back=1',
            'icon' => 'eye-open',
            'title' => $this->_('SHOW')
        ];
        $arr[] = [
            'name' => $this->_('HIDE'),
            'href' => $this->url . '&action=invis_field&back=1',
            'icon' => 'eye-close',
            'title' => $this->_('HIDE')
        ];
        $arr[] = [
            'name' => $this->_('SHOW_IN_TABLE'),
            'href' => $this->url . '&action=show_in_table_field&back=1',
            'icon' => 'align-justify',
        ];
        $arr[] = [
            'name' => $this->_('REQUIRED'),
            'href' => $this->url . '&action=required_field&back=1',
            'icon' => 'asterisk',
        ];
        $arr[] = [
            'name' => $this->_('DELETE'),
            'href' => $this->url . '&action=delete_field&back=1',
            'icon' => 'remove',
            'onclick' => 'return confirm(\''
                      .     $this->_('DELETE_MULTIPLE_TEXT')
                      .  '\')'
        ];
        return $arr;
    }


    /**
     * Возвращает контекстное меню типа биллинга
     * @param BillingType $billingType Тип биллинга
     * @return array<[
     *             'href' ?=> string Ссылка,
     *             'name' => string Заголовок пункта
     *             'icon' ?=> string Наименование иконки,
     *             'title' ?=> string Всплывающая подсказка
     *             'onclick' ?=> string JavaScript-команда при клике,
     *         ]>
     */
    public function getBillingTypeContextMenu(BillingType $billingType)
    {
        $arr = array();
        if ($billingType->id) {
            if ($this->action != 'edit_billing_type') {
                $arr[] = [
                    'href' => $this->url . '&action=edit_billing_type&id='
                           .  (int)$billingType->id,
                    'name' => $this->_('EDIT'),
                    'icon' => 'edit'
                ];
            }
            $arr[] = [
                'href' => $this->url . '&action=delete_billing_type&id='
                       .  (int)$billingType->id
                       .  (($this->action == 'billing_types') ? '&back=1' : ''),
                'name' => $this->_('DELETE'),
                'icon' => 'remove',
                'onclick' => 'return confirm(\''
                          .     $this->_('DELETE_TEXT')
                          .  '\')'
            ];
        }
        return $arr;
    }
}
