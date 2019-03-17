<?php
namespace RAAS\CMS\Users;

$link = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $Page->url . '?key=' . $User->recoveryKey;
?>
<p><?php echo date(DATETIMEFORMAT) . ' ' . sprintf(YOU_HAVE_ASKED_PASSWORD_RECOVERY_ON_SITE, $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST'])?></p>
<?php

?>
<p><?php echo sprintf(RECOVERY_LINK, $link, $link)?></p>
<p><?php echo IF_IT_WAS_NOT_YOU_JUST_IGNORE?></p>
<p>--</p>
<p>
  <?php echo WITH_RESPECT?>,<br />
  <?php echo ADMINISTRATION_OF_SITE?> <a href="http<?php echo ($_SERVER['HTTPS'] == 'on' ? 's' : '')?>://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'])?>"><?php echo htmlspecialchars($_SERVER['HTTP_HOST'])?></a>
</p>
