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

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\Registry;

  use ClicShopping\Apps\Catalog\QuickUpdate\Classes\ClicShoppingAdmin\Manufacturer;
  use ClicShopping\Apps\Catalog\QuickUpdate\Classes\ClicShoppingAdmin\Supplier;

  $CLICSHOPPING_QuickUpdate = Registry::get('QuickUpdate');
  $CLICSHOPPING_Page = Registry::get('Site')->getPage();
  $CLICSHOPPING_Template = Registry::get('TemplateAdmin');
  $CLICSHOPPING_Language = Registry::get('Language');
  $CLICSHOPPING_Hooks = Registry::get('Hooks');
  $CLICSHOPPING_CategoriesAdmin = Registry::get('CategoriesAdmin');

  Registry::set('Manufacturer', new Manufacturer());
  $CLICSHOPPING_Manufacturer = Registry::get('Manufacturer');

  Registry::set('Supplier', new Supplier());
  $CLICSHOPPING_Supplier = Registry::get('Supplier');

  $preview_global_price = false; ////???????

  // Register global off
  $row_by_page = 0;
  if (isset($_POST['row_by_page'])) {
    $row_by_page = HTML::sanitize($_POST['row_by_page']);
  }

  $manufacturer = 0;
  if (isset($_POST['manufacturer'])) {
    $manufacturer = HTML::sanitize($_POST['manufacturer']);
  }

  $supplier = 0;
  if (isset($_POST['supplier'])) {
    $supplier = HTML::sanitize($_POST['supplier']);
  }

  $sort_by = '';
  if (isset($_GET['sort_by'])) {
    $sort_by = HTML::sanitize($_GET['sort_by']);
  }

  $search = '';
  if (isset($_POST['search'])) {
    $search = HTML::sanitize($_POST['search']);
  }

  $page = 1;
  if (isset($_POST['page'])) {
    $page = HTML::sanitize($_POST['page']);
  }

  $customers_group_id = 0;
  if (isset($_POST['customers_group_id'])) {
    $customers_group_id = HTML::sanitize($_POST['customers_group_id']);
  }

  $spec_price = '';
  if (isset($_POST['spec_price'])) {
    $spec_price = HTML::sanitize($_POST['spec_price']);
  }

  $current_category_id = 0;
  if (isset($_POST['cPath'])) {
    $current_category_id = HTML::sanitize($_POST['cPath']);
  }

  define('MAX_DISPLAY_ROW_BY_PAGE', MAX_DISPLAY_SEARCH_RESULTS_ADMIN);

  $row_by_page = MAX_DISPLAY_ROW_BY_PAGE;

  // Tax Row
  $tax_class_array = array(array('id' => 0,
    'text' => $CLICSHOPPING_QuickUpdate->getDef('no_tax_text')));

  $QtaxClass = $CLICSHOPPING_QuickUpdate->db->prepare('select tax_class_id,
                                                              tax_class_title
                                                       from :table_tax_class
                                                       order by tax_class_title
                                                    ');
  $QtaxClass->execute();

  while ($QtaxClass->fetch()) {
    $tax_class_array[] = ['id' => $QtaxClass->valueInt('tax_class_id'),
      'text' => $QtaxClass->value('tax_class_title')
    ];
  }

  // Info Row pour le champ fabricant
  $manufacturers_array = array(array('id' => 0,
    'text' => $CLICSHOPPING_QuickUpdate->getDef('no_manufacturer')));

  $Qmanufacturers = $CLICSHOPPING_QuickUpdate->db->prepare('select manufacturers_id,
                                                                    manufacturers_name
                                                            from :table_manufacturers
                                                            order by manufacturers_name
                                                            ');
  $Qmanufacturers->execute();

  while ($Qmanufacturers->fetch()) {
    $manufacturers_array[] = ['id' => $Qmanufacturers->valueInt('manufacturers_id'),
      'text' => $Qmanufacturers->value('manufacturers_name')
    ];
  }

  // Info Row pour le champ fournisseur
  $suppliers_array = array([
    'id' => '0',
    'text' => $CLICSHOPPING_QuickUpdate->getDef('no_supplier')
  ]);

  $Qsuppliers = $CLICSHOPPING_QuickUpdate->db->prepare('select suppliers_id,
                                                              suppliers_name
                                                       from :table_suppliers
                                                       order by suppliers_name
                                                      ');
  $Qsuppliers->execute();

  while ($Qsuppliers->fetch()) {
    $suppliers_array[] = ['id' => $Qsuppliers->valueInt('suppliers_id'),
      'text' => $Qsuppliers->value('suppliers_name')
    ];
  }

  // explode string parameters from preview product

  if (isset($info_back) && $info_back != '-') {
    $infoback = explode('-', $info_back);
    $sort_by = $infoback[0];
    $page = $infoback[1];
    $current_category_id = $infoback[2];
    $row_by_page = $infoback[3];
    $manufacturer = $infoback[4];
    $supplier = $infoback[5];
  }

  // define the step for rollover lines per page
  $row_bypage_array = [];

  for ($i = 10; $i <= 300; $i = $i + 10) {
    $row_bypage_array[] = ['id' => $i,
      'text' => $i
    ];
  }

?>
<!-- Debut surlignage //-->
<script language="javascript">
    <!--
    var browser_family;
    var up = 1;

    if (document.all && !document.getElementById)
        browser_family = "dom2";
    else if (document.layers)
        browser_family = "ns4";
    else if (document.getElementById)
        browser_family = "dom2";
    else
        browser_family = "other";

    function display_ttc(action, prix, taxe, up) { <
        // Script  pour afficher leprix devente TTC       
        if (action == 'display') {
            if (up != 1)
                valeur = Math.round((prix + (taxe / 100) * prix) * 100) / 100;
        } else {
            if (action == 'keyup') {
                valeur = Math.round((parseFloat(prix) + (taxe / 100) * parseFloat(prix)) * 100) / 100;
            } else {
                valeur = '0';
            }
        }
        switch (browser_family) {
            case 'dom2':
                document.getElementById('descDiv').innerHTML = '<font color="#ff0000"><?php echo $CLICSHOPPING_QuickUpdate->getDef('total_cost'); ?> :</font><font color="#0000ff">  ' + valeur + '&nbsp;&euro;</font>';
                break;
            case 'ie4':
                document.all.descDiv.innerHTML = '<font color="#ff0000"><?php echo $CLICSHOPPING_QuickUpdate->getDef('total_cost'); ?> :</font><font color="#0000ff">  ' + valeur + '&nbsp;&euro;</font>';
                break;
            case 'ns4':
                document.descDiv.document.descDiv_sub.document.write(valeur);
                document.descDiv.document.descDiv_sub.document.close();
                break;
            case 'other':
                break;
        }
    }

    -- >
</script>

<div class="contentBody">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-block headerCard">
        <div class="row">
          <span
            class="col-md-1 logoHeading"><?php echo HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'categories/priceupdate.gif', $CLICSHOPPING_QuickUpdate->getDef('heading_title'), '40', '40'); ?></span>
          <span
            class="col-md-1 pageHeading"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('heading_title'); ?></span>
          <span class="col-md-2 text-center float-start">
<?php
  // ----------------------------
  // --------- Suppliers
  // ----------------------------
  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_SUPPLIER == 'True') {

    echo HTML::form('suppliers', $CLICSHOPPING_QuickUpdate->link('QuickUpdate'), 'post', 'class="form-inline"');
    echo HTML::hiddenField('row_by_page', $row_by_page);
    echo HTML::hiddenField('cPath', $current_category_id);
    echo HTML::hiddenField('manufacturer', $manufacturer);
    ?>
    <span class="smallText text-center"><?php echo $CLICSHOPPING_Supplier->getSuppliersList(); ?></span>
    </form>
    <?php
  }
?>
          </span>
          <span class="col-md-2 text-center float-start">
<?php
  // ----------------------------
  // --------- Manufacturer
  // ----------------------------

  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MANUFACTURER == 'True') {
    echo HTML::form('manufacturers', $CLICSHOPPING_QuickUpdate->link('QuickUpdate'), 'post', 'class="form-inline"');
    echo HTML::hiddenField('row_by_page', $row_by_page);
    echo HTML::hiddenField('cPath', $current_category_id);
    echo HTML::hiddenField('supplier', $supplier);
    ?>
    <span class="smallText text-center"><?php echo $CLICSHOPPING_Manufacturer->getManufacturersList(); ?></span>
    </form>
    <?php
  }
