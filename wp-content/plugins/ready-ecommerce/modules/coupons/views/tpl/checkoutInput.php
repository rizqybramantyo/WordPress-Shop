<script type="text/javascript">
<!--
function toeApplyCoupon() {
    jQuery(this).sendForm({
        msgElID: 'toeCouponsCheckoutMsg',
        data: {coupon: jQuery('#toeCoupon').val(), mod: 'coupons', action: 'applyCoupon', reqType: 'ajax'},
        onSuccess: function(res) {
            if(res.data.totalHtml) {
                updateCart(new Array(), res.data.totalHtml);
            }
            if(!res.error) {
                jQuery('#toeCoupon').val('');
            }
        }
    });
}
-->
</script>
<?php lang::_e('Enter coupon code here')?>: 
<?php echo html::text('coupon', array('attrs' => 'id="toeCoupon"'))?>
<?php echo html::button(array('value' => lang::_('Apply'), 'attrs' => 'onclick="toeApplyCoupon();"'))?>
<div id="toeCouponsCheckoutMsg"></div>