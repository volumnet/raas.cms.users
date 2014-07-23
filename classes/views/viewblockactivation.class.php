<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\ViewBlock;
use \RAAS\CMS\Page;
use \RAAS\CMS\Location;

class ViewBlockActivation extends ViewBlock
{
    const blockListItemClass = 'cms-block-users-activation';

    public function renderLegend()
    {
        return parent::renderLegend($this->view->_('BLOCK_LEGEND_ACTIVATION'));
    }


    public function locationContextMenu(Page $Page, Location $Location)
    {
        return parent::locationContextMenu($Page, $Location, $this->view->_('ADD_ACTIVATION_BLOCK'), 'Users\\Block_Activation');
    }
}