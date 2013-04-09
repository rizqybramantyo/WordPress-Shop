<?php
class couponsView extends view {
    /**
     * Get the content for special products module tab
     * 
     * @return type 
     */
   public function getTabContent(){
       if(frame::_()->getModule('gifts')) {
           $fields = frame::_()->getTable('coupons_patterns')->getFields();
           $gifts = frame::_()->getModule('gifts')->getModel()->get();
           if(empty($gifts)) {
               $fields['gifts']->setHtml('block');
               $fields['gifts']->setValue(lang::_('No gifts found'));
           } else {
               $giftsOptions = array();
               foreach($gifts as $g) {
                   $giftsOptions[$g['id']] = $g['label'];
               }
               $fields['gifts']->addHtmlParam('options', $giftsOptions);
           }
           $fields['date_from']->addHtmlParam('id', 'coupons_date_from');
           $fields['date_to']->addHtmlParam('id', 'coupons_date_to');
           unset($fields['id']);
           $allCouponsPatterns = frame::_()->getModule('coupons')->getModel('coupons_patterns')->get();
           $allCoupons = frame::_()->getModule('coupons')->getModel()->get();
           //Let's sort coupons by pattern_id
           if(!empty($allCoupons)) {
               $temp = $allCoupons;
               $allCoupons = array();
               foreach($temp as $c) {
                   $allCoupons[$c['pattern_id']][] = $c;
               } 
           }
           //****
           $this->assign('allCouponsPatterns', $allCouponsPatterns);
           $this->assign('allCoupons', $allCoupons);
           $this->assign('fields', $fields);
           return parent::getContent('couponsAdminTab');
       } else 
           return '';
   }
   public function checkoutDisplay($content) {
       return $content. parent::getContent('checkoutInput');
   }
}
?>
