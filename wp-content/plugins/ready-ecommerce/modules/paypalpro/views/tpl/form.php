<script type="text/javascript">
<!--
    jQuery(document).ready(function(){
        jQuery('#toePayPalProForm').submit(function(){
            jQuery(this).sendForm({
                msgElID: 'toePayPalproMsg',
                onSuccess: function(res){
                    if(res.html) {
                        jQuery('#toe_checkout_content').slideToggle('slow', function(){
                           jQuery(this).html(res.html);
                           jQuery(this).slideToggle('slow');
                        });
                    }
                }
            });
            return false;
        });
    });
-->
</script>
<h1><?php lang::_e('Payment Info')?></h1>
<form method="post" id="toePayPalProForm">
    <table>
        <tr>
            <td><?php lang::_e('Card Owner')?>: </td>
            <td><?php $this->fields['cc_owner']->display()?></td>
        </tr>
        <tr>
            <td><?php lang::_e('Card Type')?>: </td> 
            <td><?php $this->fields['cc_type']->display()?></td>
        </tr>
        <tr>
            <td><?php lang::_e('Card Number')?>: </td> 
            <td><?php $this->fields['cc_number_nh-dns']->display()?></td>
        </tr>
        <tr>
            <td><?php lang::_e('Card Valid From Date')?>: </td> 
            <td><?php $this->fields['cc_starts_month']->display()?> <?php $this->fields['cc_starts_year']->display()?> <?php lang::_e('(if available)')?></td>
        </tr>
        <tr>
            <td><?php lang::_e('Card Expiry Date')?>: </td> 
            <td><?php $this->fields['cc_expires_month']->display()?> <?php $this->fields['cc_expires_year']->display()?></td>
        </tr>
        <tr>
            <td><?php lang::_e('Card Security Code (CVV2)')?>: </td> 
            <td><?php $this->fields['cc_cvc_nh-dns']->display()?></td>
        </tr>
        <tr>
            <td><?php lang::_e('Card Issue Number')?>: </td>
            <td><?php $this->fields['cc_issue_nh-dns']->display()?> <?php lang::_e('(for Maestro and Solo cards only)')?></td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo html::hidden('reqType', array('value' => 'ajax'))?>
                <?php echo html::hidden('mod', array('value' => 'paypalpro'))?>
                <?php echo html::hidden('action', array('value' => 'sendPayments'))?>
                <?php echo html::submit('pay', array('value' => lang::_('Pay Now')))?>
            </td>
        </tr>
        <tr>
            <td colspan="2" id="toePayPalproMsg"></td>
        </tr>
        </table>
</form>