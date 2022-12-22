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


  namespace ClicShopping\Apps\Catalog\QuickUpdate\Sites\ClicShoppingAdmin\Pages\Home\Actions\QuickUpdate;

  use ClicShopping\OM\Registry;


  class Calcul extends \ClicShopping\OM\PagesActionsAbstract
  {

    public function __construct()
    {
      $this->app = Registry::get('QuickUpdate');
    }

    public function execute()
    {
      global $preview_global_price;
      if (isset($_POST['spec_price'])) $preview_global_price = 'true';
    }
  }