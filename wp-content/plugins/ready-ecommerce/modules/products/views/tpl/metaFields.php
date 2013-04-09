<style type="text/css">
    .toeEfValuesBox {
        display: none;
    }
</style>
<script type="text/javascript">
// <!--
    var toeSelectedEfVals = new Array();
    jQuery(document).ready(function(){
        jQuery('.toeExtraFieldsSelectList').change(function(){
            jQuery(this).parents('.product_extra:first').find('.toeEfValuesBox:first').show(TOE_DATA.animationSpeed);
            toeFillInEfVal( jQuery(this).parents('.product_extra:first'), jQuery(this).val() );
        });
        jQuery('.toeEfValuesBox input[name=attrs_price]').keyup(function(){
            var parentBox = jQuery(this).parents('.toeEfValuesBox:first');
            var selectedOpts = jQuery(parentBox).find('input[name=selectedOpts]').val().split(',');
            for(var i = 0; i < selectedOpts.length; i++) {
                jQuery(parentBox).find('input[name="exVal['+ selectedOpts[i]+ '][price]"]').val( jQuery(this).val() );
            }
            /*var forEfVals = jQuery(this).attr('for').split(',');
            
            alert(jQuery(this).val());*/
        });
        jQuery('.toeEfValuesBox input[name=price_absolute], .toeEfValuesBox input[name=disabled]').change(function(){
            var parentBox = jQuery(this).parents('.toeEfValuesBox:first');
            var selectedOpts = jQuery(parentBox).find('input[name=selectedOpts]').val().split(',');
            var checked = jQuery(this).attr('checked');
            var name = jQuery(this).attr('name');
            for(var i = 0; i < selectedOpts.length; i++) {
                if(checked)
                    jQuery(parentBox).find('input[name="exVal['+ selectedOpts[i]+ ']['+ name+ ']"]').val( 1 );
                else
                    jQuery(parentBox).find('input[name="exVal['+ selectedOpts[i]+ ']['+ name+ ']"]').val( 0 );
            }         
        });
    });
    function toeFillInEfVal(parentBox, optId) {
        jQuery(parentBox).find('.toeEfValuesBox input[name=selectedOpts]').val(optId);
        if(jQuery(optId).size() > 1) {  //If more than one value is selected - all inputs will be empty
            var setPrice = true;
            var setAbsolute = true;
            var setActive = 0;
            for(var i = 1; i < optId.length; i++) {
                if(jQuery(parentBox).find('input[name="exVal['+ optId[i-1]+ '][price]"]').val() != jQuery(parentBox).find('input[name="exVal['+ optId[i]+ '][price]"]').val())
                    setPrice = false;
                if(jQuery(parentBox).find('input[name="exVal['+ optId[i-1]+ '][price_absolute]"]').val() != jQuery(parentBox).find('input[name="exVal['+ optId[i]+ '][price_absolute]"]').val())
                    setAbsolute = false;
                if(jQuery(parentBox).find('input[name="exVal['+ optId[i-1]+ '][disabled]"]').val() != jQuery(parentBox).find('input[name="exVal['+ optId[i]+ '][disabled]"]').val())
                    setActive = false;
            }
            if(setPrice)
                jQuery(parentBox).find('.toeEfValuesBox input[name=attrs_price]').val( jQuery(parentBox).find('input[name="exVal['+ optId[0]+ '][price]"]').val() );
            else
                jQuery(parentBox).find('.toeEfValuesBox input[name=attrs_price]').val('');
            if(setAbsolute) {
                if(parseInt( jQuery(parentBox).find('input[name="exVal['+ optId[0]+ '][price_absolute]"]').val() ))
                    jQuery(parentBox).find('.toeEfValuesBox input[name=price_absolute]').attr('checked', 'checked');
                else
                    jQuery(parentBox).find('.toeEfValuesBox input[name=price_absolute]').removeAttr('checked');
            } else
                jQuery(parentBox).find('.toeEfValuesBox input[name=price_absolute]').removeAttr('checked');
            if(setActive) {
                if(parseInt( jQuery(parentBox).find('input[name="exVal['+ optId[0]+ '][disabled]"]').val() ))
                    jQuery(parentBox).find('.toeEfValuesBox input[name=disabled]').attr('checked', 'checked');
                else
                    jQuery(parentBox).find('.toeEfValuesBox input[name=disabled]').removeAttr('checked');
            } else
                jQuery(parentBox).find('.toeEfValuesBox input[name=disabled]').removeAttr('checked');
        } else {
            jQuery(parentBox).find('.toeEfValuesBox input[name=attrs_price]').val( jQuery(parentBox).find('input[name="exVal['+ optId+ '][price]"]').val() );

            if(parseInt( jQuery(parentBox).find('input[name="exVal['+ optId+ '][price_absolute]"]').val() ))
                jQuery(parentBox).find('.toeEfValuesBox input[name=price_absolute]').attr('checked', 'checked');
            else
                jQuery(parentBox).find('.toeEfValuesBox input[name=price_absolute]').removeAttr('checked');
            
            if(parseInt( jQuery(parentBox).find('input[name="exVal['+ optId+ '][disabled]"]').val() ))
                jQuery(parentBox).find('.toeEfValuesBox input[name=disabled]').attr('checked', 'checked');
            else
                jQuery(parentBox).find('.toeEfValuesBox input[name=disabled]').removeAttr('checked');
        }
    }
