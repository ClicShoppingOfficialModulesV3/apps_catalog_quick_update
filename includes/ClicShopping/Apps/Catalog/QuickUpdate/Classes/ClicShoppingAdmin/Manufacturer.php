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

  class Manufacturer
  {

    public function __construct()
    {
      $CLICSHOPPING_QuickUpdate = Registry::get('QuickUpdate');
      $this->app = $CLICSHOPPING_QuickUpdate;
    }

    /**
     * Display the list of the manufacturers
     *
     * @param string
     * @return string
     * @access public
     * manufacturers_list
     */
    public function getManufacturersList()
    {
      global $manufacturer;

      $Qmanufacturers = $this->app->db->prepare('select m.manufacturers_id,
                                                        m.manufacturers_name
                                                from :table_manufacturers m
                                                order by m.manufacturers_name ASC
                                               ');

      $Qmanufacturers->execute();

      $return_string = '<select name="manufacturer" onChange="this.form.submit();">';
      $return_string .= '<option value="0">' . $this->app->getDef('text_all_manufacturers') . '</option>';

      while ($Qmanufacturers->fetch()) {
        $return_string .= '<option value="' . $Qmanufacturers->valueint('manufacturers_id') . '"';
        if ($manufacturer && $Qmanufacturers->valueInt('manufacturers_id') == $manufacturer) $return_string .= ' SELECTED';
        $return_string .= '>' . $Qmanufacturers->value('manufacturers_name') . '</option>';
      }

      $return_string .= '</select>';

      return $return_string;
    }
  }