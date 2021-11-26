<?php
/**
 * Виджет регистрации
 * @param Block_Register $Block Текущий блок
 * @param Page $Page Текущая страница
 * @param bool $success Успешная активация
 * @param array<string[] URN поля => string Текст ошибки> $localError
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Feedback;
use RAAS\CMS\FormArrayFormatter;
use RAAS\CMS\Package;
use RAAS\CMS\SocialProfile;

if ($_POST['AJAX'] == (int)$Block->id) {
    $result = array();
    if ($success[(int)$Block->id]) {
        $result['success'] = true;
    }
    if ($localError) {
        $result['localError'] = $localError;
    }
    if ($social) {
        $result['social'] = trim($social);
    }
    if ($social) {
        $result['socialNetwork'] = trim($socialNetwork);
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($result);
    exit;
} else {
    $formArrayFormatter = new FormArrayFormatter($Form);
    $formArr = $formArrayFormatter->format(
        ['signature' => function ($form) use ($Block) {
            return $form->getSignature($Block);
        }],
        [
            'htmlId' => function ($field) use ($Block) {
                return $field->getHTMLId($Block);
            },
        ],
    );
    $DATA['password@confirm'] = '';
    $formData = (object)$DATA;
    foreach ($Form->fields as $fieldURN => $field) {
        if (!isset($formData->$fieldURN)) {
            $defval = $field->defval;
            if ($field->multiple) {
                $formData->$fieldURN = $defval ? [$defval] : [];
            } else {
                $formData->$fieldURN = $defval ?: '';
            }
        }
    } ?>
    <register-form :block-id="<?php echo (int)$Block->id?>" :form="<?php echo htmlspecialchars(json_encode($formArr))?>" :user="user" :activation-type="<?php echo (int)$Block->activation_type?>" :initial-form-data="<?php echo htmlspecialchars(json_encode($formData))?>" :allow-edit-social="<?php echo $Block->allow_edit_social ? 'true' : 'false'?>" :scroll-to-errors="true"></register-form>
    <?php
    Package::i()->requestCSS('/css/register.css');
    Package::i()->requestJS('/js/register.js');
}
