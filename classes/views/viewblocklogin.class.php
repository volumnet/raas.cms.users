<?php
namespace RAAS\CMS\Users;

use RAAS\CMS\ViewBlock;
use RAAS\CMS\Page;
use RAAS\CMS\Location;

class ViewBlockLogIn extends ViewBlock
{
    const blockListItemClass = 'cms-block-users-login';

    public function renderBlockTypeName()
    {
        return $this->view->_('BLOCK_LEGEND_LOG_IN');
    }


    public function locationContextMenu(Page $Page, Location $Location)
    {
        return parent::locationContextMenu($Page, $Location, $this->view->_('ADD_LOG_IN_BLOCK'), 'Users\\Block_LogIn');
    }
}
