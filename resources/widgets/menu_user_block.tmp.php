<?php
/**
 * Виджет блока "Меню пользователя"
 * @param Block_PHP $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Package;

?>
<nav class="menu-user"></nav>
<?php echo Package::i()->asset('/js/menu-user.js')?>
