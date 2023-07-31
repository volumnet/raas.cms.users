<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\ViewBlock;
use \RAAS\CMS\Page;
use \RAAS\CMS\Location;

class ViewBlockRecovery extends ViewBlock
{
    const BLOCK_LIST_ITEM_CLASS = 'cms-block_users-recovery';

    public function renderBlockTypeName()
    {
        return $this->view->_('BLOCK_LEGEND_RECOVERY');
    }


    public function locationContextMenu(Page $Page, Location $Location)
    {
        return parent::locationContextMenu($Page, $Location, $this->view->_('ADD_RECOVERY_BLOCK'), 'Users\\Block_Recovery');
    }
}