?>
            </span>
          <span class="col-md-2 text-center">
<?php
  echo HTML::form('search', $CLICSHOPPING_QuickUpdate->link('QuickUpdate'), 'post', 'class="form-inline"', ['session_id' => true]);
  echo HTML::inputField('search', '', 'id="search" placeholder="' . $CLICSHOPPING_QuickUpdate->getDef('text_search') . '"');
?>
             </form>
           </span>
          <span class="col-md-4 text-end float-end">
<?php
  echo HTML::button($CLICSHOPPING_QuickUpdate->getDef('button_configure'), null, $CLICSHOPPING_QuickUpdate->link('Configure'), 'primary') . ' ';
  echo '<a href="javascript:window.print()"><button type="button" class="btn btn-info">' . $CLICSHOPPING_QuickUpdate->getDef('button_print_text') . '</button></a> ';
  echo HTML::button($CLICSHOPPING_QuickUpdate->getDef('button_reset'), null, $CLICSHOPPING_QuickUpdate->link('QuickUpdate&row_by_page=' . $row_by_page), 'warning');
?>
          </span>
        </div>
      </div>
    </div>
  </div>


  <div class="separator"></div>
  <div class="col-md-12">
    <div class="card card-block headerCard">
      <div class="row">
        <div class="col-md-2 text-center float-start">
          <?php
            echo $CLICSHOPPING_QuickUpdate->getDef('text_maxi_row_by_page');
            // ----------------------------
            // --------- Row by page
            // ----------------------------
            echo HTML::form('row_by_page', $CLICSHOPPING_QuickUpdate->link('QuickUpdate'));
            echo HTML::hiddenField('manufacturer', $manufacturer);
            echo HTML::hiddenField('supplier', $supplier);
            echo HTML::hiddenField('cPath', $current_category_id);


            echo HTML::selectMenu('row_by_page', $row_bypage_array, $row_by_page, 'onchange="this.form.submit();"');
          ?>
          </form>
        </div>

        <div class="col-md-2 text-center float-end">

          <?php
            echo $CLICSHOPPING_QuickUpdate->getDef('display_categories');
            // ----------------------------
            // --------- Categories
            // ----------------------------
            echo HTML::form('categorie', $CLICSHOPPING_QuickUpdate->link('QuickUpdate'), 'post', 'class="form-inline"');
            echo HTML::hiddenField('row_by_page', $row_by_page);
            echo HTML::hiddenField('manufacturer', $manufacturer);
            echo HTML::hiddenField('supplier', $supplier);


            echo HTML::selectField('cPath', $CLICSHOPPING_CategoriesAdmin->getCategoryTree(), $current_category_id, 'onchange="this.form.submit();"');
          ?>
          </form>
        </div>

        <div class="col-md-4 text-center float-end">
          <?php
            echo HTML::form('spec_price', $CLICSHOPPING_QuickUpdate->link('QuickUpdate&Calcul&page=' . $page . '&sort_by=' . $sort_by . '&cPath=' . $current_category_id . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier));
            if ($preview_global_price != 'true') {
              ?>
              <span><?php echo $CLICSHOPPING_QuickUpdate->getDef('text_input_spec_price'); ?></span>
              <span><?php echo HTML::inputField('spec_price', 0, 'placeholder="New Price or %"'); ?></span>
              <?php
            } else {
              ?>
              <span> <?php echo $CLICSHOPPING_QuickUpdate->getDef('text_spec_price_info_update'); ?></span>

              <?php
            }
          ?>
        </div>
        <div>

          <?php
            if ($preview_global_price != 'true') {
              ?>
              <span class="col-md-1">
<?php
  echo HTML::button($CLICSHOPPING_QuickUpdate->getDef('button_preview'), null, null, 'info');
  echo HTML::hiddenField($CLICSHOPPING_QuickUpdate->getDef('button_preview'), '&page=' . $page . '&sort_by=' . $sort_by . '&cPath=' . $current_category_id . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier);
?>

          </span>
         <?php
            } else {
         ?>
              <span class="col-md-1">
             <?php echo HTML::button($CLICSHOPPING_QuickUpdate->getDef('button_cancel'), null, $CLICSHOPPING_QuickUpdate->link('QuickUpdate&page=' . $page . '&sort_by=' . $sort_by . '&cPath=' . $current_category_id . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier), 'warning'); ?>
              </span>
         <?php
            }

            if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_COMMERCIAL_MARGIN == 'True') {
           ?>
           <span class="col-md-1">
                <?php echo '&nbsp;&nbsp;&nbsp;&nbsp;' . HTML::checkboxField('marge', 'yes', 'yes', 'no') . '&nbsp;' . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/icon_info.gif', $CLICSHOPPING_QuickUpdate->getDef('text_marge_info')); ?>
           </span>
              <?php
            }
          ?>
        </form>

          <span class="col-md-2">
