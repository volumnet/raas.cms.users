<?php
/**
 * Блок активации
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

use RAAS\CMS\Block;

class Block_Activation extends Block
{
    const ALLOWED_INTERFACE_CLASSNAME = ActivationInterface::class;

    public function commit()
    {
        if (!$this->name) {
            $this->name = Module::i()->view->_('ACTIVATION');
        }
        parent::commit();
    }
}
