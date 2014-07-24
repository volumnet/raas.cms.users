<?php 
namespace RAAS\CMS\Users;
?>
<div class="feedback">
  <form class="form-horizontal" method="post" enctype="multipart/form-data">
    <div data-role="notifications" <?php echo ($success || $localError) ? '' : 'style="display: none"'?>>
      <div class="alert alert-danger" <?php echo ($localError) ? '' : 'style="display: none"'?>>
        <ul>
          <?php foreach ((array)$localError as $key => $val) { ?>
              <li><?php echo htmlspecialchars($val)?></li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <div data-role="feedback-form" <?php echo $success ? 'style="display: none"' : ''?>>
      <div class="form-group">
        <label for="login" class="control-label col-sm-2"><?php echo LOGIN?>:</label>
        <div class="col-sm-4"><input type="text" name="login" /></div>
      </div>
      <div class="form-group">
        <label for="password" class="control-label col-sm-2"><?php echo PASSWORD?>:</label>
        <div class="col-sm-4"><input type="password" name="password" /></div>
      </div>
      <?php if ($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD) || ($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER) { ?>
          <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2">
              <label class="checkbox">
                <?php if ($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD) { ?>
                    <input type="checkbox" name="save_password" value="1" /> <?php echo SAVE_PASSWORD?>
                <?php } elseif ($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER) { ?>
                    <input type="checkbox" name="foreign_computer" value="1" /> <?php echo FOREIGN_COMPUTER?>
                <?php } ?>
              </label>
            </div>
          </div>
      <?php } ?>
      <div class="form-group"><div class="col-sm-4 col-sm-offset-2"><button class="btn btn-default" type="submit"><?php echo SEND?></button></div></div>
    </div>
  </form>
</div>
