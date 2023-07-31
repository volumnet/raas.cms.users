<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\ViewBlock;
use \RAAS\CMS\Page;
use \RAAS\CMS\Location;

class ViewBlockRegister extends ViewBlock
{
    const BLOCK_LIST_ITEM_CLASS = 'cms-block_users-register';

    public function renderBlockTypeName()
    {
        return $this->view->_('BLOCK_LEGEND_REGISTER');
    }


    public function locationContextMenu(Page $Page, Location $Location)
    {
        return parent::locationContextMenu($Page, $Location, $this->view->_('ADD_REGISTER_BLOCK'), 'Users\\Block_Register');
    }
}
