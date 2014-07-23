<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\Block;

class Block_Activation extends Block
{
    public function commit()
    {
        if (!$this->name) {
            $this->name = Module::i()->view->_('ACTIVATION');
        }
        parent::commit();
    }
}
