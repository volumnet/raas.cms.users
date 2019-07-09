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
$_RAASForm_FormTab = function (FormTab $formTab) use (
    &$_RAASForm_Form_Tabbed,
    &$_RAASForm_Form_Plain,
    &$_RAASForm_Attrs
) {
    $table = $formTab->meta['table'];
    $billingType = $formTab->meta['billingType'];
    $user = $formTab->meta['user'];
    $balance = (float)$billingType->getBalance($user);
    include CMSViewSubMain::i()->tmp('/table.inc.php');
    ?>
    <h2>
      Баланс
      <span class="text-<?php echo ($balance >= 0) ? 'success' : 'error'?>">
        <?php echo number_format($balance, 2, '.', ' ')?>
      </span>
    </h2>
    <table<?php echo $_RAASTable_Attrs($table)?>>
      <?php if ($table->header) { ?>
          <thead>
            <tr>
              <?php
              foreach ($table->columns as $key => $col) {
                  include Application::i()->view->context->tmp('/column.inc.php');
                  if ($col->template) {
                      include Application::i()->view->context->tmp($col->template);
                  }
                  $_RAASTable_Header($col, $key);
              }
              ?>
            </tr>
          </thead>
      <?php } ?>
      <?php if ((array)$table->Set) { ?>
          <tbody>
            <?php
            for ($i = 0; $i < count($table->rows); $i++) {
                $row = $table->rows[$i];
                include CMSViewWeb::i()->tmp('/row.inc.php');
                if ($row->template) {
                    include Application::i()->view->context->tmp($row->template);
                }
                $_RAASTable_Row($row, $i);
                ?>
            <?php } ?>
          </tbody>
      <?php } ?>
      <tfoot>
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
            <input type="text" class="span5" name="billing_transaction_name[<?php echo (int)$billingType->id?>]" />
          </td>
        </tr>
      </tfoot>
    </table>
    <?php
};