<?php
  echo HTML::form('update', $CLICSHOPPING_QuickUpdate->link('QuickUpdate&Update'));

  echo HTML::button($CLICSHOPPING_QuickUpdate->getDef('button_update'), null, null, 'success');
  //  HTML::hiddenField($CLICSHOPPING_QuickUpdate->getDef('button_update'), 'Update&cPath=' . $current_category_id . '&page=' . $page . '&sort_by=' . $sort_by . '&row_by_page=' . $row_by_page);
  echo HTML::hiddenField('cPath', $current_category_id);
  echo HTML::hiddenField('page', $page);
  echo HTML::hiddenField('sort_by', $sort_by);
  echo HTML::hiddenField('manufacturer', $manufacturer);
  echo HTML::hiddenField('supplier', $supplier);
  echo HTML::hiddenField('row_by_page', $row_by_page);
  echo HTML::hiddenField('search', $search);
?>
          </span>
        </div>
      </div>
    </div>
  </div>


  <div class="separator"></div>

  <table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
      <!-- body_text //-->
      <td width="100%" valign="top">
        <table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <div class="row col-md-12">
          <span>
<?php
  if (DISPLAY_DOUBLE_TAXE == 'false') {
?>
<script language="javascript">
<!--
switch (browser_family) {
    case 'dom2':
    case 'ie4':
        document.write("<div id='descDiv'>");
        break;
    default:
        document.write("<ilayer id='descDiv'><layer id='descDiv_sub'>");
        break;
}
--></script>
          </span>
              <span>
<script language="javascript">
<!--
switch (browser_family) {
    case 'dom2':
    case 'ie4':
        document.write("<div id='descDiv2'>");
        break;
    default:
        document.write("<ilayer id='descDiv2'><layer id='descDiv_sub2'>");
        break;
}
--></script>

<?php
  }
