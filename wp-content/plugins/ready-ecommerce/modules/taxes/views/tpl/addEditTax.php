<?php
    $type = $this->taxFields['type']->value;
    if ($type == '') $type = 'total';
    if(!is_array($this->taxFields['data']->value)) {
        if(empty($this->taxFields['data']->value)) 
            $this->taxFields['data']->value = array();
        else
            $this->taxFields['data']->value = utils::jsonDecode ($this->taxFields['data']->value);
    }
    $cOptions = frame::_()->getModule('products')->getHelper()->getCategoriesList();
    if(!empty($cOptions)) {
        if(!is_array($this->taxFields['data']->value['categories']))
                $this->taxFields['data']->value['categories'] = array();
    }
    $listOfDest = array();
    foreach(fieldAdapter::$userfieldDest as $d) {
        $listOfDest[] = array(
            'id' => $d,
            'text' => $d,
            'checked' => (is_array($this->taxFields['data']->value['dest']) && in_array($d, $this->taxFields['data']->value['dest'])),
        );
    }
?>
<style type="text/css">
    #editTaxForm input {
        width: auto;
    }
    .toeTaxNonusualUserfieldRow {
        display: none;
    }
</style>
<script type="text/javascript">
<!--
jQuery(document).ready(function(){
    jQuery('input[name=selectAllCountries]').change(function(){
        if(jQuery(this).attr('checked')) {
            jQuery('select[name="data[country][]"]').children('option[value!=0]').attr('selected', 'selected');
        } else {
            jQuery('select[name="data[country][]"]').children('option').removeAttr('selected');
        }
        toeStatesObjects['datastate'].countryChange(jQuery('select[name="data[country][]"]').val());
    });
    jQuery('input[name="data[selectAllCategories]"]').change(function(){
        if(jQuery(this).attr('checked')) {
            jQuery('select[name="data[categories][]"]').children('option[value!=0]').attr('selected', 'selected');
        } else {
            jQuery('select[name="data[categories][]"]').children('option').removeAttr('selected');
        }
    });
    jQuery('input[name="data[showAllUserfields]"]').change(function(){
        if(jQuery(this).attr('checked')) {
            jQuery('.toeTaxNonusualUserfieldRow').show(TOE_DATA.animationSpeed);
        } else {
            jQuery('.toeTaxNonusualUserfieldRow').hide(TOE_DATA.animationSpeed);
        }
    });
});
-->
</script>
<h1><?php lang::_e(array(($this->method == 'post' ? 'Add' : 'Edit'), 'Tax'))?></h1>
<form action="<?php echo uri::mod('currency', '', '', array('id' => $this->id))?>" method="post" id="editTaxForm">
    <table width="100%">
        <tr>
            <td valign="top">
                <table width="100%">
                    <tr>
                        <td>
                            <?php lang::_e('Tax Name')?>: 
                            <a href="#" class="toeOptTip" tip="<?php lang::_e('Label for Tax')?>"></a>
                        </td>
                        <td><?php $this->taxFields['label']->display()?></td>
                    </tr>
                    <tr>
                        <td>
                            <?php lang::_e('Tax rate')?>: 
                            <a href="#" class="toeOptTip" tip="<?php lang::_e("Taxe Rate. Set from 0 to 100, in percents by default. Or check 'Absolute' checkbox for absolute value.")?>"></a>
                        </td>
                        <td>
                            <?php echo html::text('data[rate]', array('value' => $this->taxFields['data']->value['rate']))?><br />
                            <?php echo html::checkbox('data[absolute]', array('value' => 1, 'checked' => (bool)$this->taxFields['data']->value['absolute']))?>
                            <?php lang::_e('Absolute')?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php lang::_e('Country for applying')?>: 
                            <a href="#" class="toeOptTip" tip="<?php lang::_e('If you would like to apply tax only for specific countries - select them here. Check "Apply to all countries" to select them all.')?>"></a>
                        </td>
                        <td>
                            <?php echo html::countryListMultiple('data[country][]', array('value' => $this->taxFields['data']->value['country']))?><br />
                            <?php echo html::checkbox('selectAllCountries')?> <?php lang::_e('Apply to all countries')?><br />
                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php lang::_e('States for applying')?>: 
                            <a href="#" class="toeOptTip" tip="<?php lang::_e('If you would like to apply tax only for specific states - select them here. You must select country at first')?>"></a>
                        </td>
                        <td>
                            <?php echo html::statesListMultiple('data[state]', array('value' => $this->taxFields['data']->value['state']))?><br />
                        </td>
                    </tr>
                    <?php foreach($this->userFields as $uf) {
                        if(in_array($uf['code'], array('country', 'state', 'username', 'password'))) continue;  //It was displayed before in hardcode, and username with password can broke whole constuction if client will use "Save password to site" browser functionality
                    ?>
                    <tr class="toeTaxNonusualUserfieldRow">
                        <td>
                            <?php lang::_e($uf['label'])?>: 
                            <?php if(!empty($uf['description'])) {?><a href="#" class="toeOptTip" tip="<?php lang::_e($uf['description'])?>"></a><?php }?>
                        </td>
                        <td>
                            <?php echo html::$uf['type']('data['. $uf['code']. ']', array('value' => $this->taxFields['data']->value[ $uf['code'] ]))?>
                        </td>
                    </tr>
                    <?php }?>
                    <tr>
                        <td>
                            <?php lang::_e('Categories for apply')?>: 
                            <a href="#" class="toeOptTip" tip="<?php lang::_e('If you would like to apply tax only for products from specific categories - select them here. Check "Apply to all categories" to select them all.')?>"></a>
                        </td>
                        <td>
                            <?php echo (empty($cOptions) ? lang::_('You have no categories') : html::selectlist('data[categories][]', array('options' => $cOptions, 'value'=>$this->taxFields['data']->value['categories'])))?><br />
							<?php echo html::checkbox('data[selectAllCategories]', array('checked' => $this->taxFields['data']->value['selectAllCategories']))?>
                            <?php lang::_e('Apply to all categories')?>
                        </td>
                    </tr>
                </table>
            </td>
            <td valign="top">
                <table width="100%">
                    <tr>
                        <td>
                            <?php lang::_e('Apply Tax')?>: 
                            <a href="#" class="toeOptTip" tip="<?php lang::_e('This will determine for what country - shipping, billing or registration, - tax will be applied')?>"></a>
                        </td>
                        <td><?php echo html::checkboxlist('data[dest][]', array('options' => $listOfDest, 'value'=> $this->taxFields['data']->value['dest'], 'delim' => '<br />'))?></td>
                    </tr>
                    <tr>
                        <td>
                            <?php lang::_e('Apply Tax to shipping cost')?>: 
                            <a href="#" class="toeOptTip" tip="<?php lang::_e('If Yes - tax rate will calculate including shipping cost (if it\'s available)')?>"></a>
                        </td>
                        <td><?php echo html::radiobuttons(
                                'data[applyToShipping]', 
                                array(
                                    'value' => $this->taxFields['data']->value['applyToShipping'], 
                                    'options' => array(lang::_('No'), lang::_('Yes')),
                                )
                        )?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo html::hidden('id', array('value' => $this->id))?>
                <?php echo html::hidden('action', array('value' => $this->method. 'Tax'))?>
                <?php echo html::hidden('page', array('value' => 'taxes'))?>
                <?php echo html::hidden('reqType', array('value' => 'ajax'))?>
                <?php echo html::button(array('attrs' => 'class="button"', 'value' => lang::_('Save')))?>
            </td>
            <td id="mod_msg_tax" class="mod_msg"></td>
        </tr>
    </table>
    <?php /*
    foreach($this->taxFields as $k => $f) { ?>
        <tr>
            <td class="field_label">
                <?php echo $f->label?>:
                <?php if ($f->description != ''):?>
                    <a href="#" class="toeOptTip" tip="<?php echo $f->description;?>"></a>
                <?php endif;?>
            </td>
            <td><?php 
            if($k == 'data') {
                $listOfDest = array();
                if(!is_array($f->value)) {
                    if(empty($f->value)) 
                        $f->value = array();
                    else
                        $f->value = (array)json_decode($f->value, true);
                }
                foreach(fieldAdapter::$userfieldDest as $d) {
                    $listOfDest[] = array(
                        'id' => $d,
                        'text' => $d,
                        'checked' => (is_array($field->value['dest']) && in_array($d, $field->value['dest'])),
                        );
                }
                $categories = frame::_()->getModule('products')->getCategories();
                $brands = frame::_()->getModule('products')->getBrands();
                $cOptions = array();
                $bOptions = array();
                if(!empty($categories)) {
                    if(!is_array($f->value['categories']))
                            $f->value['categories'] = array();
                    foreach($categories as $c) {
                        $cOptions[$c->term_taxonomy_id] = $c->cat_name;
                    }
                }
                if(!empty($brands)) {
                    if(!is_array($f->value['brands']))
                            $f->value['brands'] = array();
                    foreach($brands as $b) {
                        $bOptions[$b->term_taxonomy_id] = $b->cat_name;
                    }
                } ?>
                <div>
                    <div id="tax_address" class="tax_type_data" <?php if ($type != 'address') echo 'style="display:none;"'?>>
                        <?php lang::_e('Destination')?>: <br />
                        <?php echo html::checkboxlist('data[dest][]', array('options' => $listOfDest, 'value'=>$f->value['dest'], 'delim' => '<br />'))?><br />
                        <?php lang::_e('Countries')?>: <br />
                        <?php 
                            echo html::selectList('data[country][]', array('size'=> 10, 'options'=> fieldAdapter::getCountries(true), 'value'=>$f->value['country']));
                        if(!empty($this->userFields)) {
                            foreach($this->userFields as $uf) {
                                $field = new field('data[address]['. $uf['code']. ']', $uf['type']); 
                                if(isset($f->value['address'][$uf['code']])) {
                                        $field->setValue($f->value['address'][$uf['code']]);
                                }
                            ?>
                                <div><?php  lang::_e($uf['label'])?>: <?php $field->display();?></div>
                            <?php }
                        }?>
                    </div>
                    <div id="tax_category" class="tax_type_data" <?php if ($type != 'category') echo 'style="display:none;"'?>>
                        <b><?php lang::_e('Categories')?></b><br />
                        <?php echo (empty($cOptions) ? lang::_('You have no categories') : html::selectlist('data[categories][]', array('options' => $cOptions, 'value'=>$f->value['categories'])))?><br />
                            <b><?php lang::_e('Brands')?></b><br />
                        <?php echo (empty($bOptions) ? lang::_('You have no brands') : html::selectlist('data[brands][]', array('options' => $bOptions, 'value'=>$f->value['brands'])))?><br />
                    </div>
                    <div><?php lang::_e('Tax Rate')?>: <br /><?php  echo html::text('data[rate]', array('value' => $f->value['rate']))?></div>
                    <div><?php lang::_e('Absolute')?>: <?php  echo html::checkbox('data[absolute]', array('checked' => $f->value['absolute']))?></div>
                </div>
        <?php } else {
                $f->display(); }?>
            </td>
        </tr>
        <?php }*/ ?>
</form>