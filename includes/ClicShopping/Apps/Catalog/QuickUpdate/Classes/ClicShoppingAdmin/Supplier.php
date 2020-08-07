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

  class Supplier
  {

    public function __construct()
    {
      $CLICSHOPPING_QuickUpdate = Registry::get('QuickUpdate');
      $this->app = $CLICSHOPPING_QuickUpdate;
    }

    /**
     * Display the list of the suppliers
     *
     * @param string
     * @return string
     * @access public
     */
    public function getSuppliersList()
    {
      global $suppliers;

      $Qsuppliers = $this->app->db->prepare('select suppliers_id,
                                                     suppliers_name
                                               from :table_suppliers
                                               order by suppliers_name ASC
                                              ');
      $Qsuppliers->execute();

      $return_string = '<select name="supplier" onChange="this.form.submit();">';
      $return_string .= '<option value="' . 0 . '">' . $this->app->getDef('text_all_suppliers') . '</option>';

      while ($suppliers = $Qsuppliers->fetch()) {
        $return_string .= '<option value="' . $suppliers['suppliers_id'] . '"';
        if ($suppliers && $suppliers['suppliers_id'] == $suppliers) $return_string .= ' SELECTED';
        $return_string .= '>' . $suppliers['suppliers_name'] . '</option>';
      }
      $return_string .= '</select>';
      return $return_string;
    }
  }