?>
          </span>
            </div>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
          <?php
            // get the specials products list
            $specials_array = [];

            $Qspecials = $CLICSHOPPING_QuickUpdate->db->prepare('select p.products_id
                                                                  from :table_products p,
                                                                       :table_specials s
                                                                  where s.products_id = p.products_id
                                                                 ');

            $Qspecials->execute();

            while ($Qspecials->fetch()) {
              $specials_array[] = $Qspecials->valueInt('products_id');
            }

            // control string sort page
            if ($sort_by && !preg_match('#order by#', $sort_by)) {
              $sort_by = 'order by ' . $sort_by;
            }

            // define the string parameters for good back preview product
            $origin = 'QuickUpdate' . "?info_back=$sort_by-$page-$current_category_id-$row_by_page-$manufacturer-$supplier";

            if (isset($_POST['page'])) {
              $split_page = (int)$_POST['page'];
            } else {
              $split_page = 0;
            }

            if ($split_page > 1) $rows = $split_page * MAX_DISPLAY_ROW_BY_PAGE - MAX_DISPLAY_ROW_BY_PAGE;

            if ($current_category_id == 0) {

              if (isset($_POST['search'])) {
                $search = HTML::sanitize($_POST['search']);
              }

              if ($manufacturer) {
                $Qproducts = $CLICSHOPPING_QuickUpdate->db->prepare('select  SQL_CALC_FOUND_ROWS  p.products_id,
                                                                                        p.products_percentage,
                                                                                        p.products_image,
                                                                                        p.products_model,
                                                                                        pd.products_name,
                                                                                        p.products_status,
                                                                                        p.products_weight,
                                                                                        p.products_quantity,
                                                                                        p.manufacturers_id,
                                                                                        p.suppliers_id,
                                                                                        p.products_price,
                                                                                        p.products_tax_class_id,
                                                                                        p.products_min_qty_order,
                                                                                        p.products_price_comparison,
                                                                                        p.products_only_online
                                                             from  :table_products p,
                                                                   :table_products_description pd
                                                             where p.products_id = pd.products_id
                                                             and pd.language_id = :language_id
                                                             and p.manufacturers_id = :manufacturers_id
                                                             and products_archive = 0
                                                             and (pd.products_name like :search
                                                                  or  p.products_model like :search
                                                                  or p.products_ean like :search
                                                                 )
                                                             ' . $sort_by . '
                                                             limit :page_set_offset,
                                                                  :page_set_max_results
                                                            ');

                $Qproducts->bindInt(':manufacturers_id', (int)$manufacturer);
                $Qproducts->bindValue(':search', '%' . $search . '%');
                $Qproducts->bindInt(':language_id', $CLICSHOPPING_Language->getId());
                $Qproducts->setPageSet(MAX_DISPLAY_ROW_BY_PAGE);
                $Qproducts->execute();

              } elseif ($supplier) {

                $Qproducts = $CLICSHOPPING_QuickUpdate->db->prepare('select  SQL_CALC_FOUND_ROWS  p.products_id,
                                                                                        p.products_percentage,
                                                                                        p.products_image,
                                                                                        p.products_model,
                                                                                        pd.products_name,
                                                                                        p.products_status,
                                                                                        p.products_weight,
                                                                                        p.products_quantity,
                                                                                        p.manufacturers_id,
                                                                                        p.suppliers_id,
                                                                                        p.products_price,
                                                                                        p.products_tax_class_id,
                                                                                        p.products_min_qty_order,
                                                                                        p.products_price_comparison,
                                                                                        p.products_only_online
                                                             from  :table_products p,
                                                                   :table_products_description pd
                                                             where p.products_id = pd.products_id
                                                             and pd.language_id = :language_id
                                                             and p.suppliers_id = :suppliers_id
                                                             and products_archive = 0
                                                             and (pd.products_name like :search
                                                                  or  p.products_model like :search
                                                                  or p.products_ean like :search
                                                                 )
                                                             ' . $sort_by . '
                                                             limit :page_set_offset,
                                                                  :page_set_max_results
                                                            ');

                $Qproducts->bindInt(':manufacturers_id', $manufacturer);
                $Qproducts->bindInt(':suppliers_id', $supplier);
                $Qproducts->bindValue(':search', '%' . $search . '%');
                $Qproducts->bindInt(':language_id', $CLICSHOPPING_Language->getId());
                $Qproducts->setPageSet(MAX_DISPLAY_ROW_BY_PAGE);
                $Qproducts->execute();
              } else {

                $Qproducts = $CLICSHOPPING_QuickUpdate->db->prepare('select  SQL_CALC_FOUND_ROWS  p.products_id,
                                                                                      p.products_percentage,
                                                                                      p.products_image,
                                                                                      p.products_model,
                                                                                      pd.products_name,
                                                                                      p.products_status,
                                                                                      p.products_weight,
                                                                                      p.products_quantity,
                                                                                      p.manufacturers_id,
                                                                                      p.suppliers_id,
                                                                                      p.products_price,
                                                                                      p.products_tax_class_id,
                                                                                      p.products_min_qty_order,
                                                                                      p.products_price_comparison,
                                                                                      p.products_only_online
                                                       from  :table_products p,
                                                             :table_products_description pd
                                                       where p.products_id = pd.products_id
                                                       and pd.language_id = :language_id
                                                       and products_archive = 0
                                                       and (pd.products_name like :search
                                                            or  p.products_model like :search
                                                            or p.products_ean like :search
                                                           )
                                                       ' . $sort_by . '
                                                       limit :page_set_offset,
                                                            :page_set_max_results
                                                      ');
                $Qproducts->bindValue(':search', '%' . $search . '%');
                $Qproducts->bindInt(':language_id', $CLICSHOPPING_Language->getId());
                $Qproducts->setPageSet(MAX_DISPLAY_ROW_BY_PAGE);
                $Qproducts->execute();
              }
            } else {
              if ($manufacturer) {
                $Qproducts = $CLICSHOPPING_QuickUpdate->db->prepare('select  SQL_CALC_FOUND_ROWS  p.products_id,
                                                                                        p.products_percentage,
                                                                                        p.products_image,
                                                                                        p.products_model,
                                                                                        pd.products_name,
                                                                                        p.products_status,
                                                                                        p.products_weight,
                                                                                        p.products_quantity,
                                                                                        p.manufacturers_id,
                                                                                        p.suppliers_id,
                                                                                        p.products_price,
                                                                                        p.products_tax_class_id,
                                                                                        p.products_min_qty_order,
                                                                                        p.products_price_comparison,
                                                                                         p.products_only_online
                                                         from  :table_products p,
                                                               :table_products_description pd,
                                                               :table_products_to_categories pc
                                                         where p.products_id = pd.products_id
                                                         and pd.language_id = :language_id
                                                         and p.products_id = pc.products_id
                                                         and pc.categories_id = :categories_id
                                                         and p.manufacturers_id = :manufacturers_id
                                                         and products_archive = 0
                                                         and (pd.products_name like :search
                                                              or  p.products_model like :search
                                                              or p.products_ean like :search
                                                             )
                                                         ' . $sort_by . '
                                                         limit :page_set_offset,
                                                              :page_set_max_results
                                                        ');

                $Qproducts->bindInt(':manufacturers_id', $manufacturer);
                $Qproducts->bindInt(':categories_id', $current_category_id);
                $Qproducts->bindValue(':search', '%' . $search . '%');
                $Qproducts->bindInt(':language_id', $CLICSHOPPING_Language->getId());
                $Qproducts->setPageSet(MAX_DISPLAY_ROW_BY_PAGE);
                $Qproducts->execute();

              } elseif ($supplier) {
                $Qproducts = $CLICSHOPPING_QuickUpdate->db->prepare('select  SQL_CALC_FOUND_ROWS  p.products_id,
                                                                                      p.products_percentage,
                                                                                      p.products_image,
                                                                                      p.products_model,
                                                                                      pd.products_name,
                                                                                      p.products_status,
                                                                                      p.products_weight,
                                                                                      p.products_quantity,
                                                                                      p.manufacturers_id,
                                                                                      p.suppliers_id,
                                                                                      p.products_price,
                                                                                      p.products_tax_class_id,
                                                                                      p.products_min_qty_order,
                                                                                      p.products_price_comparison,
                                                                                      p.products_only_online
                                                     from  :table_products p,
                                                           :table_products_description pd,
                                                           :table_products_to_categories pc
                                                     where p.products_id = pd.products_id
                                                     and pd.language_id = :language_id
                                                     and p.products_id = pc.products_id
                                                     and pc.categories_id = :categories_id
                                                     and p.suppliers_id = :suppliers_id
                                                     and products_archive = 0
                                                     and (pd.products_name like :search
                                                          or  p.products_model like :search
                                                          or p.products_ean like :search
                                                         )
                                                     ' . $sort_by . '
                                                     limit :page_set_offset,
                                                          :page_set_max_results
                                                    ');
                $Qproducts->bindInt(':suppliers_id', $supplier);
                $Qproducts->bindInt(':categories_id', $current_category_id);
                $Qproducts->bindValue(':search', '%' . $search . '%');
                $Qproducts->bindInt(':language_id', $CLICSHOPPING_Language->getId());
                $Qproducts->setPageSet(MAX_DISPLAY_ROW_BY_PAGE);
                $Qproducts->execute();
              } else {

                $Qproducts = $CLICSHOPPING_QuickUpdate->db->prepare('select  SQL_CALC_FOUND_ROWS p.products_id,
                                                                                      p.products_percentage,
                                                                                      p.products_image,
                                                                                      p.products_model,
                                                                                      pd.products_name,
                                                                                      p.products_status,
                                                                                      p.products_weight,
                                                                                      p.products_quantity,
                                                                                      p.manufacturers_id,
                                                                                      p.suppliers_id,
                                                                                      p.products_price,
                                                                                      p.products_tax_class_id ,
                                                                                      p.products_min_qty_order,
                                                                                      p.products_price_comparison,
                                                                                      p.products_only_online
                                                     from  :table_products p,
                                                           :table_products_description pd,
                                                           :table_products_to_categories pc
                                                     where p.products_id = pd.products_id
                                                     and pd.language_id = :language_id
                                                     and p.products_id = pc.products_id
                                                     and pc.categories_id = :categories_id
                                                     and pc.categories_id = :categories_id
                                                     and products_archive = 0
                                                     and (pd.products_name like :search
                                                          or  p.products_model like :search
                                                          or p.products_ean like :search
                                                         )
                                                     ' . $sort_by . '
                                                     limit :page_set_offset,
                                                          :page_set_max_results
                                                    ');

                $Qproducts->bindInt(':categories_id', $current_category_id);
                $Qproducts->bindValue(':search', '%' . $search . '%');
                $Qproducts->bindInt(':language_id', $CLICSHOPPING_Language->getId());
                $Qproducts->setPageSet(MAX_DISPLAY_ROW_BY_PAGE);
                $Qproducts->execute();

              }
            }

            $listingTotalRow = $Qproducts->getPageSetTotalRows();
          ?>
          <tr>
            <td class="text-center">
              <table width="100%" cellspacing="0" cellpadding="0" border="1" bgcolor="#F3F9FB" bordercolor="#D1E7EF"
                     height="100" valgin="top">
                <tr>
                  <td>
                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr>
                        <td valign="top">
                          <table border="0" width="100%" cellspacing="0" cellpadding="2">
                            <tr class="dataTableHeadingRow">
                              <td valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_PREVIEW_IMAGE == 'True') {
                                    ?>
                                    <td></td>
                                  </tr>
                                  <?php
                                    }
                                  ?>
                                </table>
                              </td>
                              <td class="text-center" valign="middle" height="25">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MODEL == 'True') {
                                        if ($sort_by == 'order by p.products_model DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_model ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-center"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_model ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_model') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_model DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_model') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td class="text-center"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_model'); ?></td>

                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if ($sort_by == 'order by pd.products_name DESC') {
                                        $icon_up = 'icon_up.gif';
                                        $icon_down = 'icon_down_active.gif';
                                      } else if ($sort_by == 'order by pd.products_name ASC') {
                                        $icon_up = 'icon_up_active.gif';
                                        $icon_down = 'icon_down.gif';
                                      } else {
                                        $icon_up = 'icon_up.gif';
                                        $icon_down = 'icon_down.gif';
                                      }
                                    ?>
                                    <td class="text-center"
                                        valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=pd.products_name ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_products') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=pd.products_name DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_products') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                    <td class="text-center"
                                        valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_products'); ?></td>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_PRODUCT_STATUS == 'True') {
                                        if ($sort_by == 'order by p.products_status desc') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_status asc') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-center"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_status ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . 'OFF ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_status DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . 'ON ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a>"; ?></td>
                                        <td class="text-center"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_status'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_PRICE_COMPARISON == 'True') {
                                        if ($sort_by == 'order by p.products_price_comparison DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_price_comparison ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-center"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_price_comparison ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . 'OFF ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_price_comparison DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . 'ON ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a>"; ?></td>
                                        <td class="text-center"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_price_comparison'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_ONLY_ONLINE == 'True') {
                                        if ($sort_by == 'order by p.products_only_online DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_only_online ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-center"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_only_online ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . 'OFF ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_only_online DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . 'ON ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a>"; ?></td>
                                        <td class="text-center"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_products_only_online'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_WEIGHT == 'True') {
                                        if ($sort_by == 'order by p.products_weight DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_weight ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-center"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_weight ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_weight') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_weight DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_weight') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td class="text-center"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_weight'); ?></td>

                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_STOCK == 'True') {
                                        if ($sort_by == 'order by p.products_quantity DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_quantity ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-end"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_quantity ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_quantity') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_quantity DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_quantity') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td class="text-md-cright"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_quantity'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MIN_ORDER == 'True') {
                                        if ($sort_by == 'order by p.products_min_qty_order DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_min_qty_order ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-center"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_min_qty_order ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_min_order_quantity') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_min_qty_order DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_min_order_quantity') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td class="text-end"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_min_order_quantity'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_IMAGE == 'True') {
                                        if ($sort_by == 'order by p.products_image DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_image ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td
                                          valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_image ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_image') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_image DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_image') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td
                                          valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_image'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MANUFACTURER == 'True') {
                                        if ($sort_by == 'order by manufacturers_id DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by manufacturers_id ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td
                                          valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=manufacturers_id ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_manufacturers') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=manufacturers_id DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_manufacturers') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td
                                          valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_manufacturers'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_SUPPLIER == 'True') {
                                        if ($sort_by == 'order by suppliers_id DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by suppliers_id ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td
                                          valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=suppliers_id ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_suppliers') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=suppliers_id DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_suppliers') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td
                                          valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_suppliers'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td
                                      class="text-center"><?php echo $CLICSHOPPING_QuickUpdate->getDef('table_heading_price'); ?></td>
                                  </tr>
                                </table>
                              </td>
                              <?php
                                // Permettre le changement de groupe en mode B2B
                                if (MODE_B2B_B2C == 'true') {
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_B2B == 'True') {

                                    $QcustomersGroup = $CLICSHOPPING_QuickUpdate->db->prepare('select customers_group_name
                                                          from :table_customers_groups
                                                          where customers_group_id > 0
                                                          order by customers_group_id
                                                         ');
                                    $QcustomersGroup->execute();

                                    while ($QcustomersGroup->fetch()) {
                                      if ($QcustomersGroup->rowCount() > 0) {
                                        ?>
                                        <td class="text-end" valign="middle">
                                          <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                              <td class="text-end"
                                                  valign="middle"><?php echo $QcustomersGroup->value('customers_group_name'); ?></td>
                                            </tr>
                                          </table>
                                        </td>
                                        <?php
                                      }
                                    }
                                  }
                                }
                              ?>
                              <td class="text-center" valign="middle">
                                <table border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <?php
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_TAX == 'True') {
                                        if ($sort_by == 'order by p.products_tax_class_id DESC') {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down_active.gif';
                                        } else if ($sort_by == 'order by p.products_tax_class_id ASC') {
                                          $icon_up = 'icon_up_active.gif';
                                          $icon_down = 'icon_down.gif';
                                        } else {
                                          $icon_up = 'icon_up.gif';
                                          $icon_down = 'icon_down.gif';
                                        }
                                        ?>
                                        <td class="text-center"
                                            valign="middle"><?php echo "<a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_tax_class_id ASC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_up, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_tax') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_ascendingly')) . "</a><a href=\"" . $CLICSHOPPING_QuickUpdate->link('QuickUpdate&cPath=' . $current_category_id . '&sort_by=p.products_tax_class_id DESC&page=' . $page . '&row_by_page=' . $row_by_page . '&manufacturer=' . $manufacturer . '&supplier=' . $supplier) . "\" >" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/' . $icon_down, $CLICSHOPPING_QuickUpdate->getDef('text_sort_all') . $CLICSHOPPING_QuickUpdate->getDef('table_heading_tax') . ' ' . $CLICSHOPPING_QuickUpdate->getDef('text_descendingly')) . "</a>"; ?></td>
                                        <td class="text-center"
                                            valign="middle"><?php echo '&nbsp;' . $CLICSHOPPING_QuickUpdate->getDef('table_heading_tax'); ?></td>
                                        <?php
                                      }
                                    ?>
                                  </tr>
                                </table>
                              </td>
                              <td class="text-center" valign="middle"></td>
                              <td class="text-center" valign="middle"></td>
                            </tr>
                            <tr class="datatableRow">
                              <?php
                                $rows = 0;

                                while ($products = $Qproducts->fetch()) {
                                  $rows++;

                                  if (strlen($rows) < 2) {
                                    $rows = '0' . $rows;
                                  }

// check for global add value or rates, calcul and round values rates
                                  if (isset($_POST['spec_price'])) {
                                    $flag_spec = 'true';
                                    $spec_price = HTML::sanitize($_POST['spec_price']);

                                    if (substr($spec_price, -1) == '%') {
                                      if (isset($_POST['marge']) && substr($spec_price, 0, 1) != '-') {
                                        $value = (1 - (preg_replace("#%#", '', $spec_price) / 100));


                                        $price = sprintf("%01.2f", round($products['products_price'] / $value, 2));
                                      } else {
                                        $price = sprintf("%01.2f", round($products['products_price'] + (($spec_price / 100) * $products['products_price']), 2));
                                      }
                                    } else {
                                      $price = sprintf("%01.2f", round($products['products_price'] + $spec_price, 2));
                                    }
                                  } else {
                                    $price = sprintf("%01.2f", round($products['products_price'], 2));
                                  }


// Check Tax_rate for displaying TTC
                                  $Qtax = $CLICSHOPPING_QuickUpdate->db->prepare('select r.tax_rate,
                                                           c.tax_class_title
                                                    from :table_tax_rates r,
                                                         :table_tax_class c
                                                    where r.tax_class_id = :tax_class_id
                                                    and c.tax_class_id = :tax_class_id
                                                   ');
                                  $Qtax->bindInt(':tax_class_id', $products['products_tax_class_id']);

                                  $Qtax->execute();

                                  if (!is_bool($Qtax->valueDecimal('tax_rate'))) $tax_rate = 0;


                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_MANUFACTURER == 'False') {

                                    $Qmanufacturer = $CLICSHOPPING_QuickUpdate->db->prepare('select manufacturers_name
                                                        from :table_manufacturers
                                                        where manufacturers_id = :manufacturers_id
                                                       ');
                                    $Qmanufacturer->bindInt(':manufacturers_id', (int)$products['manufacturers_id']);
                                    $Qmanufacturer->execute();

                                    $manufacturer = $Qmanufacturer->fetch();

                                  }

                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_SUPPLIER == 'False') {

                                    $Qsupplier = $CLICSHOPPING_QuickUpdate->db->prepare('select suppliers_name
                                                    from :table_suppliers
                                                    where suppliers_id = :suppliers_id
                                                   ');
                                    $Qsupplier->bindInt(':suppliers_id', (int)$products['suppliers_id']);
                                    $Qsupplier->execute();

                                    $supplier = $Qsupplier->fetch();
                                  }

// display infos per row
                                  if (isset($flag_spec)) {
                                    echo '<tr onmouseover="';
                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_TAX_MOUSE_OVER == 'True') {
                                      echo 'display_ttc(\'display\', ' . $price . ', ' . $tax_rate . ');';
                                    }
                                    echo 'this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="';

                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_TAX_MOUSE_OVER == 'True') {
                                      echo 'display_ttc(\'delete\');';
                                    }
                                    echo 'this.className=\'dataTableRow\'">';
                                  } else {
                                    echo '<tr onmouseover="';
                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_TAX_MOUSE_OVER == 'True') {
                                      echo 'display_ttc(\'display\', ' . $products['products_price'] . ', ' . $tax_rate . ');';
                                    }
                                    echo 'this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="';
                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_TAX_MOUSE_OVER == 'True') {
                                      echo 'display_ttc(\'delete\', \'\', \'\', 0);';
                                    }
                                    echo 'this.className=\'dataTableRow\'">';
                                  }

// Affiche l'image
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_PREVIEW_IMAGE == 'True') {
                                    echo "<td>" . HTML::image($CLICSHOPPING_Template->getDirectoryShopTemplateImages() . $products['products_image'], $products['products_name'], (int)SMALL_IMAGE_WIDTH_ADMIN, (int)SMALL_IMAGE_HEIGHT_ADMIN) . "</td>";
                                  } else {
                                    echo '<td>';
                                  }

// Affiche la reference du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MODEL == 'True') {
                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_MODEL == 'True') {
                                      echo "<td class=\"text-center\"><input type=\"text\" size=\"10\" name=\"product_new_model[" . $products['products_id'] . "]\" value=\"" . $products['products_model'] . "\"></td>";
                                    } else {
                                      echo "<td>" . $products['products_model'] . "</td>";
                                    }
                                  } else {
                                    echo "<td>";
                                  }

