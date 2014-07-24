<?php
namespace \RAAS\CMS\Users;
use \RAAS\Controller_Frontend;
use \RAAS\CMS\Auth;

$OUT = array();
$Item = $User = Controller_Frontend::i()->user;
$localError = array();
$a = new Auth($User);
if ($_GET['logout']) {
    $a->logout();
    if ($_POST['AJAX']) {
        $OUT['success'] = true;
    } else {
        header('Location: /');
        exit;
    }
} elseif ($User->id) {
    if ($_POST['AJAX']) {
        $OUT['success'] = true;
    } else {
        header('Location: /');
        exit;
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['login'])) {
            $localError['password'] = 'LOGIN_REQUIRED';
        if (!isset($_POST['password'])) {
            $localError['password'] = 'PASSWORD_REQUIRED';
        } else {
            $savePassword = (($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD) && isset($_POST['save_password'])) 
                         || (($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER) && !isset($_POST['foreign_computer']));
            if ($a->login(trim($_POST['login']), $_POST['password'], $savePassword)) {
                if ($_POST['AJAX']) {
                    $OUT['success'] = true;
                } else {
                    header('Location: ' . $_SERVER['HTTP_REFERER'] ?: ($_POST['HTTP_REFERER'] ?: ($_GET['HTTP_REFERER'] ?: '/')));
                    exit;
                }
            } else {
                $localError[] = 'INVALID_LOGIN_OR_PASSWORD';
            }
        }
    }
}
$OUT['localError'] = $localError;
$OUT['User'] = $User;

return $OUT;