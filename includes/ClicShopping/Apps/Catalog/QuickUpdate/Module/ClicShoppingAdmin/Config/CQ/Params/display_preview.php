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

  namespace ClicShopping\Apps\Catalog\QuickUpdate\Module\ClicShoppingAdmin\Config\CQ\Params;

  use ClicShopping\OM\HTML;

  class display_preview extends \ClicShopping\Apps\Catalog\QuickUpdate\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {
    public $default = 'False';
    public $sort_order = 160;

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_quick_update_display_preview_title');
      $this->description = $this->app->getDef('cfg_quick_update_display_preview_description');
    }

    public function getInputField()
    {
      $value = $this->getInputValue();

      $input = HTML::radioField($this->key, 'True', $value, 'id="' . $this->key . '1" autocomplete="off"') . $this->app->getDef('cfg_quick_update_display_preview_true') . ' ';
      $input .= HTML::radioField($this->key, 'False', $value, 'id="' . $this->key . '2" autocomplete="off"') . $this->app->getDef('cfg_quick_update_display_preview_false');

      return $input;
    }
  }