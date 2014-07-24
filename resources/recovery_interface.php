<?php 
namespace RAAS\CMS\Users;
use \RAAS\Application;
use \RAAS\Controller_Frontend;
use \RAAS\CMS\User;
use \RAAS\CMS\Snippet;
use \RAAS\CMS\Auth;

$notify = function(User $User, array $config = array())
{
    $emails = array();
    if ($User->email) {
        $emails[] = $User->email;
    }
    if ($config['notification_id']) {
        $S = new Snippet((int)$config['notification_id']);
        $template = $S->description;
    }
    $subject = date(DATETIMEFORMAT) . ' ' . sprintf(PASSWORD_RECOVERY_ON_SITE, $_SERVER['HTTP_HOST']);
    if ($emails) {
        ob_start();
        eval('?' . '>' . $template);
        $message = ob_get_contents();
        ob_end_clean();
        \RAAS\Application::i()->sendmail($emails, $subject, $message, 'info@' . $_SERVER['HTTP_HOST'], 'RAAS.CMS');
    }
};

$OUT = array();
$Item = $User = Controller_Frontend::i()->user;
$localError = array();
if ($_GET['key'] || $User->id) {
    $OUT['proceed'] = true;
    if (!$User->id && ($tmp_user = User::importByRecoveryKey($_GET['key']))) {
        $User = $tmp_user;
        $a = new Auth($User);
        $a->setSession();
    }
    if ($User->id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['password']) || !trim($_POST['password'])) {
                $localError['password'] = 'PASSWORD_REQUIRED';
            } elseif ($_POST['password'] != $_POST['password@confirm']) {
                $localError['password'] = 'PASSWORD_DOESNT_MATCH_CONFIRM';
            } else {
                $User->password_md5 = Application::i()->md5It($_POST['password']);
                $User->commit();
                $OUT['success'] = true;
            }
        }
    } else {
        $localError[] = 'CONFIRMATION_KEY_IS_INVALID';
    }
} else {
    if (isset($_POST['login']) && trim($_POST['login'])) {
        if ($tmp_user = User::importByLoginOrEmail(trim($_POST['login']))) {
            if ($tmp_user->email) {
                $notify($tmp_user, $config);
                $OUT['sent'] = true;
            } else {
                $localError['login'] = 'NO_EMAIL_OF_THIS_USER';
            }
        } else {
            $localError['login'] = 'USER_WITH_THIS_LOGIN_IS_NOT_FOUND';
        }
    }
}
$OUT['localError'] = $localError;
$OUT['User'] = $User;

return $OUT;