<?php
/**
 * Меню пользователя
 * @param Block_PHP $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Package;

?>
<nav class="menu-user" data-vue-role="menu-user" data-v-bind_has-orders="<?php echo class_exists('RAAS\CMS\Shop\Module') ? 'true' : 'false'?>" data-v-bind_user="user">
  <ul class="menu-user__list menu-user__list_main menu-user__list_level_0">
    <li class="menu-user__item menu-user__item_level_0 menu-user__item_main menu-user__item_login">
      <a href="/login/" class="menu-user__link menu-user__link_level_0 menu-user__link_main menu-user__link_login">
        <?php echo LOG_IN?>
      </a>
    </li>
  </ul>
</nav>