// Affiche le nom du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_PRODUCTS_NAME == 'true') {
                                    echo "<td class=\"text-center\"><input type=\"text\" size=\"20\" name=\"product_new_name[" . $products['products_id'] . "]\" value=\"" . $products['products_name'] . "\"></td>";
                                  } else {
                                    echo "<td>" . $products['products_name'] . "</td>";
                                  }

// Affiche le statut du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_PRODUCT_STATUS == 'True') {
                                    if ($products['products_status'] == '1') {
                                      echo "<td class=\"text-center\"><input type=\"radio\" name=\"product_new_status[" . $products['products_id'] . "]\" value=\"0\" ><input type=\"radio\" name=\"product_new_status[" . $products['products_id'] . "]\" value=\"1\" checked ></td>";
                                    } else {
                                      echo "<td class=\"text-center\"><input type=\"radio\" style=\"background-color: #EEEEEE\" name=\"product_new_status[" . $products['products_id'] . "]\" value=\"0\" checked ><input type=\"radio\" style=\"background-color: #EEEEEE\" name=\"product_new_status[" . $products['products_id'] . "]\" value=\"1\"></td>";
                                    }
                                  } else {
                                    echo "<td class=\"text-center\"></td>";
                                  }

// Affiche le comparateur de prix du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_PRICE_COMPARISON == 'True') {
                                    if ($products['products_price_comparison'] == '1') {
                                      echo "<td class=\"text-center\"><input type=\"radio\" name=\"product_new_products_price_comparison[" . $products['products_id'] . "]\" value=\"0\" ><input type=\"radio\" name=\"product_new_products_price_comparison[" . $products['products_id'] . "]\" value=\"1\" checked ></td>";
                                    } else {
                                      echo "<td class=\"text-center\"><input type=\"radio\" style=\"background-color: #EEEEEE\" name=\"product_new_products_price_comparison[" . $products['products_id'] . "]\" value=\"0\" checked ><input type=\"radio\" style=\"background-color: #EEEEEE\" name=\"product_new_products_price_comparison[" . $products['products_id'] . "]\" value=\"1\"></td>";
                                    }
                                  } else {
                                    echo "<td class=\"text-center\"></td>";
                                  }

