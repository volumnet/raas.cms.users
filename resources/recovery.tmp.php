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
    <div class="feedback">
      <form class="form-horizontal" method="post" enctype="multipart/form-data">
        <div data-role="notifications" <?php echo ($success || $localError) ? '' : 'style="display: none"'?>>
          <div class="alert alert-success" <?php echo ($success) ? '' : 'style="display: none"'?>>
            <?php echo $proceed ? YOUR_PASSWORD_WAS_SUCCESSFULLY_CHANGED : RECOVERY_KEY_WAS_SENT?>
          </div>
          <div class="alert alert-danger" <?php echo ($localError) ? '' : 'style="display: none"'?>>
            <ul>
              <?php foreach ((array)$localError as $key => $val) { ?>
                  <li><?php echo htmlspecialchars($val)?></li>
              <?php } ?>
            </ul>
          </div>
        </div>
        <div data-role="feedback-form" <?php echo $success ? 'style="display: none"' : ''?>>
          <?php if ($proceed) { ?>
              <?php if (!$key_is_invalid) { ?>
                  <div class="form-group">
                    <label for="password" class="control-label col-sm-3 col-md-2"><?php echo PASSWORD?></label>
                    <div class="col-sm-9 col-md-4"><input type="password" class="form-control" name="password" /></div>
                  </div>
                  <div class="form-group">
                    <label for="password" class="control-label col-sm-3 col-md-2"><?php echo PASSWORD_CONFIRM?></label>
                    <div class="col-sm-9 col-md-4"><input type="password" class="form-control" name="password@confirm" /></div>
                  </div>
                  <div class="form-group"><div class="col-sm-9 col-md-4 col-sm-offset-3 col-md-offset-2"><button class="btn btn-default" type="submit"><?php echo CHANGE?></button></div></div>
              <?php } ?>
          <?php } else { ?>
              <div class="form-group">
                <label for="password" class="control-label col-sm-3"><?php echo ENTER_LOGIN_OR_EMAIL?></label>
                <div class="col-sm-9 col-md-4"><input type="text" name="login" class="form-control" /></div>
              </div>
              <div class="form-group"><div class="col-sm-9 col-md-4 col-sm-offset-3"><button class="btn btn-default" type="submit"><?php echo SEND?></button></div></div>
          <?php } ?>
        </div>
      </form>
    </div>
<?php } ?>