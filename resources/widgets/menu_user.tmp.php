<?php
/**
 * Виджет блока "Меню пользователя"
 * @param Block_PHP $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\CMS\Package;

?>
<nav class="menu-user">
  <ul class="menu-user__list menu-user__list_main menu-user__list_level_0">
    <li data-v-if="user.id" class="menu-user__item menu-user__item_level_0 menu-user__item_main menu-user__item_full-name">
      <a href="/profile/" class="menu-user__link menu-user__link_level_0 menu-user__link_main menu-user__link_full-name">
        <?php echo htmlspecialchars($user->full_name)?>
      </a>
      <ul class="menu-user__list menu-user__list_level_1 menu-user__list_inner">
        <li class="menu-user__item menu-user__item_level_1 menu-user__item_inner menu-user__item_cabinet">
          <a href="/profile/" class="menu-user__link menu-user__link_level_1 menu-user__link_inner menu-user__link_cabinet">
            <?php echo EDIT_PROFILE?>
          </a>
        </li>
        <?php if (class_exists(ShopModule::class)) { ?>
            <li class="menu-user__item menu-user__item_level_1 menu-user__item_inner menu-user__item_my-orders">
              <a href="/my-orders/" class="menu-user__link menu-user__link_level_1 menu-user__link_inner menu-user__link_my-orders">
                <?php echo MY_ORDERS?>
              </a>
            </li>
        <?php } ?>
        <li class="menu-user__item menu-user__item_level_1 menu-user__item_inner menu-user__item_logout">
          <a href="/login/?logout=1" class="menu-user__link menu-user__link_level_1 menu-user__link_inner menu-user__link_logout">
            <?php echo LOG_OUT?>
          </a>
        </li>
      </ul>
    </li>
    <li data-v-else class="menu-user__item menu-user__item_level_0 menu-user__item_main menu-user__item_login">
      <a href="/login/" class="menu-user__link menu-user__link_level_0 menu-user__link_main menu-user__link_login">
        <?php echo LOG_IN?>
      </a>
    </li>
  </ul>
</nav>
<?php Package::i()->requestJS('/js/menu-user.js')?>
