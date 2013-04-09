var mousePos = {x: 0, y: 0};
var mouseDown = false;
var mouse = {x: 0, y: 0, down: false};
var toeLoadElement = '<p class="subscreen_loading"><img src="'+ TOE_DATA.loader+ '" /></p>';
var removeClicked = false;
var notOnRowClicked = false;
//alert(TOE_DATA.options.store_name.value);
jQuery(function() {
    jQuery("#toe_opt_tabs").tabs();
    jQuery("#toe_opt_tabs").addClass('ui-tabs-vertical-right-side ui-helper-clearfix');
    jQuery("#toe_opt_tabs li").removeClass('ui-corner-top').addClass('ui-corner-right');
    jQuery('.opt_general_save_butt').click(function(){
        var id = parseInt(jQuery(this).parents('tr:first').find('td:first').html());
        if(id) {
            var value = '';
            switch(jQuery(this).prev().attr('type')) {
                case 'checkbox':
                    value = jQuery(this).prev().attr('checked') ? 1 : 0;
                    break;
                default:
                    if(jQuery('#opt_general_form').find('[name="opt_values['+ id+ ']"]:first').size()) {
                        value = jQuery('#opt_general_form').find('[name="opt_values['+ id+ ']"]:first').val();
                    } else
                        value = jQuery(this).prev().val();
                    break;
            }
            jQuery('#opt_general_form :input[name=value]').val(value);
            jQuery('#opt_general_form :input[name=id]').val(id);
            jQuery('#opt_general_form').sendForm({
				msgElID: jQuery(this).parents('tr:first').find('.toeMainOptsMsg:first')
			});
        }
    });
    jQuery('.toeOptTip').live('mouseover',function(event){
        if(!jQuery('#toeOptDescription').attr('toeFixTip')) {
			var pageY = event.pageY - jQuery(window).scrollTop();
			var pageX = event.pageX;
			var tipMsg = jQuery(this).attr('tip');
			var moveToLeft = jQuery(this).hasClass('toeTipToLeft');	// Move message to left of the tip link
			if(typeof(tipMsg) == 'undefined' || tipMsg == '') {
				tipMsg = jQuery(this).attr('title');
			}
			toeOptShowDescription( tipMsg, pageX, pageY, moveToLeft );
			jQuery('#toeOptDescription').attr('toeFixTip', 1);
		}
        return false;
    });
    jQuery('.toeOptTip').live('mouseout',function(){
		toeOptTimeoutHideDescription();
        return false;
    });
	jQuery('#toeOptDescription').live('mouseover',function(e){
		jQuery(this).attr('toeFixTip', 1);
		return false;
    });
	jQuery('#toeOptDescription').live('mouseout',function(e){
		toeOptTimeoutHideDescription();
		return false;
    });
});
jQuery.fn.inputToTextTD = function(){
    var inpVal = jQuery(this).children('input[type=text]').val();
    if(inpVal) {
        jQuery(this).html(inpVal);
        jQuery(this).bind('click', jQuery.fn.insertEditInTD);
    }
}
jQuery(document).ready(function(){
    jQuery('.options .add_option').live('click',function(){
       var html = '';
       /*switch(jQuery(this).attr('id')) {
           case 'toeAddUserfieldOpt':*/
               html = '<p> '+ TOE_DATA.lang.Value+ ': <input type="text" value="" name="params[options][value][]"> <span class="delete_option"></span></p>';
              /* break;
           default: //For products params
               html = '<p> '+ TOE_DATA.lang.Value+ ': <input type="text" value="" name="params[options][value][]"> '+ TOE_DATA.lang.Price+ ': <input type="text" value="" name="params[options][data][price][]">% '+ TOE_DATA.lang.Weight+ ': <input type="text" value="" name="params[options][data][weight][]">% <span class="delete_option"></span></p>';
               break;
       } */
       jQuery(this).parent().append(html); 
    });
    jQuery('.options .delete_option').live('click', function(){
       var span = jQuery(this);
       var id = span.attr('rel');
       if (isNumber(id)) {
           var data = 'reqType=ajax&page=options&action=deleteEfOption&id='+ id;
           jQuery.post(ajaxurl,data, function(res){
               if (res.html == '1') {
                    span.parent().remove();       
               } else {
                   alert(res.html);
               }
           }, "json");
       } else {
           span.parent().remove();       
       }
    });
    jQuery('.toe_opt_module').live('click',function(e){
        if(notOnRowClicked) {
            notOnRowClicked = false;
            return;
        }
        var id = jQuery(this).children('td:first').text();
        var mouse = {x: e.pageX, y: e.pageY};
        var data = 'reqType=ajax&page=options&action=getEditModule&id='+ id;
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        subScreen.show(toeLoadElement, mouse.x, mouse.y);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    subScreen.insertContent(res.html);
                }
            }
        });
    });
    // add user field
    jQuery('#add_opt_userfield').click(function(e){
        var data = 'reqType=ajax&page=options&action=getAddUserfields';
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        jQuery("#user_field_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    jQuery('#user_field_form .subscreen_loading').parent().html(res.html);
                }
            }
        });
        return false;
    });
    // edit user field
    jQuery('.toe_opt_userfield').live('click',function(e){
        if(removeClicked || notOnRowClicked) return;
        var id = jQuery(this).children('td:first').text();
        var data = 'reqType=ajax&page=options&action=getEditUserfields&id='+ id;
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        jQuery("#user_field_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    jQuery('#user_field_form .subscreen_loading').parent().html(res.html);
                    jQuery('.extrafield_type').trigger('change');
                }
            }
        });
    });
    // add product field
    jQuery('#add_opt_productfield').click(function(e){
        var data = 'reqType=ajax&page=options&action=getAddProductfields';
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        jQuery("#product_field_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    jQuery('#product_field_form .subscreen_loading').parent().html(res.html);
                }
            }
        });
        return false;
    });
    // add product field in pop-up
    jQuery('#add_productfield_popup').click(function(e){
        var mouse = {x: e.pageX, y: e.pageY};
        var data = 'reqType=ajax&page=options&action=getAddProductfields&show_field=1&pids[]='+ jQuery(this).attr('href');
        subScreen.show(toeLoadElement, mouse.x, mouse.y);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
					subScreen.moveTopCenter();
					subScreen.insertContent(res.html, true);
                    jQuery('body').append('<div id="toeOptDescription"></div>');
                }
            }
        });
        return false;
    });
    // edit product field
    jQuery('.toe_opt_productfield').live('click',function(e){
        if(removeClicked) return;
        var id = jQuery(this).children('td:first').text();
        var data = 'reqType=ajax&page=options&action=getEditProductfields&id='+ id;
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        jQuery("#product_field_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    jQuery('#product_field_form .subscreen_loading').parent().html(res.html);
                    jQuery('.extrafield_type').trigger('change');
                }
            }
        });
    });
    // add currency
    jQuery('#add_opt_currency').click(function(e){
        var data = 'reqType=ajax&page=currency&action=getAddCurrency';
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        jQuery("#currency_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    jQuery('#currency_form .subscreen_loading').parent().html(res.html);
                }
            }
        });
        return false;
    });
    // edit currency
    jQuery('.toe_opt_currency').live('click',function(e){
        if(removeClicked) return;
        var id = jQuery(this).children('td:first').text();
        var data = 'reqType=ajax&page=currency&action=getEditCurrency&id='+ id;
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        jQuery("#currency_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    jQuery('#currency_form .subscreen_loading').parent().html(res.html);
                    //toeChangeCurrencyViewSelect();
                }
            }
        });
    });
    // add tax
    jQuery('#add_opt_taxes').live('click',function(e){
        var data = 'reqType=ajax&page=taxes&action=getAddTax';
        //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
        jQuery("#tax_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                   jQuery('#tax_form .subscreen_loading').parent().html(res.html);
                }
            }
        });
        return false;
    });
    // edit tax
    jQuery('.toe_opt_taxes').live('click',function(e){
        jQuery(this).toeGetEditTax(e);
    });
    // edit template
    jQuery('.toe_opt_messenger_template').live('click',function(e){
        jQuery("#editTemplateForm").remove();
        jQuery("#messenger_form").html('');
        if(removeClicked) return;
        var id = jQuery(this).children('td:first').text();
        var data = 'reqType=ajax&page=messenger&action=getEditTemplate&id='+ id;
        jQuery("#messenger_form").html(toeLoadElement);
        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.html) {
                    jQuery('#messenger_form .subscreen_loading').parent().html(res.html);
                }
            }
        });
    });
    // sortable product and user fields
    jQuery( "#toe_opt_productFieldsTab, #toe_opt_userFieldsTab" ).sortable({ 
            items: 'tr.toe_admin_row',
            cursor: 'move', 
            forcePlaceholderSize: true,
            update: function(event, ui) {
                    var fields = [];
                    jQuery('tr.toe_admin_row', this).each(function(index){
                            field_id = jQuery(this).find('td:first').text();
                            fields[index] = field_id;
                    });
                   var data = {
                        fields   : fields,
                        action   : 'sortExtraField',
                        page     : 'options',
                        reqType  : 'ajax'
                    };
                   jQuery.post(ajaxurl,data);
            }
    });
    // show needed blocks at extra field add form

    jQuery('.extrafield_type').live('change',function(){
       
       var selector = '#'+jQuery(this).attr('rel'); 
       var parent = jQuery(selector);
       var type = jQuery(this).val();
        switch (type){
           case '1':
               jQuery(this).parent().parent().next('tr').show();
               parent.find('.options_tag').hide(TOE_DATA.animationSpeed);
           break;
           case '5':
              parent.find(".options_tag .add_option").text(TOE_LANG.add_checkbox);
              parent.find(".options_tag").show(TOE_DATA.animationSpeed);
              parent.find(".image_tag").hide(TOE_DATA.animationSpeed);
              jQuery(this).parent().parent().next('tr').hide(); 
           break;
           case '10':
              parent.find(".options_tag .add_option").text(TOE_LANG.add_radiobutton);
              parent.find(".options_tag").show(TOE_DATA.animationSpeed);
              parent.find(".image_tag").hide(TOE_DATA.animationSpeed);
              jQuery(this).parent().parent().next('tr').hide();
           break;
           case '9': case '12':
              parent.find(".options_tag .add_option").text(TOE_LANG.add_item);
              parent.find(".options_tag").show(TOE_DATA.animationSpeed);
              parent.find(".image_tag").hide(TOE_DATA.animationSpeed);
              jQuery(this).parent().parent().next('tr').hide();
           break
           case '8':
              parent.find(".image_tag").show(TOE_DATA.animationSpeed);
              parent.find(".options_tag").hide(TOE_DATA.animationSpeed);
              jQuery(this).parent().parent().next('tr').hide();
               break
           default:
               parent.find(".options_tag").hide(TOE_DATA.animationSpeed);
               parent.find(".image_tag").hide(TOE_DATA.animationSpeed);
               jQuery(this).parent().parent().next('tr').hide();
           break
        }
    });
    jQuery('.toeMultipleSelectWithSelectAll').live('change', function(){
        var currValue = jQuery(this).val();
        if(currValue == 0) {
            var allOptionsSelected = jQuery(this).hasClass('toeAllSelected');
            if(allOptionsSelected) {
                jQuery(this).find('option').removeAttr('selected');
                jQuery(this).removeClass('toeAllSelected');
            } else {
                jQuery(this).find('option').attr('selected', 'selected');
                jQuery(this).addClass('toeAllSelected');
            }
        }
        
        

        //alert(jQuery(this).val());
    });
    /*jQuery('#select_product_field_cat').live('click', function(){
        var select = jQuery(this);
        var select_all = select.find('option[value="0"]');
        if (select.val() == 0) {
            if (select.attr('rel') == 0) {
                select.find('option').each(function(){
                    jQuery(this).attr('selected','true');
                });
                select_all.text(TOE_DESELECT_ALL);
                select.attr('rel', 1);
            } else {
                select.find('option').each(function(){
                    jQuery(this).removeAttr('selected');
                });
                select_all.text(TOE_SELECT_ALL);
                select.attr('rel', 0);
            }
        } 
    });*/
    // select all categories
    jQuery('#editProductfieldForm input[type="checkbox"]').live('click', function(){
        var value = jQuery(this).val();
        var checked = jQuery(this).attr('checked');
        if (value == 'all') {
            if (checked == 'checked') {
                jQuery(this).parent().find('input').each(function(){
                    jQuery(this).attr('checked', 'checked'); 
                });
            } else {
                jQuery(this).parent().find('input').each(function(){
                    jQuery(this).removeAttr('checked'); 
                });
            }   
        }
    });
    // set id and class fields
    jQuery('.set_properties').live('click',function(){
        jQuery('.attributes').show(TOE_DATA.animationSpeed);
        jQuery(this).hide(TOE_DATA.animationSpeed);
    });
    jQuery('.tab_form h1').live('click', function(){
        if (jQuery('.tab_form form').is(':visible')){
            jQuery('.tab_form form').hide(TOE_DATA.animationSpeed);
        } else {
            jQuery('.tab_form form').show(TOE_DATA.animationSpeed);
        }
    });
});
// edit tax 
jQuery.fn.toeGetEditTax = function(e) {
    if(removeClicked) return;
    var id = jQuery(this).children('td:first').text();
    var data = 'reqType=ajax&page=taxes&action=getEditTax&id='+ id;
    //var loading = '<p class="subscreen_loading">'+TOE_LOADING+'</p>';
    jQuery("#tax_form").html(toeLoadElement);
    jQuery.ajax({
        url: ajaxurl,
        data: data,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if(res.html) {
                jQuery('#tax_form .subscreen_loading').parent().html(res.html);
            }
        }
    });
}
// remove currency
function removeCurrency(remLink) {
    return removeOpt(remLink, 'reqType=ajax&page=currency&action=delete', 'toe_opt_currency');
}
// remove userfield
function removeUserfield(remLink) {
    return removeOpt(remLink, 'reqType=ajax&page=options&action=deleteUserfield', 'toe_opt_userfield');
}
// remove productfield
function removeProductfield(remLink) {
    return removeOpt(remLink, 'reqType=ajax&page=options&action=deleteProductfield', 'toe_opt_productfield');
}
// remove tax
function removeTax(remLink) {
    return removeOpt(remLink, 'reqType=ajax&page=taxes&action=delete', 'toe_opt_taxes');
}
// remove option
function removeOpt(remLink, url, trClass) {
    removeClicked = true;
    var id = parseInt(jQuery(remLink).parents('.'+ trClass).find('td:first').text());
    var data = url+ '&id='+id;
    jQuery.ajax({
        url: ajaxurl,
        data: data,
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            removeClicked = false;
            if(!res.error) {
                jQuery('.'+ trClass).each(function(){
                    if(parseInt(jQuery(this).find('td:first').text()) == id) {
                        jQuery(this).remove();
                    }
                });
            }
        }
    });
    return false;
}
/**
 * Show description for options
 */
