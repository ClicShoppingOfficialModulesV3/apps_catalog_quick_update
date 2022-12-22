<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @licence MIT - Portion of osCommerce 2.4
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Catalog\QuickUpdate\Sites\ClicShoppingAdmin\Pages\Home\Actions;

  use ClicShopping\OM\Registry;
  use ClicShopping\Apps\Catalog\Categories\Classes\ClicShoppingAdmin\CategoriesAdmin;

  class QuickUpdate extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_QuickUpdate = Registry::get('QuickUpdate');
      $CLICSHOPPING_CategoriesAdmin = Registry::get('CategoriesAdmin');

      $this->page->setFile('quick_update.php');

      $CLICSHOPPING_QuickUpdate->loadDefinitions('Sites/ClicShoppingAdmin/main');
    }
  }