// -->
</script>
<div id="product_extras">
    <fieldset class="toeProdFieldset">
        <legend><?php lang::_e('Main Product data')?></legend>
		<?php $i = 0; $itemPerRow = 3;?>
        <?php foreach($this->fields as $f) { ?>
            <?php if ($f->html != 'hidden') {?>
            <div class="product_extra">
                 <label for="<?php echo $f->name?>"><?php echo $f->label?> :</label>
                 <div class="product_field"><?php $f->display()?></div>
                 <br clear="all" />
             </div>
			<?php 
				if($i%$itemPerRow == $itemPerRow-1) { ?>
				<br clear="all" />
				<?php }
				$i++;
			?>
        <?php } else  {
             echo $f->viewField($f->name, $this->extra_values[$f->id]);
        }?>
        <?php }?>
    </fieldset>
    <br clear="all" />
    <fieldset class="toeProdFieldset">
        <legend><?php lang::_e('Extra Product data')?></legend>
        <?php foreach($this->extraFieldsMultiple as $f) {
               /*$field_cat = array_intersect($f->destination['categories'], $this->categories);
               if (empty($field_cat)) continue;*/
            ?>
             <div class="product_extra">
                <label for="<?php echo $f->name?>"><?php echo $f->label?> :</label>
                <div style="">
                    <?php lang::_e('Disable')?>: <?php ?>
                </div>
                <div class="product_field"><?php $f->display()?></div>
                <?php if($f->getHtml() == 'selectlist') {?>
                <br style="clear: both;" />
                <div class="toeEfValuesBox">
                    <table>
                        <tr><td><?php lang::_e('Price')?>:</td><td><?php echo html::text('attrs_price')?></td></tr>
                        <tr><td><?php lang::_e('Absolute')?>:</td><td><?php echo html::checkbox('price_absolute')?></td></tr>
                        <tr><td><?php lang::_e('Disable')?>:</td><td><?php echo html::checkbox('disabled')?></td></tr>
                    </table>
                    <?php echo html::hidden('selectedOpts')?>
                    <?php foreach($f->getHtmlParam('options') as $id => $value) {
                        echo html::hidden('exValuesToFields['. $id. ']', array('value' => $f->getId()));
                        foreach($this->exFieldsEfVals[$id] as $efVal) {
                            $efVal->display();
                        }
                    }?>
                </div>
                <?php }?>
                <br clear="all" />
            </div>
        <?php }?>   
    </fieldset>
    <br clear="all" />
</div>
    <br clear="all" />
<h4>
    <a id="add_productfield_popup" href="<?php echo $this->post->ID?>" target="_blank">
         <?php lang::_e('+ Add New Parameter');?>
    </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="<?php echo admin_url('admin.php?page=toeoptions#toe_opt_productFieldsTab'); ?>" target="_blank">
        <?php lang::_e('Manage Product Parameters');?>
    </a>
</h4>