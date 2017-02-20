<?php
namespace RAAS\CMS\Users;

if ($_POST['AJAX']) {
    $result = array();
    if ($success) {
        $result['success'] = 1;
    }
    if ($localError) {
        $result['localError'] = $localError;
    }
    ob_clean();
    echo json_encode($result);
    exit;
} else {
    ?>
    <div data-role="notifications">
      <div class="alert alert-success" <?php echo ($success) ? '' : 'style="display: none"'?>><?php echo YOUR_ACCOUNT_HAS_BEEN_SUCCESSFULLY_ACTIVATED?></div>
      <div class="alert alert-danger" <?php echo ($localError) ? '' : 'style="display: none"'?>>
        <ul>
          <?php foreach ((array)$localError as $key => $val) { ?>
              <li><?php echo htmlspecialchars($val)?></li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <?php
}
