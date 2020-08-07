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

  namespace ClicShopping\Apps\Catalog\QuickUpdate\Classes\ClicShoppingAdmin;

  use ClicShopping\OM\Registry;

  class Status
  {

    protected $products_id;
    protected $products_price_comparison;
    protected $products_only_online;

    /**
     * Status products comparison - Sets the products_price_comparison of products
     *
     * @param string products_id, products_price_comparison
     * @return string status on or off
     * @access public
     * osc_set_product_products_price_comparison
     */
    Public static function GetProductPriceComparison($products_id, $products_price_comparison)
    {
      $CLICSHOPPING_Db = Registry::get('Db');

      if ($products_price_comparison == 1) {

        return $CLICSHOPPING_Db->save('products', ['products_price_comparison' => 1,
          'products_last_modified' => 'now()'
        ],
          ['products_id' => (int)$products_id]
        );

      } elseif ($products_price_comparison == 0) {

        return $CLICSHOPPING_Db->save('products', ['products_price_comparison' => 0,
          'products_last_modified' => 'now()'
        ],
          ['products_id' => (int)$products_id]
        );

      } else {
        return -1;
      }
    }


    /**
     * Status products only online - Sets the products_only_online of products
     *
     * @param string products_id, products_only_online
     * @return string status on or off
     * @access public
     * osc_set_product_products_only_online
     */
    Public static function GetProductOnlyOnline($products_id, $products_only_online)
    {
      $CLICSHOPPING_Db = Registry::get('Db');

      if ($products_only_online == '1') {

        return $CLICSHOPPING_Db->save('products', ['products_only_online' => 1,
          'products_last_modified' => 'now()'
        ],
          ['products_id' => (int)$products_id]
        );

      } elseif ($products_only_online == '0') {

        return $CLICSHOPPING_Db->save('products', ['products_only_online' => 0,
          'products_last_modified' => 'now()'
        ],
          ['products_id' => (int)$products_id]
        );

      } else {
        return -1;
      }
    }
  }