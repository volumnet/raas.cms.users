<?php 
namespace RAAS\CMS\Users;
use \RAAS\Controller_Frontend as RAASController_Frontend;
use \RAAS\CMS\User;
use \RAAS\CMS\Auth;

$OUT = array();
$Item = $User = RAASController_Frontend::i()->user;
$localError = array();
if ($User->vis) {
    $localError = ERR_ALREADY_ACTIVATED;
} elseif (($tmp_user = User::importByActivationKey(isset($_GET['key']) ? $_GET['key'] : ''))) {
    $User = $tmp_user;
    $User->vis = 1;
    $User->commit();
    $a = new Auth($User);
    $a->setSession();
    $OUT['success'] = true;
} else {
    $localError[] = CONFIRMATION_KEY_IS_INVALID;
}
$OUT['localError'] = $localError;
$OUT['User'] = $User;

return $OUT;