// Affiche le comparateur de prix du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_ONLY_ONLINE == 'true') {
                                    if ($products['products_only_online'] == '1') {
                                      echo "<td class=\"text-center\"><input type=\"radio\" name=\"product_new_products_only_online[" . $products['products_id'] . "]\" value=\"0\" ><input type=\"radio\" name=\"product_new_products_only_online[" . $products['products_id'] . "]\" value=\"1\" checked ></td>";
                                    } else {
                                      echo "<td class=\"text-center\"><input type=\"radio\" style=\"background-color: #EEEEEE\" name=\"product_new_products_only_online[" . $products['products_id'] . "]\" value=\"0\" checked ><input type=\"radio\" style=\"background-color: #EEEEEE\" name=\"product_new_products_only_online[" . $products['products_id'] . "]\" value=\"1\"></td>";
                                    }
                                  } else {
                                    echo "<td class=\"text-center\"></td>";
                                  }

// Affiche le poids du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_WEIGHT == 'True') {
                                    echo "<td class=\"text-center\"><input type=\"text\" size=\"5\" name=\"product_new_weight[" . $products['products_id'] . "]\" value=\"" . $products['products_weight'] . "\"></td>";
                                  } else {
                                    echo "<td class=\"text-center\"></td>";
                                  }

// Affiche la quantite du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_STOCK == 'True') {
                                    echo "<td class=\"text-center\"><input type=\"text\" size=\"3\" name=\"product_new_quantity[" . $products['products_id'] . "]\" value=\"" . $products['products_quantity'] . "\"></td>";
                                  } else {
                                    echo "<td class=\"text-center\"></td>";
                                  }

