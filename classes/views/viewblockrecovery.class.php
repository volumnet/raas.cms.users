<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\ViewBlock;
use \RAAS\CMS\Page;
use \RAAS\CMS\Location;

class ViewBlockRecovery extends ViewBlock
{
    const blockListItemClass = 'cms-block-users-recovery';

    public function renderBlockTypeName()
    {
        return $this->view->_('BLOCK_LEGEND_RECOVERY');
    }


    public function locationContextMenu(Page $Page, Location $Location)
    {
        return parent::locationContextMenu($Page, $Location, $this->view->_('ADD_RECOVERY_BLOCK'), 'Users\\Block_Recovery');
    }
}
