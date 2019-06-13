<?php
/**
 * Форма редактирования типа биллинга
 */
namespace RAAS\CMS\Users;

use RAAS\Form as RAASForm;

class EditBillingTypeForm extends RAASForm
{
    protected $_view;

    public function __get($var)
    {
        switch ($var) {
            case 'view':
                return ViewSub_Dev::i();
                break;
            default:
                return parent::__get($var);
                break;
        }
    }


    public function __construct(array $params = array())
    {
        $view = $this->view;
        $item = isset($params['Item']) ? $params['Item'] : null;
        $defaultParams = [
            'caption' => $view->_('EDIT_BILLING_TYPE'),
            'parentUrl' => Sub_Dev::i()->url . '&action=billing_types',
            'children' => [
                [
                    'name' => 'name',
                    'caption' => $view->_('NAME'),
                    'required' => 'required',
                ],
                [
                    'name' => 'urn',
                    'caption' => $view->_('URN'),
                ],
                [
                    'type' => 'textarea',
                    'name' => 'description',
                    'caption' => $view->_('DESCRIPTION'),
                ],
            ]
        ];
        $arr = array_merge($defaultParams, $params);
        parent::__construct($arr);
    }
}
