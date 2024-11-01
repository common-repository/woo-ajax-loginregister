(function( $ ){
	$.fn.zorem_snackbar = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var zorem_snackbar = $("<article></article>").addClass('snackbar-log snackbar-log-success snackbar-log-show').text( msg );
		$(".snackbar-logs").empty();
		$(".snackbar-logs").append(zorem_snackbar);
		setTimeout(function(){ zorem_snackbar.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

/* alr_snackbar_warning jquery */
(function( $ ){
	$.fn.zorem_snackbar_warning = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var zorem_snackbar_warning = $("<article></article>").addClass( 'snackbar-log snackbar-log-error snackbar-log-show' ).html( msg );
		$(".snackbar-logs").append(zorem_snackbar_warning);
		setTimeout(function(){ zorem_snackbar_warning.remove(); }, 3000);
		return this;
	}; 
})( jQuery );


jQuery(document).ready(function(){
	"use strict";
	jQuery( '.alr_tab_input:checked' ).trigger('click');
	jQuery(".tipTip").tipTip();
	
	var password_strength = jQuery('.password_strength').val();
	if(password_strength == 0 || password_strength == 1 || password_strength == 2 ){
		jQuery('.desc_hint').text('Hint: The password should be at least 7 characters long. To make it stronger, use upper and lower case letters, numbers.');
	} else if(password_strength == 3){
		jQuery('.desc_hint').text('Hint: The password should be at least 9 characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).');
	} else if(password_strength == 4){
		jQuery('.desc_hint').text('Hint: The password should be at least 11 characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).	');
	} else{
		jQuery('.desc_hint').text(' ');
	}	 		
	
});


/*ajex call for general tab form save*/	 
jQuery(document).on("submit", "#alr_setting_tab_form", function(){
	"use strict";
	jQuery("#alr_setting_tab_form .spinner").addClass("active");
	var form = jQuery('#alr_setting_tab_form');
	jQuery.ajax({
		url: ajaxurl+"?action=alr_setting_form_update",//csv_workflow_update,		
		data: form.serialize(),
		type: 'POST',
		dataType:"json",	
		success: function(response) {
			if( response.success === "true" ){
				jQuery("#alr_setting_tab_form .spinner").removeClass("active");
				jQuery("#sma_general_tab_form").zorem_snackbar( 'Data saved successfully.' );
			} else {
				//show error on front
			}
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery('.password_hint').attr('readonly','readonly');
	jQuery( ".password_strength" ).change(function() {
	//var optionText = $(".zorem_password_strength option:selected").val();
	var password_strength = jQuery(this).val();
	if(password_strength == 0 || password_strength == 1 || password_strength == 2 ){
		jQuery('.desc_hint').text('Hint: The password should be at least 7 characters long. To make it stronger, use upper and lower case letters, numbers.');
	} else if(password_strength == 3){
		jQuery('.desc_hint').text('Hint: The password should be at least 9 characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).');
	} else if(password_strength == 4){
		jQuery('.desc_hint').text('Hint: The password should be at least 11 characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).	');
	} else{
		jQuery('.desc_hint').text(' ');
	}	 		
});

jQuery(document).on( "click", ".zorem_alr_tab_name .alr_tab_input", function(){
	'use strict';
	var tab = jQuery(this).data( "name" );
	jQuery( '.zorem_alr_tab_wraper .alr_tab_section' ).hide();
	jQuery( '#'+tab+'' ).show();
	jQuery(window).trigger('resize');
});
jQuery(document).on("click", ".zorem_alr_tab_name .alr_tab_input", function(){
	'use strict';
	var tab = jQuery(this).data('tab');
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=ajax_login_register&tab="+tab;
	window.history.pushState({path:url},'',url);	
	
	var label = jQuery(this).data('label');
	jQuery( '.zorem-layout__header-breadcrumbs .header-breadcrumbs-last' ).text( label );
});

jQuery( document ).on( "click", "#activity-panel-tab-help", function() {
	jQuery(this).addClass( 'is-active' );
	jQuery( '.woocommerce-layout__activity-panel-wrapper' ).addClass( 'is-open is-switching' );
});

jQuery(document).click(function(){
	var $trigger = jQuery(".woocommerce-layout__activity-panel");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery('#activity-panel-tab-help').removeClass( 'is-active' );
		jQuery( '.woocommerce-layout__activity-panel-wrapper' ).removeClass( 'is-open is-switching' );
    }   
});




