<?php
namespace RAAS\CMS\Users;

$recoveryBlocks = Block_Recovery::getSet(array(
    'where' => "block_type = 'RAAS\\\\CMS\\\\Users\\\\Block_Recovery'",
    'orderBy' => 'id'
));
$recoveryPages = array();
if ($recoveryBlocks) {
    $recoveryPages = array();
    foreach ($recoveryBlocks as $recoveryBlock) {
        $recoveryPages = array_merge($recoveryPages, $recoveryBlock->pages);
    }
}
$recoveryPage = null;
$langRecoveryPages = array_filter($recoveryPages, function ($x) use ($User) {
    return $x->lang == $User->lang;
});
if ($langRecoveryPages) {
    $recoveryPage = array_shift($langRecoveryPages);
}
if (!$recoveryPage->id && $recoveryPages) {
    $recoveryPage = array_shift($recoveryPages);
}
?>
<p><?php echo GREETINGS?></p>

<?php if ($active) { ?>
    <p><?php echo YOUR_ACCOUNT_HAS_BEEN_ACTIVATED?></p>
    <p><?php echo NOW_YOU_CAN_LOG_IN_INTO_THE_SYSTEM?></p>
    <p>
      <strong><?php echo YOUR_LOGIN?>:</strong> <?php echo htmlspecialchars($User->login)?><br />
      <?php
      $recoveryUrl = ('http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'])
                   . ($recoveryPage->id ? $recoveryPage->url : '/recovery/');
      echo sprintf(YOUR_PASSWORD_ISNT_STORED_IN_DATABASE_FOR_SECURITY_REASON, htmlspecialchars($recoveryUrl));
      ?>
    </p>
<?php } else { ?>
    <p><?php echo YOUR_ACCOUNT_HAS_BEEN_BLOCKED?></p>
    <p><?php echo PLEASE_CONTACT_SITE_ADMINISTRATOR_TO_ASK_REASON?></p>
<?php } ?>

<p>--</p>
<p>
  <?php echo WITH_RESPECT?>,<br />
  <?php echo ADMINISTRATION_OF_SITE?> <a href="http<?php echo ($_SERVER['HTTPS'] == 'on' ? 's' : '')?>://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'])?>"><?php echo htmlspecialchars($_SERVER['HTTP_HOST'])?></a>
</p>