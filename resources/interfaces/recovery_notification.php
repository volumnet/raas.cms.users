<?php
/**
 * Уведомление о восстановлении пароля
 * @param Page $Page Текущая страница
 * @param User $User Пользователь
 */
namespace RAAS\CMS;

use RAAS\Controller_Frontend as ControllerFrontend;

$cf = ControllerFrontend::i();
$link = $Page->url . '?key=' . $User->recoveryKey;
if ($referer) {
    $link .= '&HTTP_REFERER=' . urlencode($referer);
}
?>
<p>
  <?php
  echo date(DATETIMEFORMAT) . ' ' .
      sprintf(
          YOU_HAVE_ASKED_PASSWORD_RECOVERY_ON_SITE,
          $cf->schemeHost,
          $cf->idnHost
      );
  ?>
</p>
<p>
  <?php
  echo sprintf(
      RECOVERY_LINK,
      $cf->schemeHost . $link,
      $cf->idnSchemeHost . $link
  )?>
</p>
<p>
  <?php echo IF_IT_WAS_NOT_YOU_JUST_IGNORE?>
</p>

<p>--</p>
<p>
  <?php echo WITH_RESPECT?>,<br />
  <?php echo ADMINISTRATION_OF_SITE?>
  <a href="<?php echo htmlspecialchars($cf->schemeHost)?>">
    <?php echo htmlspecialchars($cf->idnHost)?>
  </a>
</p>
