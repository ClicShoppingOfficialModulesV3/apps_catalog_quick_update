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

  use ClicShopping\Apps\Catalog\QuickUpdate\Classes\ClicShoppingAdmin\Status;
  use ClicShopping\Apps\Catalog\Products\Classes\ClicShoppingAdmin\ProductsStatusAdmin;

  class Update extends \ClicShopping\OM\PagesActionsAbstract
  {

    public function __construct()
    {
      $this->app = Registry::get('QuickUpdate');
    }

    public function execute()
    {
      global $current_category_id;

      $CLICSHOPPING_Hooks = Registry::get('Hooks');
      $CLICSHOPPING_Language = Registry::get('Language');
      $CLICSHOPPING_MessageStack = Registry::get('MessageStack');

      $count_update = 0;
      $item_updated = [];

      if ($_POST['product_new_model']) {
        foreach ($_POST['product_new_model'] as $id => $new_model) {
          if (trim($_POST['product_new_model'][$id]) != trim($_POST['product_old_model'][$id])) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_model = :products_model
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', (int)$id);
            $Qupdate->bindValue(':products_model', $new_model);
            $Qupdate->execute();
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (isset($_POST['product_new_name'])) {
        foreach ($_POST['product_new_name'] as $id => $new_name) {
          if (trim($_POST['product_new_name'][$id]) != trim($_POST['product_old_name'][$id])) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products_description
                                                set products_name= :products_name
                                                where products_id = :products_id
                                                and language_id= :language_id
                                              ');
            $Qupdate->bindValue(':products_name', $new_name);
            $Qupdate->bindInt(':products_id', (int)$id);
            $Qupdate->bindInt(':language_id', $CLICSHOPPING_Language->getId());
            $Qupdate->execute();

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_last_modified = now()
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', (int)$id);
            $Qupdate->execute();
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
//***************************************
        }
      }

      if (isset($_POST['product_new_price'])) {
        foreach ($_POST['product_new_price'] as $id => $new_price) {
          if ($_POST['product_new_price'][$id] != $_POST['product_old_price'][$id] && isset($_POST['update_price'][$id])) {

            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_price = :products_price
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', (int)$id);
            $Qupdate->bindValue(':products_price', $new_price);
            $Qupdate->execute();

// B2B
            $QcustomersGroup = $this->app->db->prepare('select  customers_group_id,
                                                                 customers_group_name,
                                                                 customers_group_discount
                                                           from :table_customers_groups
                                                           where customers_group_id >  0
                                                           order by customers_group_id
                                                          ');
            $QcustomersGroup->execute();

            while ($QcustomersGroup->fetch()) {
              if ($QcustomersGroup->rowCount() > 0) {

                $Qattributes = $this->app->db->prepare('select customers_group_id,
                                                                customers_group_price,
                                                                products_price
                                                         from :table_products_groups
                                                         where products_id = :products_id
                                                         and customers_group_id = :customers_group_id
                                                         order by customers_group_id
                                                      ');

                $Qattributes->bindInt(':products_id', (int)$id);
                $Qattributes->bindInt(':customers_group_id', $QcustomersGroup->valueInt('customers_group_id'));
                $Qattributes->execute();

//                $attributes = $Qattributes->fetch();

                $Qdiscount = $this->app->db->prepare('select discount
                                                from :table_groups_to_categories
                                                where customers_group_id = :customers_group_id
                                                and categories_id = :categories_id
                                                ');

                $Qdiscount->bindInt(':categories_id', (int)$current_category_id);
                $Qdiscount->bindInt(':customers_group_id', $QcustomersGroup->valueInt('customers_group_id'));
                $Qdiscount->execute();

                $discount = $Qdiscount->fetch();

                if (is_null($discount['discount'])) {
                  $ricarico = $QcustomersGroup->value('customers_group_discount');
                } else {
                  $ricarico = $discount['discount'];
                }
              }

              $pricek = $new_price;
//$ricarico = $customers_group['customers_group_discount'];

              if ($pricek > 0) {

                if (B2B == 'true') {
                  if ($ricarico > 0) $newprice = $pricek + ($pricek / 100) * $ricarico;
                  if ($ricarico == 0) $newprice = $pricek;
                }

                if (B2B == 'false') {
                  if ($ricarico > 0) $newprice = $pricek - ($pricek / 100) * $ricarico;
                  if ($ricarico == 0) $newprice = $pricek;
                }

              } else {
                $newprice = 0;
              }

              if ($Qattributes->valueInt('customers_group_id') == NULL) {

                $this->app->db->save('products_groups', [
                    'customers_group_id' => (int)$QcustomersGroup->valueInt('customers_group_id'),
                    'customers_group_price' => (float)$newprice,
                    'products_id' => (int)$id,
                    'products_price' => (float)$pricek
                  ]
                );

              } else {
                $Qupdate = $this->app->db->prepare('update :table_products_groups
                                                    set customers_group_price = :customers_group_price
                                                    where products_id = :products_id
                                                    and customers_group_id= :customers_group_id
                                                  ');

                $Qupdate->bindInt(':products_id', (int)$id);
                $Qupdate->bindValue(':customers_group_price', $new_price);
                $Qupdate->bindInt(':customers_group_id', $Qattributes->valueInt('customers_group_id'));
                $Qupdate->execute();
              }

              $count_update++;
              $item_updated[$id] = 'updated';
            }
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (isset($_POST['product_new_weight'])) {
        foreach ($_POST['product_new_weight'] as $id => $new_weight) {
          if ($_POST['product_new_weight'][$id] != $_POST['product_old_weight'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_weight = :products_weight
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', (int)$id);
            $Qupdate->bindValue(':products_weight', $new_weight);
            $Qupdate->execute();
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (isset($_POST['product_new_quantity'])) {
        foreach ($_POST['product_new_quantity'] as $id => $new_quantity) {
          if ($_POST['product_new_quantity'][$id] != $_POST['product_old_quantity'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_quantity = :products_quantity
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->bindInt(':products_quantity', $new_quantity);
            $Qupdate->execute();
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

// min order quantity
      if (isset($_POST['new_products_min_qty_order'])) {
        foreach ($_POST['new_products_min_qty_order'] as $id => $new_products_min_qty_order) {
          if ($_POST['new_products_min_qty_order'][$id] != $_POST['products_old_min_qty_order'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_min_qty_order = :products_min_qty_order
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->bindInt(':products_min_qty_order', $new_products_min_qty_order);
            $Qupdate->execute();
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (isset($_POST['product_new_manufacturer'])) {
        foreach ($_POST['product_new_manufacturer'] as $id => $new_manufacturer) {
          if ($_POST['product_new_manufacturer'][$id] != $_POST['product_old_manufacturer'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set manufacturers_id = :manufacturers_id
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->bindInt(':manufacturers_id', $new_manufacturer);
            $Qupdate->execute();
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (isset($_POST['product_new_supplier'])) {
        foreach ($_POST['product_new_supplier'] as $id => $new_supplier) {
          if ($_POST['product_new_supplier'][$id] != $_POST['product_old_supplier'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set suppliers_id = :suppliers_id
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->bindInt(':suppliers_id', $new_supplier);
            $Qupdate->execute();

          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (isset($_POST['product_new_image'])) {
        foreach ($_POST['product_new_image'] as $id => $new_image) {
          if (trim($_POST['product_new_image'][$id]) != trim($_POST['product_old_image'][$id])) {
            $count_update++;
            $item_updated[$id] = 'updated';

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_image = :products_image
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->bindValue(':products_image', $new_image);
            $Qupdate->execute();
          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (isset($_POST['product_new_status'])) {
        foreach ($_POST['product_new_status'] as $id => $new_status) {
          if ($_POST['product_new_status'][$id] != $_POST['product_old_status'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';
            ProductsStatusAdmin::getProductStatus($id, $new_status);

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_last_modified = now()
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->execute();
          }
        }

        $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
      }

      if (isset($_POST['product_new_products_price_comparison'])) {
        foreach ($_POST['product_new_products_price_comparison'] as $id => $new_products_price_comparison) {
          if ($_POST['product_new_products_price_comparison'][$id] != $_POST['product_old_products_price_comparison'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';
            Status::GetProductPriceComparison($id, $new_products_price_comparison);

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_last_modified = now()
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->execute();
          }
        }

        $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
      }


      if (isset($_POST['product_new_products_only_online'])) {
        foreach ($_POST['product_new_products_only_online'] as $id => $new_products_only_online) {
          if ($_POST['product_new_products_only_online'][$id] != $_POST['product_old_products_only_online'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';
            Status::GetProductOnlyOnline($id, $new_products_only_online);

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_last_modified = now()
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', (int)$id);
            $Qupdate->execute();

          }
        }

        $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
      }

      if (isset($_POST['product_new_tax'])) {
        foreach ($_POST['product_new_tax'] as $id => $new_tax_id) {
          if ($_POST['product_new_tax'][$id] != $_POST['product_old_tax'][$id]) {
            $count_update++;
            $item_updated[$id] = 'updated';

            if ($new_tax_id == '') $new_tax_id = 0;

            $Qupdate = $this->app->db->prepare('update :table_products
                                                set products_tax_class_id = :products_tax_class_id
                                                where products_id = :products_id
                                              ');

            $Qupdate->bindInt(':products_id', $id);
            $Qupdate->bindValue(':products_tax_class_id', $new_tax_id);
            $Qupdate->execute();

          }

          $CLICSHOPPING_Hooks->call('QuickUpdate', 'Update');
        }
      }

      if (is_array($item_updated)) {
        $count_item = array_count_values($item_updated);
        if ($count_item['updated'] > 0) $CLICSHOPPING_MessageStack->add($count_item['updated'] . ' ' . $this->app->getDef('text_products_updated') . ' / ' . " $count_update " . $this->app->getDef('text_qty_updated'), 'success');
      }
    }
  }