<?php
/**
 * Восстановление пароля
 * @param Block_Recovery $Block Текущий блок
 * @param Page $Page Текущая страница
 * @param bool $success Успешная активация
 * @param array<string[] URN поля => string Текст ошибки> $localError
 */
namespace RAAS\CMS\Users;

use RAAS\AssetManager;
use RAAS\CMS\Package;

if ($_POST['AJAX'] == (int)$Block->id) {
    $result = array();
    if ($success) {
        $result['success'] = true;
    }
    if ($localError) {
        $result['localError'] = $localError;
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($result);
    exit;
} else { ?>
    <recovery-form :block-id="<?php echo (int)$Block->id?>" :initial-errors="<?php echo htmlspecialchars(json_encode((object)$localError))?>" :proceed="<?php echo $proceed ? 'true' : 'false'?>" :key-is-invalid="<?php echo ($proceed && $key_is_invalid) ? 'true' : 'false'?>" :email-as-login="false" :scroll-to-errors="true"></register-form>
    <?php
    AssetManager::requestCSS('/css/recovery.css');
    AssetManager::requestJS('/js/recovery.js');
}
