<style type="text/css">
    #toeCreateCouponsButtonBlock {
        display: none;
    }
</style>
<script type="text/javascript">
<!--
var toeAllCoupons = <?php echo utils::jsonEncode($this->allCoupons)?>;
var toeCouponsTabsToPatterns = new Array();
function toeCouponsListTabe(table, data) {
    var attrs = new Array();
    attrs['table']= table;
    attrs['data']= data;
    toeCouponsListTabe.superclass.constructor.apply(this, attrs);
    this.converters['used'] = this._convertActive;
    this.init(table, data);
}
extend(toeCouponsListTabe, toeListTable);
jQuery(document).ready(function(){
    toeTables['toe_coupons_patterns_list'] = new toeListTable('toe_coupons_patterns_list', <?php echo utils::jsonEncode($this->allCouponsPatterns)?>);
    toeTables['toe_coupons_patterns_list'].draw();
    
    jQuery('#toeCouponsForm').submit(function(){
        jQuery(this).sendForm({
            msgElID: 'toeCouponsMsg',
            onSuccess: function(res) {
                if(!res.error) {
                    if(!toeTables['toe_coupons_patterns_list'].redrawRow('id', res.data[0].id, res.data[0])) {
                        toeTables['toe_coupons_patterns_list'].draw(res.data);
                        toeClearCouponsForm();
                        toeBuildCouponsTab(res.data[0]);
                    }
                }
            }
        });
        return false;
    });
    jQuery('#addOptCoupons').click(function(){
        toeClearCouponsForm();
        return false;
    });
    jQuery('#toeCreateCoupons').click(function(){
        var id = parseInt(jQuery('#toeCouponsForm input[name=id]').val());
        if(!id) {
            alert('<?php lang::_e('Select coupon at first')?>');
            return;
        }
        jQuery(this).sendForm({
            msgElID: 'toeCouponsMsg',
            data: {mod: 'coupons', action: 'createCoupons', reqType: 'ajax', id: id, generate_amount: jQuery('#toeCouponsForm input[name=generate_amount]').val()},
            onSuccess: function(res) {
                if(res.data && toeTables['toeCouponsTable'+ id]) {
                    toeTables['toeCouponsTable'+ id].setData(res.data);
                    toeTables['toeCouponsTable'+ id].draw();
                }
            }
        });
        return false;
    });
    jQuery('#toeCreateCouponsButtonBlock').hide(TOE_DATA.animationSpeed);
    toeClearCouponsForm();
    jQuery('#toeCouponsTabs').tabs().addClass('ui-tabs-vertical-left-side');
    toeBuildCouponsTabs();
});
function toeClearCouponsForm() {
    jQuery('#toeCouponsForm').clearForm();
    jQuery('#toeCouponsForm input[name=id]').val('');
    jQuery('#toeCreateCouponsButtonBlock').hide(TOE_DATA.animationSpeed);
}
function toeEditCouponPattern(row) {
    if(notOnRowClicked) {
        notOnRowClicked = false;
        return;
    }
    var id = parseInt(jQuery(row).find('.id:first').html());
    if(!id) return;
    //And finaly - load data without new ajax request
    var couponPattern = toeTables['toe_coupons_patterns_list'].getData('id', id);
    if(couponPattern) {
        toeClearCouponsForm();
        var form = jQuery('#toeCouponsForm');
        for(key in couponPattern) {
            switch(key) {
                case 'gifts':
                    for(i = 0; i < couponPattern[key].length; i++) {
                        jQuery(form).find('[name="gifts[]"]').find('option[value='+ couponPattern[key][i]+ ']').attr('selected', 'selected');
                    }
                    break;
                default:
                    jQuery(form).find('[name="'+ key+ '"]').val(couponPattern[key]);
                    break;
            }
        }
        jQuery('#toeCreateCouponsButtonBlock').show(TOE_DATA.animationSpeed);
    }
}
function toeRemoveCouponPattern(link) {
    notOnRowClicked = true;
    var id = getIdFromTable(link);
    if(id) {
        jQuery(this).sendForm({
            data: {id: id, 
                action: 'deleteCouponTemplate', 
                page: 'coupons', 
                reqType: 'ajax'},
            msgElID: 'toeCouponsMsg',
            onSuccess: function(res) {
                if(!res.error) {
                    toeTables['toe_coupons_patterns_list'].deleteRow('id', res.data.id);
                    toeRemoveCoupons(res.data.id, true);  //Remove coupons for this pattern
                    
                }
            }
        });
    }
}
function toeRemoveCoupon(link) {
    var id = parseInt(jQuery(link).parents('tr:first').find('.id:first').html());
    if(!id) return;
    jQuery(this).sendForm({
        data: {id: id, 
            action: 'deleteCoupon', 
            page: 'coupons', 
            reqType: 'ajax'},
        msgElID: 'toeCouponsMsg',
        onSuccess: function(res) {
            if(!res.error) {
                var patternId = parseInt(jQuery(link).parents('tr:first').find('.pattern_id:first').html());
                if(patternId) {
                    toeTables['toeCouponsTable'+ patternId].deleteRow('id', id);
                }
            }
        }
    });
}
function toeRemoveCoupons(patternId, patternRemoved) {
    patternRemoved = typeof(patternRemoved) == 'undefined' ? false : patternRemoved;
    jQuery(this).sendForm({
        data: {pattern_id: patternId, 
            action: 'deleteCoupon', 
            page: 'coupons', 
            reqType: 'ajax'},
        msgElID: 'toeCouponsMsg',
        onSuccess: function(res) {
            if(!res.error) {
                if(patternRemoved) {
                    jQuery('#toeCouponsTabs').tabs('remove', toeCouponsTabsToPatterns[patternId]);
                }
            }
        }
    });
    return false;
}
function toeBuildCouponsTabs() {
    if(toeTables['toe_coupons_patterns_list'].data) {
        for(i = 0; i < toeTables['toe_coupons_patterns_list'].data.length; i++) {
            toeBuildCouponsTab(toeTables['toe_coupons_patterns_list'].data[i]);
        }
    }
}
function toeBuildCouponsTab(patternData) {
    var table = jQuery('#toeCouponsTableExample').clone().attr('id', 'toeCouponsTable'+ patternData.id).css('display', '');
    jQuery('#toeCouponsTabs').tabs('add', '#toeCouponsListTab'+ patternData.id, patternData.label);
    toeCouponsTabsToPatterns[patternData.id] = parseInt(jQuery('#toeCouponsTabs > ul > li').size()) - 1;
    jQuery('#toeCouponsListTab'+ patternData.id).append('<input type="button" onclick="toeRemoveCoupons('+ patternData.id+ ', false);" value="<?php lang::_e('Delete All')?>" />');
    jQuery('#toeCouponsListTab'+ patternData.id).append( table );
    toeTables['toeCouponsTable'+ patternData.id] = new toeCouponsListTabe('toeCouponsTable'+ patternData.id, toeAllCoupons[patternData.id]);
    if(toeAllCoupons[patternData.id]) {
        toeTables['toeCouponsTable'+ patternData.id].draw();
    }
}
-->
</script>
<div class="tab_form">
    <form id="toeCouponsForm">
        <?php foreach($this->fields as $f) { ?>
            <div class="toeCouponsFormDataRow">
                <?php lang::_e($f->label)?>: <?php $f->display()?>
            </div>
        <?php }?>
        <div class="toeCouponsFormDataRow">
            <div style="float: left;">
                <?php echo html::hidden('id', array('value' => ''))?>
                <?php echo html::hidden('page', array('value' => lang::_('coupons')))?>
                <?php echo html::hidden('action', array('value' => lang::_('storeCouponPattern')))?>
                <?php echo html::hidden('reqType', array('value' => lang::_('ajax')))?>
                <?php echo html::submit('save', array('value' => lang::_('Save Coupons Pattern')))?>
            </div>
            <div style="float: right;" id="toeCouponsMsg"></div>
            <div style="clear: both;"></div>
        </div>
        <div class="toeCouponsFormDataRow" id="toeCreateCouponsButtonBlock">
            <?php echo html::submit('createCoupons', array('attrs' => 'id="toeCreateCoupons"', 'value' => lang::_('Create Coupons')))?>
        </div>
    </form>
