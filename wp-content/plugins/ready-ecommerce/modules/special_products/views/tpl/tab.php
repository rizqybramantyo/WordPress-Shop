<?php
    $this->spFields['price_change']->addHtmlParam('attrs', 'style="width: 100px;"');
    $categories = frame::_()->getModule('products')->getCategories();
    $products = frame::_()->getModule('products')->getModel()->get(array('getFields' => 'post.ID, post.post_title'));
    if(!empty($categories)) {
        foreach($categories as $c) {
            $cOptions[$c->term_taxonomy_id] = $c->cat_name;
        }
    }
    if(!empty($products)) {
        foreach($products as $p) {
            $pOptions[$p['ID']] = $p['post_title'];
        }
    }
?>
<style type="text/css">
    .ui-button-text-only .ui-button-text {
        padding: 0.4em 1em;
    }
</style>
<script type="text/javascript">
<!--
jQuery(document).ready(function(){
    toeTables['toe_special_products'] = new toeListTable('toe_special_products', <?php echo utils::jsonEncode($this->specialProducts)?>);
    toeTables['toe_special_products'].draw();
    jQuery('#toeSigns').buttonset();
    jQuery('#toeSpechialProductsApplyTo').buttonset();
    jQuery('#toeSigns input[name=sign]').change(function(){
        if(parseInt(jQuery(this).val())) {
            jQuery('#toe_sign_view').val('-');
        } else {
            jQuery('#toe_sign_view').val('+');
        }
    });
    jQuery('#toe_apply_to_categories').click(function(){
        toeSpSwitchSelectBox(this, 'toe_sp_categories_box');
    });
    jQuery('#toe_apply_to_products').click(function(){
        toeSpSwitchSelectBox(this, 'toe_sp_products_box');
    });
    jQuery('#toeSpeshialProductForm').submit(function(){
        jQuery(this).sendForm({
            msgElID: 'speshialProductsMsg',
            onSuccess: function(res) {
                if(!res.error) {
                    if(!toeTables['toe_special_products'].redrawRow('id', res.data[0].id, res.data[0])) {
                        toeTables['toe_special_products'].draw(res.data);
                        jQuery('#toeSpeshialProductForm').clearForm();
                        jQuery('#toeSpeshialProductForm').find('input[name=active]').attr('checked', 'checked');
                        toeSpSwitchSelectBox(jQuery('#toe_apply_to_categories'), 'toe_sp_categories_box');
                        toeSpSwitchSelectBox(jQuery('#toe_apply_to_products'), 'toe_sp_products_box');
                        
                    }
                }
            }
        });
        return false;
    });
});
-->
</script>
<div class="tab_form">
    <form id="toeSpeshialProductForm">
        <table width="100%">
            <tr>
                <td class="field_label"><?php lang::_e($this->spFields['label']->label)?>: </td>
                <td><?php $this->spFields['label']->display()?></td>
            </tr>
            <tr>
                <td class="field_label"><?php lang::_e($this->spFields['price_change']->label)?>: </td>
                <td>
                    <?php echo html::text('sign_view', array('value' => '+', 'attrs' => 'disabled="true" style="width: 15px;" id="toe_sign_view"'))?>
                    <?php $this->spFields['price_change']->display()?>
                    <div id="toeSigns">
                        <?php echo html::radiobutton('sign', array('attrs' => 'id="sign_plus"', 'value' => 0, 'checked' => 1))?><label for="sign_plus">&nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <?php echo html::radiobutton('sign', array('attrs' => 'id="sign_minus"', 'value' => 1))?><label for="sign_minus">&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="field_label"><?php lang::_e($this->spFields['absolute']->label)?>: </td>
                <td><?php $this->spFields['absolute']->display()?></td>
            </tr>
            <tr>
                <td class="field_label"><?php lang::_e($this->spFields['apply_to']->label)?>: </td>
                <td>
                    <table>
                        <tr id="toeSpechialProductsApplyTo">
                            <td><?php echo html::checkbox('apply_to[categories]', array('value' => 1, 'attrs' => 'id="toe_apply_to_categories"'))?><label for="toe_apply_to_categories"><?php lang::_e('Categories')?></label></td>
                            <td><?php echo html::checkbox('apply_to[products]', array('value' => 1, 'attrs' => 'id="toe_apply_to_products"'))?><label for="toe_apply_to_products"><?php lang::_e('Products')?></label></td>
                        </tr>
                        <tr>
                            <td><div id="toe_sp_categories_box" style="display: none;"><?php echo html::selectlist('categories', array('options' => $cOptions))?></div></td>
                            <td><div id="toe_sp_products_box" style="display: none;"><?php echo html::selectlist('products', array('options' => $pOptions))?></div></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="field_label"><?php lang::_e($this->spFields['mark_as_sale']->label)?>: </td>
                <td><?php $this->spFields['mark_as_sale']->display()?></td>
            </tr>
            <tr>
                <td class="field_label"><?php lang::_e($this->spFields['active']->label)?>: </td>
                <td><?php $this->spFields['active']->display()?></td>
            </tr>
            <tr>
                <td>
                    <?php echo html::hidden('id', array('value' => ''))?>
                    <?php echo html::hidden('action', array('value' => 'storeSpecialProduct'))?>
                    <?php echo html::hidden('page', array('value' => 'special_products'))?>
                    <?php echo html::hidden('reqType', array('value' => 'ajax'))?>
                    <?php echo html::button(array('attrs' => 'class="button"', 'value' => lang::_('Save')))?>
                </td>
                <td id="speshialProductsMsg"></td>
            </tr>
        </table>
    </form>
</div>
<div class="options_list">
    <table id="toe_special_products" width="100%">
        <tr class="toe_admin_row_header">
            <td><?php lang::_e('ID')?></td>
            <td><?php lang::_e('Label')?></td>
            <td><?php lang::_e('Status')?></td>
            <td><?php lang::_e('Action')?></td>
        </tr>
        <tr class="toe_admin_row toeSpecialProductRow toeRowExample">
            <td class="id"></td>
            <td class="label"></td>
            <td><a href="#" class="active" onclick="toeSwitchSpecialProductStatus(this); return false;"></a></td>
            <td>
                <a href="#" onclick="toeRemoveSpecialProduct(this); return false;">
                    <?php echo html::img('cross.gif')?>
                </a>
            </td>
        </tr>
    </table>
</div>