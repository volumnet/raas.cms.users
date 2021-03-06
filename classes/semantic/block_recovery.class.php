<?php
namespace RAAS\CMS\Users;
use \RAAS\CMS\Block;

class Block_Recovery extends Block
{
    protected static $tablename2 = 'cms_users_blocks_recovery';

    protected static $references = array(
        'Notification' => array('FK' => 'notification_id', 'classname' => 'RAAS\\CMS\\Snippet', 'cascade' => false),
        'author' => array('FK' => 'author_id', 'classname' => 'RAAS\\User', 'cascade' => false),
        'editor' => array('FK' => 'editor_id', 'classname' => 'RAAS\\User', 'cascade' => false),
    );

    public function commit()
    {
        if (!$this->name) {
            $this->name = Module::i()->view->_('PASSWORD_RECOVERY');
        }
        parent::commit();
    }


    public function getAddData()
    {
        return array(
            'id' => (int)$this->id,
            'notification_id' => (int)$this->notification_id,
        );
    }
}
