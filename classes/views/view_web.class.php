<?php
namespace RAAS\Cms\Users;

class View_Web extends \RAAS\Module_View_Web
{
    protected static $instance;

    public function header()
    {
        $this->css[] = $this->publicURL . '/style.css';
        $c = Module::i()->newUsers();
        $menuItem = array(array(
            'href' => '?p=' . $this->package->alias . '&m=' . $this->module->alias, 
            'name' => $this->_('__NAME') . ($c ? ' (' . $c . ')' : ''),
            'active' => ($this->moduleName == 'users') && ($this->sub != 'dev')
        ));
        $menu = $this->menu->getArrayCopy();
        array_splice($menu, -1, 0, $menuItem);
        $this->menu = new \ArrayObject($menu);
    }
}