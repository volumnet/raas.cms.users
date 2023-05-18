<?php
/**
 * Уведомление о регистрации
 * @param bool $SMS Уведомление отправляется по SMS
 * @param Form $Form Форма регистрации
 * @param bool $ADMIN Отправка сообщения для администратора
 *     (если false то для пользователя)
 * @param User $User Пользователь
 * @param array $config Конфигурация блока
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS;

use RAAS\Controller_Frontend as ControllerFrontend;
use RAAS\CMS\Users\Block_Register;
use RAAS\CMS\Users\Block_Activation;

$cf = ControllerFrontend::i();
$adminUrl = $cf->schemeHost . '/admin/?p=cms';

$page = $User->page;

if ($ADMIN) {
    $headerTemplate = NEW_USER_REGISTERED_ON_SITE;
} else {
    $headerTemplate = YOU_HAVE_SUCCESSFULLY_REGISTERED_ON_WEBSITE;
}
if ($SMS) {
    if ($ADMIN) {
        echo sprintf($headerTemplate, $cf->schemeHost, $cf->idnHost) . "\n";
    }
    foreach ($Form->fields as $field) {
        $renderer = NotificationFieldRenderer::spawn($field, $USER);
        echo $renderer->render(['admin' => $ADMIN, 'sms' => true]);
    }
} else { ?>
    <p>
      <?php echo sprintf($headerTemplate, $cf->schemeHost, $cf->idnHost)?>
    </p>
    <div>
      <?php
      if (!$ADMIN) {
          $fields = $Form->visFields;
      } else {
          $fields = $Form->fields;
      }
      $passwordDetected = false;
      foreach ($fields as $field) {
          if ($field->datatype == 'password') {
              $passwordDetected = true;
              if ($ADMIN) {
                  continue;
              }
          }
          $renderer = NotificationFieldRenderer::spawn($field, $User);
          echo $renderer->render(['admin' => $ADMIN, 'sms' => false]);
      }
      if (!$passwordDetected && !$ADMIN && $User->password) {
          echo '<div>' . PASSWORD . ': ' . htmlspecialchars($User->password) . '</div>';
      }
      ?>
    </div>
    <?php if ($ADMIN) {
        if ($User && $User->id) { ?>
            <p>
              <a href="<?php echo htmlspecialchars($adminUrl . '&m=users&action=edit&id=' . (int)$User->id)?>">
                <?php echo VIEW?>
              </a>
            </p>
        <?php } ?>
        <p>
          <small>
            <?php
            echo IP_ADDRESS . ': ' .
                htmlspecialchars($User->ip) . '<br />' .
                USER_AGENT . ': ' .
                htmlspecialchars($User->user_agent) . '<br />' .
                PAGE . ': ';
            if ($page->parents) {
                foreach ($page->parents as $row) { ?>
                    <a href="<?php echo htmlspecialchars($adminUrl . '&id=' . (int)$row->id)?>">
                      <?php echo htmlspecialchars($row->name)?>
                    </a> /
                <?php }
            } ?>
            <a href="<?php echo htmlspecialchars($adminUrl . '&id=' . (int)$page->id)?>">
              <?php echo htmlspecialchars($page->name)?>
            </a>
          </small>
        </p>
    <?php } else {
        switch ($config['activation_type']) {
            case Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED:
                echo '<p>' . NOW_YOU_CAN_LOG_IN_INTO_THE_SYSTEM . '</p>';
                break;
            case Block_Register::ACTIVATION_TYPE_ADMINISTRATOR:
                echo '<p>' . PLEASE_WAIT_FOR_ADMINISTRATOR_TO_ACTIVATE . '</p>';
                break;
            case Block_Register::ACTIVATION_TYPE_USER:
                $activationBlocks = Block_Activation::getSet([
                    'where' => "block_type = 'RAAS\\\\CMS\\\\Users\\\\Block_Activation'",
                    'orderBy' => "id"
                ]);
                $activationPages = [];
                if ($activationBlocks) {
                    $activationPages = [];
                    foreach ($activationBlocks as $activationBlock) {
                        $activationPages = array_merge(
                            $activationPages,
                            $activationBlock->pages
                        );
                    }
                }
                $p = $Page->parent;
                $activationPage = null;
                while ($p->id) {
                    $nearestActivationPages = array_filter(
                        $activationPages,
                        function ($x) use ($p) {
                            return $x->pid == $p->id;
                        }
                    );
                    if ($nearestActivationPages) {
                        $activationPage = array_shift($nearestActivationPages);
                        break;
                    }
                    $p = $p->parent;
                }
                if (!$activationPage->id && $activationPages) {
                    $activationPage = array_shift($activationPages);
                }
                if ($activationPage->id) {
                    $url = $cf->schemeHost . $activationPage->url
                        . '?key=' . $User->activationKey;
                    echo '<p>' . sprintf(ACTIVATION_LINK, $url, $url) . '</p>';
                }
                break;
        }
        ?>
        <p>--</p>
        <p>
          <?php echo WITH_RESPECT . ',<br />' . ADMINISTRATION_OF_SITE?>
          <a href="<?php echo htmlspecialchars($cf->schemeHost)?>">
            <?php echo htmlspecialchars($cf->idnHost)?>
          </a>
        </p>
        <?php
    }
}
