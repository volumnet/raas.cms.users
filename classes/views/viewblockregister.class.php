<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\ViewBlock;
use \RAAS\CMS\Page;
use \RAAS\CMS\Location;

class ViewBlockRegister extends ViewBlock
{
    const blockListItemClass = 'cms-block-users-register';

    public function renderLegend()
    {
        return parent::renderLegend($this->view->_('BLOCK_LEGEND_REGISTER'));
    }


    public function locationContextMenu(Page $Page, Location $Location)
    {
        return parent::locationContextMenu($Page, $Location, $this->view->_('ADD_REGISTER_BLOCK'), 'Users\\Block_Register');
    }
}