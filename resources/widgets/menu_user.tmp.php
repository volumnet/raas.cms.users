<?php
/**
 * Виджет "Меню пользователя" для AJAX
 * @param Block_PHP $Block Текущий блок
 * @param Page $Page Текущая страница
 */
namespace RAAS\CMS\Users;

use RAAS\Controller_Frontend;
use RAAS\CMS\Shop\Module as ShopModule;

$user = Controller_Frontend::i()->user;
if ($_GET['mobile']) {
    if ($user->id) { ?>
        <li class="menu-mobile__item menu-mobile__item_main menu-mobile__item_level_0 menu-mobile__item_full-name">
          <span class="menu-mobile__link menu-mobile__link_main menu-mobile__link_level_0 menu-mobile__link_full-name">
            <?php echo htmlspecialchars($user->full_name)?>
          </span>
        </li>
        <li class="menu-mobile__item menu-mobile__item_main menu-mobile__item_level_0 menu-mobile__item_cabinet">
          <a href="/profile/" class="menu-mobile__link menu-mobile__link_main menu-mobile__link_level_0 menu-mobile__link_cabinet">
            <?php echo EDIT_PROFILE?>
          </a>
        </li>
        <?php if (class_exists(ShopModule::class)) { ?>
            <li class="menu-mobile__item menu-mobile__item_main menu-mobile__item_level_0 menu-mobile__item_my-orders">
              <a href="/my_orders/" class="menu-mobile__link menu-mobile__link_main menu-mobile__link_level_0 menu-mobile__link_my-orders">
                <?php echo MY_ORDERS?>
              </a>
            </li>
        <?php } ?>
        <li class="menu-mobile__item menu-mobile__item_main menu-mobile__item_level_0 menu-mobile__item_logout">
          <a href="/login/?logout=1" class="menu-mobile__link menu-mobile__link_main menu-mobile__link_level_0 menu-mobile__link_logout">
            <?php echo LOG_OUT?>
          </a>
        </li>
    <?php } else { ?>
        <li class="menu-mobile__item menu-mobile__item_main menu-mobile__item_level_0 menu-mobile__item_login">
          <a href="/login/" class="menu-mobile__link menu-mobile__link_main menu-mobile__link_level_0 menu-mobile__link_login">
            <?php echo LOG_IN?>
          </a>
        </li>
        <li class="menu-mobile__item menu-mobile__item_main menu-mobile__item_level_0 menu-mobile__item_register">
          <a href="/register/" class="menu-mobile__link menu-mobile__link_main menu-mobile__link_level_0 menu-mobile__link_register">
            <?php echo REGISTRATION?>
          </a>
        </li>
        <li class="menu-mobile__item menu-mobile__item_main menu-mobile__item_level_0 menu-mobile__item_recovery">
          <a href="/recovery/" class="menu-mobile__link menu-mobile__link_main menu-mobile__link_level_0 menu-mobile__link_recovery">
            <?php echo LOST_PASSWORD?>
          </a>
        </li>
    <?php } ?>
<?php } else { ?>
      <ul class="menu-user__list menu-user__list_level_0 menu-user__list_main">
        <?php if ($user->id) { ?>
            <li class="menu-user__item menu-user__item_level_0 menu-user__item_main menu-user__item_full-name">
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
                      <a href="/my_orders/" class="menu-user__link menu-user__link_level_1 menu-user__link_inner menu-user__link_my-orders">
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
        <?php } else { ?>
            <li class="menu-user__item menu-user__item_level_0 menu-user__item_main menu-user__item_login">
              <a href="/login/" class="menu-user__link menu-user__link_level_0 menu-user__link_main menu-user__link_login">
                <?php echo LOG_IN?>
              </a>
            </li>
            <li class="menu-user__item menu-user__item_level_0 menu-user__item_main menu-user__item_register">
              <a href="/register/" class="menu-user__link menu-user__link_level_0 menu-user__link_main menu-user__link_register">
                <?php echo REGISTRATION?>
              </a>
            </li>
        <?php } ?>
      </ul>
<?php } ?>
