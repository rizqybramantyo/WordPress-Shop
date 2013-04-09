function taxChangeType(type) {
    jQuery('.tax_type_data').hide();
    switch(type) {
        case 'address':
            jQuery('#tax_address').show('slow');
            break;
        case 'category':
            jQuery('#tax_category').show('slow');
            break;    
    }
}

function scCancelColorPicker(){
  var oldColor = jQuery("#colorpicker").attr('old-color');
  if (oldColor) {
    jQuery.farbtastic("#colorpicker").setColor(oldColor);
    jQuery("#colorpicker").hide();
  }
}

jQuery(function() {
    // Start taxes tab javascript
    jQuery('#editTaxForm').live('submit',function(){
        jQuery(this).sendForm({msgElID: 'mod_msg_tax', onSuccess: function(res) {
				if(!res.error) {
					if(jQuery('#taxes_list').length) {  //For using in adminPage.php template
						var mainTableUpdated = false;
						jQuery('.toe_opt_taxes').each(function(){
							if(jQuery(this).children('td:first').text() == res.data.id) {
								jQuery(this).children('td').each(function(iter, el){
									switch(iter) {
										case 1:
											jQuery(el).html(res.data.label);
											break;
										case 2:
											jQuery(el).html(res.data.code);
											break;
									}
								});
								mainTableUpdated = true;
							}
						});
						if(!mainTableUpdated) { //No Updates - add new row
							var table = jQuery('#taxes_list');
							jQuery(table).append('<tr class="toe_admin_row toe_opt_taxes"></tr>');
							var tr = jQuery(table).find('tr:last');
							for(var id in res.data) {
								jQuery(tr).append('<td>'+ res.data[id]+ '</td>');
							}
							jQuery(tr).append('<td><a href="#" class="toe_opt_remove_tax" onclick="removeTax(this); return false;"><img src="'+ TOE_DATA.close+'" /></a></td>');
							jQuery(tr).live('click', function(e) {
								jQuery(this).toeGetEditTax(e);
							});
						}
					}
					if(jQuery('#editTaxForm').find('input[name=id]').val() == 0) { //INSERT action
						jQuery('#editTaxForm').clearForm();
					}
				}
            }}
        );
        return false;
    });
    jQuery('select[name=type]').live('change',function(){
        taxChangeType(jQuery(this).val());
    });
    // end taxes tab javascript
    
    // start user fields tab
    jQuery('#editUserfieldForm').live('submit',function(){
        jQuery(this).sendForm({msgElID: 'mod_msg_user', onSuccess: function(res) {
                if(toeTables['user_fields_list']) {
                    if(!toeTables['user_fields_list'].redrawRow('id', res.data.id, res.data)) {
                        toeTables['user_fields_list'].draw([res.data]);
                    }
                }
                if(jQuery('#editUserfieldForm').find('input[name=id]').val() == 0 && !res.error) { //INSERT action
                    jQuery('#editUserfieldForm').clearForm();
                }
            }}
        );
        return false;
    });
    // end user field tab
    
    // start currency tab
    jQuery('#editCurrencyForm').live('submit',function(){
        toeCheckCurrencyPriceView();    //To fill currency and price view fields with correct data
        jQuery(this).sendForm({msgElID: 'mod_msg_curr', onSuccess: function(res) {
                if(jQuery('.toe_opt_currency').length) {  //For using in adminPage.php template
                    if(!res.error) {
                        var mainTableUpdated = false;
                        jQuery('.toe_opt_currency').each(function(){
                            if(jQuery(this).children('td:first').text() == res.data.id) {
                                jQuery(this).children('td').each(function(iter, el){
                                    switch(iter) {
                                        case 1:
                                            jQuery(el).html(res.data.label);
                                            break;
                                        case 2:
                                            jQuery(el).html(res.data.code);
                                            break;
                                    }
                                });
                                mainTableUpdated = true;
                            }
                        });
                        if(!mainTableUpdated) { //No Updates - add new row
                            var table = jQuery('#toe_opt_currencyTab').children('table');
                            jQuery(table).append('<tr class="toe_admin_row toe_opt_currency"></tr>');
                            var tr = jQuery(table).find('tr:last');
                            for(id in res.data) {
                                jQuery(tr).append('<td>'+ res.data[id]+ '</td>');
                            }
                            jQuery(tr).append(jQuery('.toe_opt_remove_currency:first').clone());
                        }
                    }
                    if(jQuery('#editCurrencyForm').find('input[name=id]').val() == 0 && !res.error) { //INSERT action
                        jQuery('#editCurrencyForm').clearForm();
                    }
                }
            }}
        );
        return false;
    });
    // end currency tab
    // start product field tab
    jQuery('#editProductfieldForm').live('submit',function(){
        jQuery(this).sendForm({msgElID: 'mod_msg_prod', onSuccess: function(res) {
                var mainTableUpdated = false;
                if(jQuery('.toe_opt_productfield').length) {  //For using in adminPage.php template
                    jQuery('.toe_opt_productfield').each(function(){
                        if(jQuery(this).children('td:first').text() == res.data.id) {
                            jQuery(this).children('td').each(function(iter, el){
                                switch(iter) {
                                    case 1:
                                        jQuery(el).html(res.data.label);
                                        break;
                                    case 2:
                                        jQuery(el).html(res.data.code);
                                        break;
                                    case 3:
                                        jQuery(el).html(res.data.type);
                                        break;
                                }
                            });
                            mainTableUpdated = true;
                        }
                    });
                } else if(jQuery('#product_fields_list').size() == 0) {
                    document.location.reload();
                } else {    //for using at products page
                    if (res.field != '') {
                        var new_field = '<div class="product_extra">'+res.field+'</div>';
                        jQuery('#product_extras').append(new_field);
                    }
                }
                if(!mainTableUpdated) { //No Updates - add new row
                    var table = jQuery('#product_fields_list');
                    jQuery(table).append('<tr class="toe_admin_row toe_opt_productfield"></tr>');
                    var tr = jQuery(table).find('tr:last');
                    for(id in res.data) {
                        jQuery(tr).append('<td>'+ res.data[id]+ '</td>');
                    }
                    jQuery(tr).append('<td><a href="#" class="toe_opt_remove_productfield" onclick="removeProductfield(this); return false;"><img src="'+ TOE_DATA.close+ '" /></a></td>');
                }
                if(jQuery('#editProductfieldForm').find('input[name=id]').val() == 0) { //INSERT action
                    jQuery('#editProductfieldForm').clearForm();
                }
            }}
        );
        return false;
    });
    // end product field tab
    // Start taxes tab javascript
    jQuery('#editTemplateForm').live('submit',function(){
        jQuery(this).sendForm({msgElID: 'mod_msg_template', onSuccess: function(res) {
                if(jQuery('.toe_opt_messenger_template').length) {  //For using in adminPage.php template
                    jQuery('.toe_opt_messenger_template').each(function(){
                        if(jQuery(this).children('td:first').text() == res.data.id) {
                            jQuery(this).children('td').each(function(iter, el){
                                switch(iter) {
                                    case 1:
                                        jQuery(el).html(res.data.label);
                                        break;
                                    case 2:
                                        jQuery(el).html(res.data.code);
                                        break;
                                }
                            });
                        }
                    });
                }
                if(jQuery('#editTemplateForm').find('input[name=id]').val() == 0 && !res.error) { //INSERT action
                    jQuery('#editTemplateForm').clearForm();
                }
            }}
        );
        return false;
    });
      // Color picker. (Farbtastic.

      jQuery("body").append("<div id='colorpicker'></div>");
      
      if(jQuery("#colorpicker").exists() && jQuery(".colorpicker").exists())
        jQuery("#colorpicker").farbtastic(".colorpicker:first").prepend("<span class='ui-icon ui-icon-check'></span>");
      jQuery('.colorpicker').each(function(){
            jQuery.farbtastic("#colorpicker").linkTo(jQuery(this));
      });
      jQuery('.colorpicker').focus(function() {
            jQuery("#colorpicker").hide();
            jQuery.farbtastic("#colorpicker").linkTo(jQuery(this));
            jQuery("#colorpicker").attr('old-color', jQuery.farbtastic("#colorpicker").color);
            var offset = jQuery(this).offset();
            jQuery("#colorpicker").css('left', offset.left - 68).css('top', offset.top + 20).fadeIn(400);
      });
      jQuery("#colorpicker .ui-icon-check").click(function(){
            jQuery("#colorpicker").hide();
      });
      jQuery('.colorpicker').live('click',function(){
            jQuery("#colorpicker").attr('old-color', jQuery.farbtastic("#colorpicker").color);
            jQuery("#colorpicker").show();
      });
      jQuery('.colorpicker').keydown(function(event) {
            // Esc.
            if (event.keyCode == 27) {scCancelColorPicker()}
            // Enter.
            if (event.keyCode == 13) {
              jQuery("#colorpicker .ui-icon-check").click();
              event.preventDefault();
            }
            // Space.
            if (event.keyCode == 32) {jQuery("#colorpicker").show();}
      });
	
});