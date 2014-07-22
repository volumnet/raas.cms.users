<?php
namespace RAAS\CMS\Users;
use \RAAS\Application;
use \RAAS\CMS\Snippet;
use \RAAS\CMS\Snippet_Folder;

class Webmaster extends \RAAS\CMS\Webmaster
{
    protected static $snippets = array('register' => 'REGISTRATION', 'activation' => 'ACTIVATION', 'login' => 'LOG_IN', 'recovery' => 'PASSWORD_RECOVERY');
    protected static $notifications = array('register' => 'REGISTRATION', 'activation' => 'ACTIVATION', 'recovery' => 'PASSWORD_RECOVERY');
    protected static $instance;

    public function __get($var)
    {
        switch ($var) {
            default:
                return Module::i()->__get($var);
                break;
        }
    }


    /**
     * Создаем стандартные сниппеты
     */
    public function checkStdSnippets()
    {
        foreach (self::$snippets as $urn => $name) {
            $Item = Snippet::importByURN('__RAAS_users_' . $urn . '_interface');
            if (!$Item->id) {
                $Item = new Snippet(array('pid' => Snippet_Folder::importByURN('__RAAS_interfaces')->id, 'urn' => '__RAAS_users_' . $urn . '_interface', 'locked' => 1));
            }
            $Item->name = $this->view->_($name . '_STANDARD_INTERFACE');
            $f = $this->resourcesDir . '/' . $urn . '_interface.php';
            $Item->description = file_get_contents($f);
            $Item->commit();
        }

        foreach (self::$notifications as $urn => $name) {
            $Item = Snippet::importByURN('__RAAS_users_' . $urn . '_notify');
            if (!$Item->id) {
                $Item = new Snippet(array('pid' => Snippet_Folder::importByURN('__RAAS_interfaces')->id, 'urn' => '__RAAS_users_' . $urn . '_notify', 'locked' => 1));
            }
            $Item->name = $this->view->_($name . '_NOTIFICATION');
            $f = $this->resourcesDir . '/' . $urn . '_notify.php';
            $Item->description = file_get_contents($f);
            $Item->commit();
        }
    }


    public function createCab() 
    {
        // Добавим виджеты
        $VF = Snippet_Folder::importByURN('__RAAS_views');
        foreach (self::$snippets as $urn => $name) {
            $temp = Snippet::importByURN($urn);
            if (!$temp->id) {
                $S = new Snippet();
                $S->name = $this->view->_($name);
                $S->urn = $urn;
                $S->pid = $VF->id;
                $f = $this->resourcesDir . '/' . $urn . '.tmp.php';
                $S->description = file_get_contents($f);
                $S->commit();
            }
        }

        $F = new User_Field();
        $F->pid = $MT->id;
        $F->name = $this->view->_('YOUR_NAME');
        $F->urn = 'full_name';
        $F->datatype = 'text';
        $F->show_in_table = 1;
        $F->commit();

        $F = new User_Field();
        $F->pid = $MT->id;
        $F->name = $this->view->_('PHONE');
        $F->urn = 'phone';
        $F->datatype = 'text';
        $F->show_in_table = 1;
        $F->commit();

        
        $S = Snippet::importByURN('__RAAS_users_register_notify');
        $FRM = new \RAAS\CMS\Form();
        $FRM->name = $this->view->_('REGISTRATION_FORM');
        $FRM->signature = 0;
        $FRM->antispam = 'hidden';
        $FRM->antispam_field_name = 'name';
        $FRM->interface_id = (int)$S->id;
        $FRM->commit();

        $F = new Form_Field();
        $F->pid = $FRM->id;
        $F->name = $this->view->_('YOUR_NAME');
        $F->urn = 'full_name';
        $F->required = 1;
        $F->datatype = 'text';
        $F->show_in_table = 1;
        $F->commit();

        $F = new Form_Field();
        $F->pid = $FRM->id;
        $F->name = $this->view->_('PHONE');
        $F->urn = 'phone';
        $F->datatype = 'text';
        $F->show_in_table = 1;
        $F->commit();

        $Site = array_shift(Page::getSet(array('where' => "NOT pid")));
        
        $register = $this->createPage(array('name' => $this->view->_('REGISTRATION'), 'urn' => 'register', 'cache' => 0, 'response_code' => 200), $Site);
        $activate = $this->createPage(array('name' => $this->view->_('ACTIVATION'), 'urn' => 'activate', 'cache' => 0, 'response_code' => 200), $Site);
        $login = $this->createPage(array('name' => $this->view->_('LOG_IN'), 'urn' => 'login', 'cache' => 0, 'response_code' => 200), $Site);
        $recovery = $this->createPage(array('name' => $this->view->_('PASSWORD_RECOVERY'), 'urn' => 'recovery', 'cache' => 0, 'response_code' => 200), $Site);
    }
}