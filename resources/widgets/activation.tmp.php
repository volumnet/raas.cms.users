<?php
/**
 * Активация
 * @param Block_Activation $Block Текущий блок
 * @param Page $Page Текущая страница
 * @param bool $success Успешная активация
 * @param array $localError <pre><code>array<string[] URN поля => string Текст ошибки></code></pre>
 */
namespace RAAS\CMS\Users;

use RAAS\AssetManager;
use RAAS\CMS\Package;

if ($_POST['AJAX'] == (int)$Block->id) {
    $result = [];
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
    <activation-notification
      :success="<?php echo $success ? 'true' : 'false'?>"
      :errors="<?php echo htmlspecialchars(json_encode($localError))?>"
    ></activation-notification>
    <?php
    AssetManager::requestCSS('/css/activation.css');
    AssetManager::requestJS('/js/activation.js');
}
