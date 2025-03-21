<?php
/**
 * Виджет редактирования пользователя
 */
namespace RAAS\CMS\Users;

echo $Form->renderFull();

if ($Form->Item->email && !Module::i()->registryGet('automatic_notification')) { ?>
    <div
      id="notifyUserModal"
      class="modal hide fade"
      tabindex="-1"
      role="dialog"
      aria-labelledby="notifyUserModalLabel"
      aria-hidden="true"
      style="width: 1000px; margin-left: -500px; top: 2%; position: absolute;"
    >
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="notifyUserModalLabel" style="display: inline-block">
          <?php echo Module::i()->view->_('NOTIFICATION')?>
        </h3>
        <div style="display: inline-block; vertical-align: top;">
          <label class="checkbox inline">
            <input type="radio" name="notify_about" id="notify_about_activation" />
            <?php echo Module::i()->view->_('NOTIFY_ABOUT_ACTIVATION')?>
          </label>
          <label class="checkbox inline">
            <input type="radio" name="notify_about" id="notify_about_block" />
            <?php echo Module::i()->view->_('NOTIFY_ABOUT_BLOCK')?>
          </label>
        </div>
      </div>
      <form action="ajax.php?p=cms&m=users&action=send_notification&id=<?php echo (int)$Item->id?>" method="post">
        <div class="modal-body" style="max-height: none">
          <div data-role="message">
            <div><input type="text" id="notifySubject" name="notification_subject" style="width: 956px"></div>
            <div data-role="text-container"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">
            <?php echo Module::i()->view->_('CANCEL')?>
          </button>
          <button type="submit" class="btn btn-primary">
            <?php echo Module::i()->view->_('SEND')?>
          </button>
        </div>
      </form>
      <div data-role="notifications" style="display: none">
        <div class="alert alert-success">
          <?php echo Module::i()->view->_('YOUR_NOTIFICATION_HAS_BEEN_SUCCESSFULLY_SENT')?>
        </div>
      </div>
    </div>
    <script src="<?php echo $VIEW->context->publicURL?>/edit.js"></script>
<?php } ?>