function toeOptShowDescription(description, x, y, moveToLeft) {
    if(typeof(description) != 'undefined' && description != '') {
        if(!jQuery('#toeOptDescription').size()) {
            jQuery('body').append('<div id="toeOptDescription"></div>');
        }
		if(moveToLeft)
			jQuery('#toeOptDescription').css('right', jQuery(window).width() - (x - 10));	// Show it on left side of target
		else
			jQuery('#toeOptDescription').css('left', x + 10);
        jQuery('#toeOptDescription').css('top', y);
        jQuery('#toeOptDescription').show(200);
        jQuery('#toeOptDescription').html(description);
    }
}
/**
 * Hide description for options
 */
function toeOptHideDescription() {
	jQuery('#toeOptDescription').removeAttr('toeFixTip');
    jQuery('#toeOptDescription').hide(200);
}
/**
 * This function will help us not to hide desc right now, but wait - maybe user will want to select some text or click on some link in it.
 */
function toeOptTimeoutHideDescription() {
	jQuery('#toeOptDescription').removeAttr('toeFixTip');
	setTimeout(function(){
		if(!jQuery('#toeOptDescription').attr('toeFixTip'))
			toeOptHideDescription();
	}, 500);
}
function toeRemoveTextFieldsDynamicTable(link) {
    jQuery(link).parents('.toeTextFieldDynamicRow:first').remove();
}
function toeAddTextFieldsDynamicTable(link, optsCount) {
    var newRow = jQuery(link).parents('.toeTextFieldsDynamicTable:first').find('.toeTextFieldDynamicRow:last').clone();
    jQuery(newRow).find('input').each(function(){                   //Govnokod? maybe... if so - try to do it better. good luck!
        var name = jQuery(this).attr('name');               //sites_3[3][0]
        var varName = name.substr(0, strpos(name, '['));    //sites_3
        name = name.substr(strpos(name, '[')+1);            //3][0]
        name = name.substr(0, name.length-1);               //3][0
        name = str_replace(name, '[', '');                  //3]0
        var currNameArr = name.split(']');                  //3,0
        if(optsCount == 2) {                                //It is realy govnokod - hardcode for admin - shipping - table rate
            currNameArr[2] = parseInt(currNameArr[2]) + 1;      //4,0
        } else {
            if(isNumber(currNameArr[0]))
                currNameArr[0] = parseInt(currNameArr[0]) + 1;      //4,0
            else if(isNumber(currNameArr[1]))
                currNameArr[1] = parseInt(currNameArr[1]) + 1;      //4,0
        }
        name = currNameArr.join('][');                      //4][0
        name = varName + '['+ name+ ']';
        jQuery(this).attr('name', name);
        jQuery(this).val('');
    });
    jQuery(newRow).find('input').val('');
    jQuery(link).parents('.toeTextFieldsDynamicTable:first').find('.toeTextFieldDynamicRow:last').after(newRow);
}
function toeChangeCurrencyViewSelect() {
    var newCurrViewArr = {};
    if(jQuery('input[name=symbol]').val() == '') {
        newCurrViewArr = toeDefaultCurrViewSelect;
    } else {
        var newCurrSymbol = jQuery('input[name=symbol]').val();
        for(var id in toeDefaultCurrViewSelect) {
            newCurrViewArr[id] = str_replace(toeDefaultCurrViewSelect[id], '$', newCurrSymbol);
        }
    }
    jQuery('select[name=currency_view]').toeRebuildSelect(newCurrViewArr, true, jQuery('input[name=currency_view]').val());

    //jQuery('input[name=currency_view]').val( jQuery('select[name=currency_view_select]').val() );
    //jQuery('input[name=price_view]').val( jQuery('select[name=price_view_select]').val() );

    //prostite za govnokod............
    jQuery('select[name=price_view]').children('option[value='+ jQuery('input[name=price_view]').val()+ ']').attr('selected', 'selected');
}
function toeCheckCurrencyPriceView(checkWhat) {
    if(typeof(checkWhat) == 'undefined')
        checkWhat = 'all';
    if(checkWhat == 'all' || checkWhat == 'currencyView') {
        var symbol = jQuery('input[name=symbol]').val();
        jQuery('input[name=symbol_left]').val('');
        jQuery('input[name=symbol_right]').val('');
        switch(jQuery('select[name=currency_view]').val()) {
            case '$1':
                jQuery('input[name=symbol_left]').val(symbol);
                break;
            case '$ 1':
                jQuery('input[name=symbol_left]').val(symbol+ ' ');
                break;
            case '1$':
                jQuery('input[name=symbol_right]').val(symbol);
                break;
            case '1 $':
                jQuery('input[name=symbol_right]').val(' '+ symbol);
                break;
        }
    }
    if(checkWhat == 'all' || checkWhat == 'priceView') {
        jQuery('input[name=symbol_point]').val('');
        jQuery('input[name=symbol_thousand]').val('');
        jQuery('input[name=decimal_places]').val('');
        switch(jQuery('select[name=price_view]').val()) {
            case '100 000.00':
                jQuery('input[name=symbol_point]').val('.');
                jQuery('input[name=symbol_thousand]').val(' ');
                jQuery('input[name=decimal_places]').val('2');
                break;
            case '100000.00':
                jQuery('input[name=symbol_point]').val('.');
                jQuery('input[name=decimal_places]').val('2');
                break;
            case '100 000,00':
                jQuery('input[name=symbol_point]').val(',');
                jQuery('input[name=symbol_thousand]').val(' ');
                jQuery('input[name=decimal_places]').val('2');
                break;
            case '100000,00':
                jQuery('input[name=symbol_point]').val(',');
                jQuery('input[name=decimal_places]').val('2');
                break;
            case '100.000,00':
                jQuery('input[name=symbol_point]').val(',');
                jQuery('input[name=symbol_thousand]').val('.');
                jQuery('input[name=decimal_places]').val('2');
                break;
            case '100 000':
                jQuery('input[name=symbol_thousand]').val(' ');
                break;
        }
    }
}
function toeSwitchModuleStatus(link) {
    notOnRowClicked = true;
    if(jQuery(link).parents('tr').find('td.type').html() == 'system') return;
    var id = getIdFromTable(link);
    if(id) {
        jQuery(this).sendForm({
            data: {id: id, action: 'putModule', page: 'options', reqType: 'ajax', active: jQuery(link).hasClass('toeOptDisabled')},
            msgElID: 'toeOptModulesMsg',
            onSuccess: function(res) {
                if(toeTables['modules_list_payment']) {
                    toeTables['modules_list_payment'].redrawRow('id', res.data.id, res.data);
                }
                /*if(toeTables['modules_list_shipping']) {
                    toeTables['modules_list_shipping'].redrawRow('id', res.data.id, res.data);
                }*/
                if(toeTables['modules_list']) {
                    toeTables['modules_list'].redrawRow('id', res.data.id, res.data);
                }
            }
        });
    }
    return ;
}
function toeSwitchUserfieldStatus(link) {
    notOnRowClicked = true;
    var id = getIdFromTable(link);
    if(id) {
        jQuery(this).sendForm({
            data: {id: id, 
                action: 'putUserfield', 
                page: 'options', 
                reqType: 'ajax', 
                active: jQuery(link).hasClass('toeOptDisabled') ? 1 : 0,
                ignore: ['mandatory', 'ordering']},
            msgElID: 'toeOptModulesMsg',
            onSuccess: function(res) {
                if(toeTables['user_fields_list']) {
                    toeTables['user_fields_list'].redrawRow('id', res.data.id, res.data);
                }
            }
        });
    }
}

