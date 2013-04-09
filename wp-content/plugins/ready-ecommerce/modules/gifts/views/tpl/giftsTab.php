<script type="text/javascript">
<!--
jQuery(document).ready(function(){
    toeTables['toe_gifts_list'] = new toeListTable('toe_gifts_list', <?php echo utils::jsonEncode($this->allGifts)?>);
    toeTables['toe_gifts_list'].draw();
    
    jQuery('#toeGiftsConditions').buttonset();
    jQuery('.toeGiftTypeRadioBox').buttonset();
    jQuery('.toeGiftConditionCheck').click(function(){
        var id = jQuery(this).attr('id');
        if(jQuery(this).attr('checked') == 'checked') 
            jQuery('#'+ id+ 'Params').slideDown(TOE_DATA.animationSpeed);
        else
            jQuery('#'+ id+ 'Params').slideUp(TOE_DATA.animationSpeed);
    });
    jQuery('.toeGiftType').click(function(){
        jQuery('.toeGiftTypeParams').slideUp(TOE_DATA.animationSpeed);
        jQuery('#'+ jQuery(this).attr('id')+ 'Params').slideDown(TOE_DATA.animationSpeed);
    });
    jQuery('.toeGiftConditionCheck').removeAttr('checked');
    jQuery('.toeGiftType').removeAttr('checked');
    jQuery('#toeGiftsForm').submit(function(){
        jQuery(this).sendForm({
            msgElID: 'toeGiftsMsg',
            onSuccess: function(res) {
                if(!res.error) {
                    if(!toeTables['toe_gifts_list'].redrawRow('id', res.data[0].id, res.data[0])) {
                        toeTables['toe_gifts_list'].draw(res.data);
                        toeClearGiftForm();
                    }
                }
            }
        });
        return false;
    });
    jQuery('#addOptGift').click(function(){
        toeClearGiftForm();
        return false;
    });
});
-->
</script>
<div class="tab_form">
    <form id="toeGiftsForm">
        <div class="toeGiftFormDataRow">
            <?php lang::_e('Label')?>: <?php echo html::text('label')?>
        </div>
        <div id="toeGiftsConditions" class="toeGiftFormDataRow">
            <?php foreach($this->conditions as $t) { ?>
            <div class="toGiftConditionBlock">
                <div style="float: left;">
                    <?php echo html::checkbox('conditions['. $t['id'].'][enabled]', array('value' => 1, 'attrs' => 'id="toeGiftCondition'.$t['id'].'" class="toeGiftConditionCheck"'))?>
                    <label for="<?php echo 'toeGiftCondition'.$t['id']?>">
                        <?php lang::_e($t['label'])?>
                    </label>
                </div>
                <div id="toeGiftCondition<?php echo $t['id']?>Params" class="toeGiftConditionParamsBlock">
                    <?php
                        $paramsStr = '';
                        switch($t['id']) {
                            case 1:
                                $paramsStr = lang::_('Price From'). ': '. html::text('conditions[1][price_from]', array('attrs' => 'id="toeGiftCond_1_price_from"')). '<br />'. 
                                             lang::_('Price To') .': '. html::text('conditions[1][price_to]', array('attrs' => 'id="toeGiftCond_1_price_to"'));
                                break;
                            case 2:
                                $paramsStr = lang::_('More than'). ': '. html::text('conditions[2][products_count]', array('attrs' => 'id="toeGiftCond_2_products_count"')). lang::_('products');
                                break;
                            case 3:
                                $paramsStr = lang::_('Select a date') .': '. html::datepicker('conditions[3][date]', array('attrs' => 'id="toeGiftCond_3_date"'));
                                break;
                            case 4:
                                $paramsStr = lang::_('Select Categories') .': '. html::categorySelectlist('conditions[4][categories]', array('attrs' => 'id="toeGiftCond_4_categories"'));
                                break;
                            case 5:
                                $paramsStr = lang::_('Select Products') .': '. html::productsSelectlist('conditions[5][products]', array('attrs' => 'id="toeGiftCond_5_products"'));
                                break;
                            case 6:
                                break;
                            case 7:
                                $paramsStr = lang::_('Orders count') .': '. html::text('conditions[7][orders_count]', array('attrs' => 'id="toeGiftCond_7_orders_count"'));
                                break;
                            case 8:
                                $paramsStr = lang::_('Total amount') .': '. html::text('conditions[8][total_amount]', array('attrs' => 'id="toeGiftCond_8_total_amount"'));
                                break;
                        }
                        echo $paramsStr;
                    ?>
                </div>
                <div style="clear: both;"></div>
            </div>
            <?php }?>
        </div>
        <div class="toeGiftFormDataRow">
            <?php lang::_e('Gift')?>:
            <div style="width: 100%;">
                <div style="float: left; width: 33%;">
                    <div class="toeGiftTypeRadioBox"><?php echo html::radiobutton('type', array('value' => 'price', 'attrs' => 'id="toeGiftTypePrice" class="toeGiftType"'))?><label for="toeGiftTypePrice"><?php lang::_e('Price')?></label></div>
                    <div class="toeGiftTypeParams" id="toeGiftTypePriceParams">
                        <?php lang::_e('Price')?>: <?php echo html::text('type_params[price]')?><br />
                        <?php lang::_e('Absolute')?>: <?php echo html::checkbox('type_params[absolute_price]')?>
                    </div>
                </div>
                <div  style="float: left; width: 33%;">
                    <div class="toeGiftTypeRadioBox"><?php echo html::radiobutton('type', array('value' => 'product', 'attrs' => 'id="toeGiftTypeProduct" class="toeGiftType"'))?><label for="toeGiftTypeProduct"><?php lang::_e('Product Present')?></label></div>
                    <div class="toeGiftTypeParams" id="toeGiftTypeProductParams">
                        <?php echo html::productsSelectlist('type_params[product]')?>
                    </div>
                </div>
                <div style="float: left; width: 33%;">
                    <div class="toeGiftTypeRadioBox"><?php echo html::radiobutton('type', array('value' => 'shipping', 'attrs' => 'id="toeGiftTypeShipping" class="toeGiftType"'))?><label for="toeGiftTypeShipping"><?php lang::_e('Free Shipping')?></label></div>
                    <div class="toeGiftTypeParams" id="toeGiftTypeShippingParams"></div>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
        <div class="toeGiftFormDataRow">
            <?php lang::_e('Date From')?>: <?php echo html::datepicker('date_from', array('id' => 'gifts_date_from'))?> 
            <?php lang::_e('To')?>: <?php echo html::datepicker('date_to', array('id' => 'gifts_date_to'))?>
        </div>
        <div class="toeGiftFormDataRow">
            <div style="float: left;">
                <?php echo html::hidden('id', array('value' => ''))?>
                <?php echo html::hidden('page', array('value' => lang::_('gifts')))?>
                <?php echo html::hidden('action', array('value' => lang::_('storeGift')))?>
                <?php echo html::hidden('reqType', array('value' => lang::_('ajax')))?>
                <?php echo html::submit('save', array('value' => lang::_('Save')))?>
            </div>
            <div style="float: right;" id="toeGiftsMsg"></div>
            <div style="clear: both;"></div>
        </div>
    </form>
</div>
<div style="clear: both;"></div>
<div class="options_list">
    <table id="toe_gifts_list" width="100%">
        <tr class="toe_admin_row_header">
            <td><?php lang::_e('ID')?></td>
            <td><?php lang::_e('Label')?></td>
            <td><?php lang::_e('Action')?></td>
        </tr>
        <tr class="toe_admin_row toeGiftsRow toeRowExample" onclick="toeEditGift(this);">
            <td class="id"></td>
            <td class="label"></td>
            <td>
                <a href="#" onclick="toeRemoveGift(this); return false;">
                    <?php echo html::img('cross.gif')?>
                </a>
            </td>
        </tr>
    </table>
</div>
<div><?php echo html::button(array('attrs' => 'id="addOptGift" class="button"', 'value' => lang::_('Add')))?></div>