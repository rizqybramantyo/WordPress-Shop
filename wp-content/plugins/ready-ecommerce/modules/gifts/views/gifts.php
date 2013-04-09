<?php
class giftsView extends view {
    /**
     * Get the content for gifts module tab
     * 
     * @return type 
     */
   public function getTabContent() {
       $this->assign('allGifts', frame::_()->getModule('gifts')->getModel()->get());
       $this->assign('conditions', frame::_()->getTable('gifts_conditions')->get('*'));
       return parent::getContent('giftsTab');
   }
   public function getFreeProductLink($gift) {
       $this->assign('gift', $gift);
       return parent::getContent('freeProductLink');
   }
   public function selectProduct($id) {
       $gift = frame::_()->getModule('gifts')->getCurrent(array('id' => $id));
       if($gift) {
           $productsId = $gift[0]['type_params']['product'];
           if(!empty($productsId)) {
               $products = frame::_()->getModule('products')->getModel()->get(frame::_()->getTable('products')->alias(). '.post_id IN ('. implode(', ', $productsId). ')');
               if(!empty($products)) {
                    $this->assign('products', $products);
                    $this->assign('gift', $gift[0]);
                    return parent::getContent('selectProduct');
               }
           }
       }
       return false;
   }
   public function checkoutDisplay() {
       $checkout= frame::_()->getModule('checkout')->calculate(array('subtotal', 'gifts'));
       if(!empty($checkout['gifts']['data'])) {
           $this->assign('gifts', $checkout['gifts']);
           return parent::getContent('checkoutDisplay');
       }
       return false;
   }
   public function addProdOrderDisplayData($content, $oid, $prod) {
       if(!empty($prod['product_params']['gifts']) || !empty($prod['product_params']['gift'])) {
           $content .= parent::getContent('prodOrderData');
       }
       return $content;
   }
   public function addGiftsDataToOrder($content, $oid) {
       $data = frame::_()->getTable('gifts_to_orders')->get('*', array('order_id' => $oid));
       if(!empty($data[0])) {
           $data[0]['gift_data'] = utils::jsonDecode($data[0]['gift_data']);
           $this->assign('gifts', $data[0]['gift_data']);
           $content .= parent::getContent('checkoutDisplay');
       }
       return $content;
   }
}
?>
