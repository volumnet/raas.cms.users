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
use RAAS\CMS\Package;
use RAAS\CMS\SocialProfile;

if ($_POST['AJAX']) {
    $result = array();
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
    ob_clean();
    echo json_encode($result);
    exit;
} else { ?>
    <a name="feedback"></a>
    <div class="feedback">
      <form class="form-horizontal" <?php /*data-role="raas-ajaxform"*/?> action="" method="post" enctype="multipart/form-data">
        <?php include Package::i()->resourcesDir . '/form2.inc.php'?>
        <div data-role="notifications" <?php echo ($success[(int)$Block->id] || $localError) ? '' : 'style="display: none"'?>>
          <div class="alert alert-success" <?php echo ($success[(int)$Block->id]) ? '' : 'style="display: none"'?>>
            <?php
            if (Controller_Frontend::i()->user->id) {
                echo PROFILE_SUCCESSFULLY_UPDATED;
            } else {
                echo YOU_HAVE_SUCCESSFULLY_REGISTERED . ' ';
                switch ($config['activation_type']) {
                    case Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED:
                        echo NOW_YOU_CAN_LOG_IN_INTO_THE_SYSTEM;
                        break;
                    case Block_Register::ACTIVATION_TYPE_ADMINISTRATOR:
                        echo PLEASE_WAIT_FOR_ADMINISTRATOR_TO_ACTIVATE;
                        break;
                    case Block_Register::ACTIVATION_TYPE_USER:
                        echo PLEASE_ACTIVATE_BY_EMAIL;
                        break;
                }
            }
            ?>
          </div>
          <div class="alert alert-danger" <?php echo ($localError) ? '' : 'style="display: none"'?>>
            <ul>
              <?php foreach ((array)$localError as $key => $val) { ?>
                  <li><?php echo htmlspecialchars($val)?></li>
              <?php } ?>
            </ul>
          </div>
        </div>

        <div data-role="feedback-form" <?php echo ($success[(int)$Block->id] && !Controller_Frontend::i()->user->id) ? 'style="display: none"' : ''?>>
          <p>
            <?php echo ASTERISK_MARKED_FIELDS_ARE_REQUIRED?>
          </p>
          <?php if ($Form->signature) { ?>
                <input type="hidden" name="form_signature" value="<?php echo md5('form' . (int)$Form->id . (int)$Block->id)?>" />
          <?php } ?>
          <?php if ($Form->antispam == 'hidden' && $Form->antispam_field_name && !Controller_Frontend::i()->user->id) { ?>
                <input type="text" autocomplete="off" name="<?php echo htmlspecialchars($Form->antispam_field_name)?>" value="<?php echo htmlspecialchars($DATA[$Form->antispam_field_name])?>" style="position: absolute; left: -9999px" />
          <?php } ?>
          <?php foreach ($Form->fields as $row) { ?>
              <div class="form-group">
                <label<?php echo !$row->multiple ? ' for="' . htmlspecialchars($row->urn . $row->id . '_' . $Block->id) . '"' : ''?> class="control-label col-sm-3">
                  <?php echo htmlspecialchars($row->name . ($row->required ? '*' : ''))?>
                </label>
                <div class="col-sm-9 col-md-4">
                  <?php $getField($row, $DATA)?>
                </div>
              </div>
          <?php } ?>
          <?php if ($config['allow_edit_social']) { ?>
              <style type="text/css">
              .raas-social { display: inline-block; width: 16px; height: 16px; background-image: url('http://ulogin.ru/img/small.png?version=1.3.00'); }
              .raas-social<?php echo SocialProfile::SN_VK?> { background-position: 0 -19px; }
              .raas-social<?php echo SocialProfile::SN_FB?> { background-position: 0 -88px; }
              .raas-social<?php echo SocialProfile::SN_OK?> { background-position: 0 -42px; }
              .raas-social<?php echo SocialProfile::SN_MR?> { background-position: 0 -65px; }
              .raas-social<?php echo SocialProfile::SN_TW?> { background-position: 0 -111px; }
              .raas-social<?php echo SocialProfile::SN_LJ?> { background-position: 0 -180px; }
              .raas-social<?php echo SocialProfile::SN_GO?> { background-position: 0 -134px; }
              .raas-social<?php echo SocialProfile::SN_YA?> { background-position: 0 -157px; }
              .raas-social<?php echo SocialProfile::SN_WM?> { background-position: 0 -410px; }
              .raas-social<?php echo SocialProfile::SN_YT?> { background-position: 0 -433px; }
              </style>
              <script src="//ulogin.ru/js/ulogin.js"></script>
              <div class="col-sm-offset-3 col-sm-9 col-md-6" style="margin-bottom: 25px">
                <h3><?php echo SOCIAL_NETWORKS?></h3>
                <div data-role="raas-social-network-container" style="margin: 20px 0">
                  <?php foreach ((array)$DATA['social'] as $i => $temp) { ?>
                      <div data-role="raas-repo-element" class="clearfix">
                        <input type="hidden" name="social[]" value="<?php echo htmlspecialchars($temp)?>" />
                        <a href="<?php echo htmlspecialchars($temp)?>" target="_blank">
                          <span class="raas-social raas-social<?php echo (int)SocialProfile::getSocialNetwork($temp)?>"></span>
                          <?php echo htmlspecialchars($temp)?>
                        </a>
                        <a href="#" class="close" style="float: right;" data-role="raas-repo-del">
                          &times;
                        </a>
                      </div>
                  <?php } ?>
                </div>
                <div id="uLogin" data-ulogin="display=panel;fields=first_name,last_name;providers=vkontakte,odnoklassniki,mailru,facebook;hidden=twitter,google,yandex,livejournal,youtube,webmoney;redirect_uri=;callback=RAAS_CMS_social_login"></div>
              </div>
              <script>
              jQuery(document).ready(function($) {
                  $('[data-role="raas-social-network-container"]').on('click', '[data-role="raas-repo-del"]', function() {
                      $(this).closest('[data-role="raas-repo-element"]').remove();
                      return false;
                  });
                  RAAS_CMS_social_login = function(token)
                  {
                      $.post(location.toString(), {'token': token, 'AJAX': 1}, function(data) {
                          var isFound = false;
                          $('[data-role="raas-social-network-container"] input:hidden').each(function() {
                              if ($.trim($(this).val()) == $.trim(data.social)) {
                                  isFound = true;
                              }
                          });
                          if (!isFound) {
                              var text = '<div data-role="raas-repo-element" class="clearfix">'
                                       + '  <input type="hidden" name="social[]" value="' + data.social + '" />'
                                       + '  <a href="' + data.social + '" target="_blank"><span class="raas-social raas-social' + data.socialNetwork + '"></span> ' + data.social + '</a>'
                                       + '  <a href="#" class="close" style="float: right;" data-role="raas-repo-del">&times;</a>'
                                       + '</div>';
                              $('[data-role="raas-social-network-container"]').append(text);
                          }
                      }, 'json');
                  }
              });
              </script>
          <?php } ?>
          <?php if ($Form->antispam == 'captcha' && $Form->antispam_field_name && !Controller_Frontend::i()->user->id) { ?>
              <div class="form-group">
                <label for="<?php echo htmlspecialchars($Form->antispam_field_name)?>" class="control-label col-sm-3">
                  <?php echo CAPTCHA?>
                </label>
                <div class="col-sm-9 col-md-4">
                  <img src="/assets/kcaptcha/?<?php echo session_name() . '=' . session_id()?>" /><br />
                  <input type="text" name="<?php echo htmlspecialchars($Form->antispam_field_name)?>" class="form-control" />
                </div>
              </div>
          <?php } ?>
          <div class="form-group">
            <div class="col-sm-9 col-md-4 col-sm-offset-3">
              <button class="btn btn-primary" type="submit">
                <?php echo $User->id ? SAVE : DO_REGISTER?>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
<?php } ?>
