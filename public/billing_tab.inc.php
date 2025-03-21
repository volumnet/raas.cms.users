<?php
/**
 * Вкладка транзакций по биллингу
 */
namespace RAAS\CMS\Users;

use RAAS\Application;
use RAAS\FormTab;
use RAAS\CMS\View_Web as CMSViewWeb;
use RAAS\CMS\ViewSub_Main as CMSViewSubMain;

/**
 * Отображает вкладку
 * @param FormTab $formTab Вкладка
 */
$_RAASForm_FormTab = function (FormTab $formTab) {
    $table = $formTab->meta['table'];
    $billingType = $formTab->meta['billingType'];
    $user = $formTab->meta['user'];
    $balance = (float)$billingType->getBalance($user);
    ?>
    <h2>
      Баланс
      <span class="text-<?php echo ($balance >= 0) ? 'success' : 'error'?>">
        <?php echo number_format($balance, 2, '.', ' ')?>
      </span>
    </h2>
    <table<?php echo $table->getAttrsString()?>>
      <?php if ($table->header) { ?>
          <thead>
            <?php echo $table->renderHeaderRow()?>
            <tr>
              <td></td>
              <td>
                <?php
                $author = Application::i()->user;
                echo htmlspecialchars(
                    $author->full_name ?
                    $author->full_name . ' (' . $author->login . ')' :
                    $author->login
                );
                ?>
              </td>
              <td colspan="2">
                <input type="number" step="0.01" name="billing_transaction_amount[<?php echo (int)$billingType->id?>]" value="" placeholder="<?php echo ViewSub_Users::i()->_('PAYMENT_AMOUNT')?>" style="margin: 0 auto;" />
              </td>
              <td>
                <input type="text" class="span5" name="billing_transaction_name[<?php echo (int)$billingType->id?>]" style="margin: 0 auto;" />
                <button type="submit" class="btn btn-primary"><span class="fa fa-plus"></span></button>
              </td>
            </tr>
          </thead>
      <?php } ?>
      <?php echo $table->renderBody()?>
    </table>
    <?php
};
