<?php
/**
 * Вход в систему
 * @param Block_LogIn $Block Текущий блок
 * @param Page $Page Текущая страница
 * @param User|null $User Текущий пользователь
 * @param bool $success Успешная активация
 * @param array<string[] URN поля => string Текст ошибки> $localError
 */
namespace RAAS\CMS\Users;

use RAAS\AssetManager;
use RAAS\CMS\Package;
use RAAS\CMS\User;

if ($_POST['AJAX'] == (int)$Block->id) {
    $result = [];
    if ($success[(int)$Block->id]) {
        $result['success'] = true;
    }
    if ($localError) {
        $result['localError'] = $localError;
    }
    if ($social) {
        $result['social'] = trim($social);
        $result['socialNetwork'] = trim($socialNetwork);
        if (!$localError) {
            $result['success'] = true;
        }
    }
    if ($User) {
        $result['User'] = $User->getArrayCopy();
        $result['User']['last_name'] = $User->last_name;
        $result['User']['first_name'] = $User->first_name;
        $result['User']['full_name'] = $User->full_name;
        if (!$localError && $User->id) {
            $result['success'] = true;
        }
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($result);
    exit;
} else {
    switch ($Block->password_save_type) {
        case Block_LogIn::SAVE_PASSWORD_NONE:
            $passwordSaveType = 0;
            break;
        case Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD:
            $passwordSaveType = 1;
            break;
        case Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER:
            $passwordSaveType = -1;
            break;
    }
    ?>
    <login-form :block-id="<?php echo (int)$Block->id?>" :initial-errors="<?php echo htmlspecialchars(json_encode((object)$localError))?>" :email-as-login="<?php echo $Block->email_as_login ? 'true' : 'false'?>" :password-save-type="<?php echo (int)$passwordSaveType?>" :allow-social-login="<?php echo $Block->social_login_type ? 'true' : 'false'?>" :scroll-to-errors="true"></login-form>
    <?php
    AssetManager::requestCSS('/css/login.css');
    AssetManager::requestJS('/js/login.js');
}
