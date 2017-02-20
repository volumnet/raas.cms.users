<?php
namespace RAAS\CMS\Users;

use \RAAS\Controller_Frontend as RAASController_Frontend;
use \RAAS\CMS\Auth;
use \RAAS\CMS\User as CMSUser;
use \RAAS\CMS\ULogin;

$checkRedirect = function ($referer) {
    if ($_POST['AJAX']) {
        return true;
    } elseif ($referer) {
        header('Location: ' . $referer);
        exit;
    } else {
        header('Location: /');
        exit;
    }
};

$OUT = array();
$Item = $User = RAASController_Frontend::i()->user;
$localError = array();
$a = new Auth($User);
if ($_GET['logout']) {
    $a->logout();
    $OUT['success'] = $checkRedirect();
} elseif ($User->id) {
    $OUT['success'] = $checkRedirect();
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['token']) && $config['social_login_type']) {
            if ($temp = ULogin::getProfile($_POST['token'])) {
                if ($a->loginBySocialNetwork($temp->profile)) {
                    $OUT['success'] = $checkRedirect();
                } elseif ($config['social_login_type'] == Block_LogIn::SOCIAL_LOGIN_QUICK_REGISTER) {
                    $User = new CMSUser();
                    $User->vis = 1;
                    $User->meta_social = $temp->profile;
                    $User->commit();
                    foreach (array('last_name', 'first_name', 'full_name') as $key) {
                        if (isset($User->fields[$key]) && ($row = $User->fields[$key])) {
                            $row->deleteValues();
                            $row->addValue($temp->$key);
                        }
                    }
                    $a = new Auth($User);
                    $a->setSession();
                    $OUT['success'] = $checkRedirect();
                } else {
                    $localError[] = ERR_USER_WITH_THIS_SOCIAL_NETWORK_IS_NOT_FOUND;
                }
            } else {
                $localError[] = ERR_CANT_CONNECT_TO_SOCIAL_NETWORK;
            }
        } else {
            if (!isset($_POST['login'])) {
                $localError['password'] = LOGIN_REQUIRED;
            } elseif (!isset($_POST['password'])) {
                $localError['password'] = PASSWORD_REQUIRED;
            } else {
                $savePassword = (($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD) && isset($_POST['save_password']))
                             || (($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER) && !isset($_POST['foreign_computer']));
                $val = $a->login(trim($_POST['login']), $_POST['password'], $savePassword);
                if ($val === -1) {
                    $localError[] = YOUR_ACCOUNT_IS_BLOCKED;
                } elseif ($val) {
                    $checkRedirect($_SERVER['HTTP_REFERER'] ?: ($_POST['HTTP_REFERER'] ?: $_GET['HTTP_REFERER']));
                } else {
                    $localError[] = INVALID_LOGIN_OR_PASSWORD;
                }
            }
        }
    }
}
$OUT['localError'] = $localError;
$OUT['User'] = $User;

return $OUT;
