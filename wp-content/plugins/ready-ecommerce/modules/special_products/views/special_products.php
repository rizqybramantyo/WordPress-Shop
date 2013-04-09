<?php
class special_productsView extends view {
    /**
     * Get the content for special products module tab
     * 
     * @return type 
     */
   public function getTabContent(){
       $specialProducts = frame::_()->getModule('special_products')->getModel()->get();
       $this->assign('spFields', frame::_()->getTable('special_products')->getFields());
       $this->assign('specialProducts', $specialProducts);
       return parent::getContent('tab');
   }
}
?>