// Affiche la quantite minimum de commande du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MIN_ORDER == 'True') {
                                    echo "<td class=\"text-center\"><input type=\"text\" size=\"3\" name=\"new_products_min_qty_order[" . $products['products_id'] . "]\" value=\"" . $products['products_min_qty_order'] . "\"></td>";
                                  } else {
                                    echo "<td  class=\"text-center\"></td>";
                                  }

// Affiche l'images du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_IMAGE == 'True') {
                                    echo "<td  class=\"text-center\"><input type=\"text\" size=\"8\" name=\"product_new_image[" . $products['products_id'] . "]\" value=\"" . $products['products_image'] . "\"></td>";
                                  } else {
                                    echo "<td  class=\"text-center\"></td>";
                                  }

// Affiche le fabricant du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MANUFACTURER == 'True') {
                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_MANUFACTURER == 'True') {
                                      echo '<td  class="text-center">' . HTML::selectMenu('product_new_manufacturer[' . $products['products_id'] . ']', $manufacturers_array, $products['manufacturers_id']) . '</td>';
                                    } else {
                                      echo '<td  class="text-center">' . $manufacturer['manufacturers_name'] . '</td>';
                                    }
                                  } else {
                                    echo "<td  class=\"text-center\"></td>";
                                  }

// Affiche le fabricant du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_SUPPLIER == 'True') {
                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_SUPPLIER == 'True') {
                                      echo "<td class=\"text-center\">" . HTML::selectMenu("product_new_supplier[" . $products['products_id'] . "]\"", $suppliers_array, $products['suppliers_id']) . "</td>";
                                    } else {
                                      echo "<td class=\"text-center\">" . $supplier['suppliers_name'] . "</td>";
                                    }
                                  } else {
                                    echo "<td class=\"text-center\"></td>";
                                  }