</div>
<div style="clear: both;"></div>
<div><?php echo html::button(array('attrs' => 'id="addOptCoupons" class="button"', 'value' => lang::_('Create New')))?></div>
<div class="options_list">
    <div id="toeCouponsTabs">
        <ul>
            <li><a href="#toeAllCouponsPatterns"><?php lang::_e('All Coupons Patterns')?></a></li>
        </ul>
        <div id="toeAllCouponsPatterns">
            <table id="toe_coupons_patterns_list" width="100%">
                <tr class="toe_admin_row_header">
                    <td><?php lang::_e('ID')?></td>
                    <td><?php lang::_e('Label')?></td>
                    <td><?php lang::_e('Action')?></td>
                </tr>
                <tr class="toe_admin_row toeCouponsPatternsRow toeRowExample" onclick="toeEditCouponPattern(this);">
                    <td class="id"></td>
                    <td class="label"></td>
                    <td>
                        <a href="#" onclick="toeRemoveCouponPattern(this); return false;">
                            <?php echo html::img('cross.gif')?>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <!--Example for coupons table-->
    <table id="toeCouponsTableExample" style="display: none;" width="100%">
        <tr class="toe_admin_row_header">
            <td><?php lang::_e('ID')?></td>
            <td><?php lang::_e('Code')?></td>
            <td><?php lang::_e('Used')?></td>
            <td><?php lang::_e('Action')?></td>
        </tr>
        <tr class="toe_admin_row toeRowExample">
            <td class="id"></td>
            <td class="code"></td>
            <td><a href="#" onclick="return false;" class="used"></a></td>
            <td>
                <a href="#" onclick="toeRemoveCoupon(this); return false;">
                    <?php echo html::img('cross.gif')?>
                </a>
            </td>
            <td style="display: none;" class="pattern_id"></td>
        </tr>
    </table>
    <!--****-->
</div>