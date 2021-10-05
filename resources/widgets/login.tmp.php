<?php
/**
 * Виджет входа в систему
 * @param Block_LogIn $Block Текущий блок
 * @param Page $Page Текущая страница
 * @param User|null $User Текущий пользователь
 * @param bool $success Успешная активация
 * @param array<string[] URN поля => string Текст ошибки> $localError
 */
namespace RAAS\CMS\Users;

use RAAS\User;

if ($_POST['AJAX']) {
    $result = [];
    if ($success[(int)$Block->id]) {
        $result['success'] = 1;
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
    if ($User) {
        $result['User'] = $User->getArrayCopy();
        $result['User']['last_name'] = $User->last_name;
        $result['User']['first_name'] = $User->first_name;
        $result['User']['full_name'] = $User->full_name;
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($result);
    exit;
} else {
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
            <label for="login_<?php echo (int)$Block->id?>" class="control-label col-sm-3 col-md-2">
              <?php echo LOGIN?>:
            </label>
            <div class="col-sm-9 col-md-4">
              <input type="text" class="form-control" name="login" id="login_<?php echo (int)$Block->id?>" />
            </div>
          </div>
          <div class="form-group">
            <label for="password_<?php echo (int)$Block->id?>" class="control-label col-sm-3 col-md-2">
              <?php echo PASSWORD?>:
            </label>
            <div class="col-sm-9 col-md-4">
              <input type="password" class="form-control" name="password" id="password_<?php echo (int)$Block->id?>" />
            </div>
          </div>
          <?php if (($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD) || ($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER)) { ?>
              <div class="form-group">
                <div class="col-sm-9 col-md-4 col-sm-offset-3 col-md-offset-2">
                  <label>
                    <?php if ($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_SAVE_PASSWORD) { ?>
                        <input type="checkbox" name="save_password" value="1" />
                        <?php echo SAVE_PASSWORD?>
                    <?php } elseif ($config['password_save_type'] == Block_LogIn::SAVE_PASSWORD_FOREIGN_COMPUTER) { ?>
                        <input type="checkbox" name="foreign_computer" value="1" />
                        <?php echo FOREIGN_COMPUTER?>
                    <?php } ?>
                  </label>
                </div>
              </div>
          <?php } ?>
          <div class="form-group">
            <label class="control-label col-sm-3 col-md-2">&nbsp;</label>
            <div class="col-sm-9 col-md-4">
              <a href="/register/">Зарегистрировать учетную запись</a>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-3 col-md-2">&nbsp;</label>
            <div class="col-sm-9 col-md-4">
              <a href="/recovery/"><?php echo LOST_PASSWORD?></a>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-9 col-md-4 col-sm-offset-3 col-md-offset-2">
              <button class="btn btn-primary" type="submit">
                <?php echo DO_LOGIN?>
              </button>
            </div>
          </div>
          <?php if ($config['social_login_type']) { ?>
              <div class="form-group">
                <label class="control-label col-sm-3 col-md-2">&nbsp;</label>
                <div class="col-sm-9 col-md-10" style="margin-top: 30px">
                  <div class="h5">Войти через социальные сети</div>
                  <div id="uLogin" data-ulogin="display=panel;optional=first_name,last_name,phone,email,sex,nickname,bdate,city,country;providers=vkontakte,facebook,twitter,google,yandex,odnoklassniki,mailru;hidden=livejournal,youtube,webmoney;redirect_uri=<?php echo urlencode('http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])?>"></div>
                  <script>
                  jQuery(document).ready(function($) {
                      window.setTimeout(function () {
                          $('body').append('<script src="//ulogin.ru/js/ulogin.js"><' + '/script>');
                      }, 100);
                  });
                  </script>
                </div>
              </div>
          <?php } ?>
        </div>
      </form>
    </div>
<?php } ?>