// Affiche le prix du produit
                                  if (in_array($products['products_id'], $specials_array)) {
                                    echo "<td class=\"text-end\">&nbsp;&nbsp;&nbsp;<a href=\"" . CLICSHOPPING::link(null, '?A&Marketing\Specials&Specials') . "\">" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/specials.gif', $CLICSHOPPING_QuickUpdate->getDef('text_quick_updates_specials')) . "</a>&nbsp;<input type=\"text\" size=\"6\" name=\"product_new_price[" . $products['products_id'] . "]\" value=\"" . $products['products_price'] . "\" disabled >" . $CLICSHOPPING_QuickUpdate->getDef('text_no_taxe') . "</td>";
                                  } elseif ($products['products_percentage'] == '0') {


//      echo "<td class=\"text-end\">&nbsp;&nbsp;&nbsp;<a href=\"" . CLICSHOPPING::link(null, '?A&Catalog\Products&Update&pID=' . $products['products_id'] . '&cPath=' . $categories_products[0]) . "\">" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/locked.gif', $CLICSHOPPING_QuickUpdate->getDef('text_quick_updates_locked')) . "</a>&nbsp;<input type=\"text\" size=\"6\" name=\"product_new_price[" . $products['products_id'] . "]\" value=\"" . $products['products_price'] . "\" disabled >" . $CLICSHOPPING_QuickUpdate->getDef('text_no_taxe') . "</td>";


                                  } else {
                                    if (isset($flag_spec)) {
                                      echo "<td class=\"text-end\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" size=\"6\" name=\"product_new_price[" . $products['products_id'] . "]\" ";
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MOUSE_OVER == 'True') {
                                        echo "onKeyUp=\"display_ttc('keyup', this.value" . ", " . $tax_rate . ", 1);\"";
                                      }
                                      echo " value=\"" . $price . "\">" . HTML::checkboxField('update_price[' . $products['products_id'] . ']', 'yes', 'yes', 'no') . $CLICSHOPPING_QuickUpdate->getDef('text_no_taxe') . "</td>";
                                    } else {
                                      echo "<td class=\"text-end\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" size=\"6\" name=\"product_new_price[" . $products['products_id'] . "]\" ";
                                      if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_MOUSE_OVER == 'True') {
                                        echo "onKeyUp=\"display_ttc('keyup', this.value" . ", " . $tax_rate . ", 1);\"";
                                      }
                                      echo " value=\"" . $price . "\">" . HTML::hiddenField('update_price[' . $products['products_id'] . ']', 'yes') . $CLICSHOPPING_QuickUpdate->getDef('text_no_taxe') . "</td>";
                                    }
                                  }


                if (MODE_B2B_B2C == 'true') {
                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_B2B == 'True') {
                    $QcustomersGroup = $CLICSHOPPING_QuickUpdate->db->prepare('select customers_group_id,
                                                                                      customers_group_name,
                                                                                      customers_group_discount
                                                                                from :table_customers_groups
                                                                                where customers_group_id > 0
                                                                                order by customers_group_id
                                                                               ');
                    $QcustomersGroup->execute();

                    $header = false;

                    while ($QcustomersGroup->fetch()) {
                      if (!$header) {
                        $header = true;
                      }

                      if ($QcustomersGroup->rowCount() > 0) {

                        $Qattributes = $CLICSHOPPING_QuickUpdate->db->prepare('select customers_group_id,
                                                                                        customers_group_price
                                                                                from :table_products_groups
                                                                                where products_id = :products_id
                                                                                and customers_group_id = :customers_group_id
                                                                                order by customers_group_id
                                                                                ');
                        $Qattributes->bindInt(':products_id', $products['products_id']);
                        $Qattributes->bindInt(':customers_group_id', $QcustomersGroup->valueInt('customers_group_id'));
                        $Qattributes->execute();
                      }

                      if ($Qattributes->fetch()) {

                        $price2 = $Qattributes->valueDecimal('customers_group_price');

                        if (empty($price2)) {
                          echo '<td class="text-end">' . $CLICSHOPPING_QuickUpdate->getDef('no_price') . '</td>';
                        } else {
                          echo '<td class="text-end">' . $price2 . $CLICSHOPPING_QuickUpdate->getDef('text_no_taxe') . '</td>';
                        }
                      } else {
                        echo '<td class="text-end">' . $CLICSHOPPING_QuickUpdate->getDef('no_price') . '</td>';
                      }
                    }
                  }
                }

// Affichage de la TVA
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_TAX == 'True') {
                                    if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_TAX == 'True') {
                                      echo "<td class=\"text-end\">" . HTML::selectMenu("product_new_tax[" . $products['products_id'] . "]\"", $tax_class_array, $products['products_tax_class_id']) . "</td>";
                                    } else {


                                      echo "<td class=\"text-end\">" . $tax_rate['tax_class_title'] . "</td>";


                                    }
                                  } else {
                                    echo "<td class=\"text-end\"></td>";
                                  }

// Afficher la visluation du produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_PREVIEW == 'True') {
                                    echo "<td  align=\"left\"><a href=\"" . CLICSHOPPING::link(null, 'A&Catalog\Products&Preview&pID=' . $products['products_id']) . "\">" . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/preview.gif', $CLICSHOPPING_QuickUpdate->getDef('text_image_preview')) . "</a></td>";
                                  }

// Editer la fiche produit
                                  if (CLICSHOPPING_APP_QUICKUPDATE_CQ_DISPLAY_EDIT_PRODUCT == 'True') {


//      echo '<td  align="left"><a href="' . CLICSHOPPING::link(null, 'A&Catalog\Products&Edit&pID=' . $products['products_id'] . '&cPath=' . $categories_products[0]) . '">' . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/products.gif', $CLICSHOPPING_QuickUpdate->getDef('text_image_switch_edit')) ."</a></td>';


                }

// Hidden parameters for cache old values
                if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_PRODUCTS_NAME == 'True') echo HTML::hiddenField('product_old_name[' . $products['products_id'] . '] ', $products['products_name']);
                if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_MODEL == 'True') echo HTML::hiddenField('product_old_model[' . $products['products_id'] . '] ', $products['products_model']);
                echo HTML::hiddenField('product_old_status[' . $products['products_id'] . ']', $products['products_status']);
                echo HTML::hiddenField('product_old_products_price_comparison[' . $products['products_id'] . ']', $products['products_price_comparison']);
                echo HTML::hiddenField('product_old_products_only_online[' . $products['products_id'] . ']', $products['products_only_online']);
                echo HTML::hiddenField('product_old_quantity[' . $products['products_id'] . ']', $products['products_quantity']);
                echo HTML::hiddenField('products_old_min_qty_order[' . $products['products_id'] . ']', $products['products_min_qty_order']);
                echo HTML::hiddenField('product_old_image[' . $products['products_id'] . ']', $products['products_image']);
                if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_MANUFACTURER == 'True') echo HTML::hiddenField('product_old_manufacturer[' . $products['products_id'] . ']', $products['manufacturers_id']);
                if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_SUPPLIER == 'True') echo HTML::hiddenField('product_old_supplier[' . $products['products_id'] . ']', $products['suppliers_id']);
                echo HTML::hiddenField('product_old_weight[' . $products['products_id'] . ']', $products['products_weight']);
                echo HTML::hiddenField('product_old_price[' . $products['products_id'] . ']', $products['products_price']);
                if (CLICSHOPPING_APP_QUICKUPDATE_CQ_UPDATE_TAX == 'True') echo HTML::hiddenField('product_old_tax[' . $products['products_id'] . ']', $products['products_tax_class_id']);

// hidden display parameters
                echo HTML::hiddenField('row_by_page', $row_by_page);
                echo HTML::hiddenField('sort_by', $sort_by);
                echo HTML::hiddenField('page', $split_page);
              }
            ?>
            </tr>
                          </table>
                        </td>
                      </tr>
                      </form>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <div class="separator"></div>
  <div class="row">
    <div class="col-md-12">
      <div
        class="col-md-6 float-start pagenumber hidden-xs TextDisplayNumberOfLink"><?php echo $Qproducts->getPageSetLabel($CLICSHOPPING_QuickUpdate->getDef('text_display_number_of_link')); ?></div>
      <div
        class="float-end text-end"> <?php echo $Qproducts->getPageSetLinks(CLICSHOPPING::getAllGET(array('page', 'info', 'x', 'y'))); ?></div>
    </div>
  </div>
</div>