function toeSwitchSpecialProductStatus(link) {
    notOnRowClicked = true;
    var id = getIdFromTable(link);
    if(id) {
        jQuery(this).sendForm({
            data: {id: id, 
                action: 'storeSpecialProduct', 
                page: 'special_products', 
                reqType: 'ajax', 
                active: jQuery(link).hasClass('toeOptDisabled') ? 1 : 0,
                ignore: ['absolute', 'mark_as_sale', 'apply_to']},
            msgElID: 'speshialProductsMsg',
            onSuccess: function(res) {
                if(toeTables['toe_special_products']) {
                    toeTables['toe_special_products'].redrawRow('id', res.data.id, res.data);
                }
            }
        });
    }
}
/**
 *retrives ID from table by link in row in which exist element with class "id"
 **/
function getIdFromTable(link) {
    return parseInt(jQuery(link).parents('tr').find('td.id').html());
}
function toeSpSwitchSelectBox(checkbox, boxId) {
    if(jQuery(checkbox).attr('checked') == 'checked')
        jQuery('#'+ boxId).slideDown(TOE_DATA.animationSpeed);
    else {
        jQuery('#'+ boxId).slideUp(TOE_DATA.animationSpeed);
        jQuery('label[for='+ jQuery(checkbox).attr('id')+ ']').attr('aria-pressed', 'false');
        jQuery('label[for='+ jQuery(checkbox).attr('id')+ ']').removeClass('ui-state-active');
    }
}
/**
 * Slider widget functions
 **/
function toeSliderCompleteSubmitNewFile(file, res) {
    if(res.error) {
        alert(res.errors[0]);
    } else {
        toeSliderDrawImageData(res.data);
    }
}
function toeSliderDrawImageData(data) {
    //alert(data.fieldName);
    var box = jQuery('#toeUploadbut_'+ data.fieldName).parents('.toeSliderWidgetOptions:first').find('.toeSliderImagesInputsBox:first');
    var newCell = jQuery(box).find('.toeSliderWidgetImagesInputsExample:first').clone();
    var newIdDataArr = jQuery(box).find('.toeSliderWidgetImagesInputs:last').find('input[type=text]:first').attr('name').split('__');
    var newId = newIdDataArr[1] == 'replId' ? 0 : parseInt(newIdDataArr[1])+1;
    if(data.type == 'product') {
        jQuery(newCell).find('img:first').remove();
        jQuery(newCell).find('input[type=text][name*="link"]:first').val('').hide().    //Hide input fields with it's label and <br> element
            prev('label').hide().               
            next('input').next('br').hide();
        jQuery(newCell).find('textarea:first').val('').hide().
            prev('label').hide().
            next('textarea').next('br').hide();
        jQuery(newCell).find('input[type=text][name*="title"]:first').attr('readonly', 'readonly');
    } else {
        jQuery(newCell).find('img:first').attr('src', data.path);
        jQuery(newCell).find('input[type=text][name*="link"]:first').val(data.link);
        jQuery(newCell).find('textarea:first').val(data.desc);
    }
    jQuery(newCell).find('input[type=hidden][name*="path"]:first').val(data.path);  //For product this will be product ID, for slides - path to image
    jQuery(newCell).find('input[type=text][name*="title"]:first').val(data.title);
    jQuery(newCell).find('input[type=text][name*="order"]:first').val(data.order);
    if(data.type != '' && typeof(data.type) != 'undefined')
        jQuery(newCell).find('input[type=hidden][name*="type"]:first').val(data.type);
    
    jQuery(newCell).find('input, textarea').removeAttr('disabled');
    jQuery(newCell).find('input, textarea').each(function(){
        var name = str_replace(jQuery(this).attr('name'), '__replId__', '__'+ newId+ '__');
        jQuery(this).attr('name', name);
    });
    jQuery(newCell).removeClass('toeSliderWidgetImagesInputsExample');
    jQuery(box).append(newCell);
}
function toeRemoveSlide(delElement) {
    var parentBox = jQuery(delElement).parents('.toeSliderWidgetImagesInputs:first');
    jQuery(delElement).sendForm({
        msgElID: 'none',
        data: {page: 'slider_widget', action: 'removeFile', reqType: 'ajax', filePath: jQuery(parentBox).find('img:first').attr('src')},
        onSuccess: function(res) {
            jQuery(parentBox).remove();
        }
    });
}
function toeSliderDrawImageDataList(imagesData, uniqBoxId) {
    if(imagesData.length) {
        var fieldName = jQuery('#'+ uniqBoxId).find('button:first').attr('id');
        for(var i = 0; i < imagesData.length; i++) {
            imagesData[i]['fieldName'] = str_replace(fieldName, 'toeUploadbut_', '');
            toeSliderDrawImageData(imagesData[i]);
        }
    }
}
function toeSliderGetProductsList(params) {
    if(typeof(params) != 'object') {
        params = {msgElID: '', uniqBoxId: ''};
    }
    jQuery(this).sendForm({
        msgElID: params.msgElID,
        data: {mod: 'slider_widget', action: 'getProductsListHtml', reqType: 'ajax', uniqBoxId: params.uniqBoxId},
        onSuccess: function(res) {
            if(res.html != '')
                subScreen.show(res.html);
        }
    });
}
function toeSliderSelectProducts(form) {
    var uniqBoxId = jQuery(form).find('input[name=uniqBoxId]:first').val();
    var fieldName = str_replace(jQuery('#'+ uniqBoxId).find('button:first').attr('id'), 'toeUploadbut_', '');

    var selectedProductsOptions = jQuery(form).find('select option:selected');
    if(jQuery(selectedProductsOptions).size()) {
        jQuery(selectedProductsOptions).each(function(){
            var prodAddData = {
                path: jQuery(this).val(),
                title: jQuery(this).html(),
                order: 0,
                fieldName: fieldName,
                type: 'product'
            };
            toeSliderDrawImageData(prodAddData);
        });
    }
    subScreen.hide();
}
function toeSliderAddProductHtml() {
    
}