jQuery(document).ready(function() {
	jQuery('.woocommerce-form-login button').after('<div class="login_msg success" style="display:none"></div>'); 
	jQuery('.woocommerce-form-login button').after('<div class="login_msg fail" style="display:none"></div>'); 
	jQuery('.woocommerce-form-login button').after('<div class="login_loader" style="display:none"><img src="'+zorem_ajax_object.plugin_dir_url+'assets/images/wpspin.gif">'+zorem_ajax_object.loading_text+'</div>');		
});
/*
* AJAX login
*/
jQuery(document).on("submit", '.woocommerce-form-login', function(e){

	var form = jQuery(this);
	var error;
	var username = form.find("#username");
	var password = form.find("#password");
	var login_captcha = form.find("#login_captcha");
	
	if( username.val() === '' ){
		form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror( username );error = true;
	} else{
		hideerror(username);
	}	
	if(password.val() == '' ){
		form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror(password);error = true;
	} else {
		hideerror(password);
	}
	
	if(login_captcha.val() == '' ){form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();showerror(login_captcha);error = true;} else {hideerror(login_captcha);}
	
	if(error == true){
		return false;
	}
	form.find(".login_loader").show();
	form.find(".login_msg").hide();
	jQuery.ajax({
		type: 'POST',
		dataType: 'json',
		url: zorem_ajax_object.ajax_url,
		data: form.serialize(),
		success: function(data){
			form.find(".login_loader").hide();

			if (data.loggedin == true){
				form.find(".login_msg.success").html(data.message).show();
				if ( data.redirect != false ) {
					window.location = data.redirect;
				} else {
					window.location.reload();
				}
			} else {
				if(data.invalid_captcha == true){
					showerror( login_captcha );
					login_captcha.removeClass('input-success-icon');
					login_captcha.addClass('input-failure-icon');
				} else{
					hideerror(login_captcha);
					login_captcha.addClass('input-success-icon');
					login_captcha.removeClass('input-failure-icon');
				}
				if(data.invalid_username == true){
					showerror( username );
					username.removeClass('input-success-icon');
					username.addClass('input-failure-icon');
				} else{
					hideerror(username);
					username.addClass('input-success-icon');
					username.removeClass('input-failure-icon');
				}
				if(data.incorrect_password == true){
					showerror( password );
					password.removeClass('input-success-icon');
					password.addClass('input-failure-icon');
				} else{
					hideerror(password);
					password.addClass('input-success-icon');
					password.removeClass('input-failure-icon');
				}
				form.find(".login_msg.fail").html(data.message).show();
			}
		},
		error: function (jqXHR, exception) {
			
			var msg = '';
			if (jqXHR.status === 0) {
				msg = 'Not connect.\n Verify Network.';
			} else if (jqXHR.status == 404) {
				msg = 'Requested page not found. [404]';
			} else if (jqXHR.status == 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else if (jqXHR.responseText === '-1') {
				msg = 'Please refresh page and try again.';
			} else {
				msg = 'Uncaught Error.\n' + jqXHR.responseText;
			}
			form.find(".login_loader").hide();
			form.find(".login_msg.fail").hide();
			form.find(".login_msg.fail").html(msg).show();
    	},
	});
	e.preventDefault();
});

// Change event of login username
jQuery(document).on("keyup", '.woocommerce-form-login #username', function(e){
	var form = jQuery('.woocommerce-form-login');
	var error;
	var username = form.find("#username");	
	if( username.val() === '' ){		
		showerror( username );
		form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();
		username.removeClass('input-success-icon');
		username.addClass('input-failure-icon');
		error = true;
	} else{
		hideerror(username);
		username.addClass('input-success-icon');
		username.removeClass('input-failure-icon');
	}
	if(error == true){
		return false;
	} else{
		form.find(".login_msg.fail").hide();
	}	 
});

// Change event of login password
jQuery(document).on("keyup", '.woocommerce-form-login #password', function(e){
	var form = jQuery('.woocommerce-form-login');
	var error;	
	var password = form.find("#password");
	if( password.val() === '' ){
		form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();	
		showerror( password );
		password.removeClass('input-success-icon');
		password.addClass('input-failure-icon');
		error = true;
	} else{
		hideerror(password);
		password.addClass('input-success-icon');
		password.removeClass('input-failure-icon');
	}
	if(error == true){
		return false;
	} else{
		form.find(".login_msg.fail").hide();
	}	 
});

// Change event of login captcha
jQuery(document).on("keyup", '.woocommerce-form-login #login_captcha', function(e){
	var form = jQuery('.woocommerce-form-login');
	var error;	
	var login_captcha = form.find("#login_captcha");
	if( login_captcha.val() === '' ){
		form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();	
		showerror( login_captcha );
		login_captcha.removeClass('input-success-icon');
		login_captcha.addClass('input-failure-icon');
		error = true;
	} else{
		hideerror(login_captcha);
		login_captcha.addClass('input-success-icon');
		login_captcha.removeClass('input-failure-icon');
	}
	if(error == true){
		return false;
	} else{
		form.find(".login_msg.fail").hide();
	}	 
});

/*
* AJAX registration
*/
jQuery(document).on("submit", '.woocommerce-form-register', function(e){
	var form = jQuery(this);
	// validation 
	var error;
	var reg_email = form.find("#reg_email");
	var reg_password = form.find("#reg_password");
	var register_captcha = form.find("#register_captcha");
	
	if( reg_email.val() === '' ){
		form.find(".register_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror( reg_email );error = true;		
	} else {
		if( validateEmail( reg_email.val() ) ){
			hideerror( reg_email );						
		} else {
			form.find(".register_msg.fail").text(zorem_ajax_object.valid_email).show();
			showerror( reg_email );error = true;			
		}
	}
	
	if(register_captcha.val() == '' ){form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();showerror(register_captcha);error = true;} else {hideerror(register_captcha);}

	if(reg_password.val() == '' ){
		form.find(".register_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror(reg_password);error = true;		
	} else {
		hideerror(reg_password);		
	}
	
	if(error == true){
		return false;
	}
	
	form.find(".register_loader").show();
	form.find(".register_msg").hide();
	jQuery.ajax({
		type: 'POST',
		dataType: 'json',
		url: zorem_ajax_object.ajax_url,
		data: form.serialize(),
		success: function(data){
			form.find(".register_loader").hide();

			if ( data.code === 200 ){
				form.find(".register_msg.success").text(data.message).show();
				if ( data.redirect != false ) {
					window.location = data.redirect;
				} else {
					window.location.reload();
				}
			} else {
				if(data.invalid_captcha == true){
					showerror( register_captcha );
					register_captcha.removeClass('input-success-icon');
					register_captcha.addClass('input-failure-icon');
				} else{
					hideerror(register_captcha);
					register_captcha.addClass('input-success-icon');
					register_captcha.removeClass('input-failure-icon');
				}	
				form.find(".register_msg.fail").text(data.message).show();
			}
		},
		error: function (jqXHR, exception) {
			
			var msg = '';
			if (jqXHR.status === 0) {
				msg = 'Not connect.\n Verify Network.';
			} else if (jqXHR.status === 404) {
				msg = 'Requested page not found. [404]';
			} else if (jqXHR.status === 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else if (jqXHR.responseText === '-1') {
				msg = 'Please refresh page and try again.';
			} else {
				msg = 'Uncaught Error.\n' + jqXHR.responseText;
			}

			form.find(".register_loader").hide();
			form.find(".register_msg.fail").hide();
			form.find(".register_msg.fail").html(msg).show();
    	},
	});
	e.preventDefault();
});

// Change event of registration email
jQuery(document).on("keyup", '.woocommerce-form-register #reg_email', function(e){
	var form = jQuery('.woocommerce-form-register');
	var error;
	var reg_email = form.find("#reg_email");	
	
	if( reg_email.val() === '' ){
		form.find(".register_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror( reg_email );error = true;
		reg_email.removeClass('input-success-icon');
		reg_email.addClass('input-failure-icon');
	} else {
		if( validateEmail( reg_email.val() ) ){
			hideerror( reg_email );	
			reg_email.addClass('input-success-icon');
			reg_email.removeClass('input-failure-icon');			
		} else {
			reg_email.removeClass('input-success-icon');
			reg_email.addClass('input-failure-icon');			
			showerror( reg_email );
			error = true;			
		}
	}
	
	if(error == true){
		return false;
	} else{
		form.find(".register_msg.fail").hide();
	}	 
});

// Change event of registration password
jQuery(document).on("keyup", '.woocommerce-form-register #reg_password', function(e){
	var form = jQuery('.woocommerce-form-register');
	var error;	
	var reg_password = form.find("#reg_password");
	var strength = checkPasswordStrength( form, reg_password );
	var min_password_strength = wc_password_strength_meter_params.min_password_strength;
	
	if(reg_password.val() == '' ){
		form.find(".register_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror(reg_password);error = true;
		reg_password.removeClass('input-success-icon');
		reg_password.addClass('input-failure-icon');
	} else {			
		if ( strength < min_password_strength ) {
			showerror(reg_password);
			error = true;
			reg_password.removeClass('input-success-icon');
			reg_password.addClass('input-failure-icon');
		} else{
			hideerror(reg_password);
			reg_password.addClass('input-success-icon');	
			reg_password.removeClass('input-failure-icon');
		}		
	}
	
	if(error == true){
		return false;
	} else{
		form.find(".register_msg.fail").hide();
	}	 
});

// Change event of registration password
jQuery(document).on("keyup", '.woocommerce-form-register #register_captcha', function(e){
	var form = jQuery('.woocommerce-form-register');
	var error;	
	var register_captcha = form.find("#register_captcha");
		
	if(register_captcha.val() == '' ){
		form.find(".register_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror(register_captcha);error = true;
		register_captcha.removeClass('input-success-icon');
		register_captcha.addClass('input-failure-icon');
	} else {
		hideerror(register_captcha);
		register_captcha.addClass('input-success-icon');
		register_captcha.removeClass('input-failure-icon');
	}
	
	if(error == true){
		return false;
	} else{
		form.find(".register_msg.fail").hide();
	}	 
});

/*
* AJAX Reset Password
*/
jQuery(document).on("submit", '.woocommerce-ResetPassword', function(e){	
	var form = jQuery(this);
	var reset_key = form.find("input[name='reset_key']").val();
	if(!reset_key){
	jQuery('.woocommerce-Button').prop("disabled", false);
	
	var error;	
	var username = form.find("#user_login");
	var lostpassword_captcha = form.find("#lostpassword_captcha");
	if( username.val() === '' ){
		form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();
		showerror( username );error = true;
	} else{
		hideerror(username);
	}
	
	if(lostpassword_captcha.val() == '' ){form.find(".login_msg.fail").text(zorem_ajax_object.required_message).show();showerror(lostpassword_captcha);error = true;} else {hideerror(lostpassword_captcha);}
	
	if(error == true){
		return false;
	}
	form.find(".login_loader").show();
	form.find(".login_msg").hide();
	jQuery.ajax({
		type: 'POST',
		dataType: 'json',
		url: zorem_ajax_object.ajax_url,
		data: form.serialize(),
		success: function(data){
			form.find(".login_loader").hide();
			if (data.lost_password == true){
				form.find(".login_msg.success").text(data.message).show();
			} else {
				form.find(".login_msg.fail").text(data.message).show();
			}
			
		},
		error: function (jqXHR, exception) {
			
			var msg = '';
			if (jqXHR.status === 0) {
				msg = 'Not connect.\n Verify Network.';
			} else if (jqXHR.status === 404) {
				msg = 'Requested page not found. [404]';
			} else if (jqXHR.status === 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else if (jqXHR.responseText === '-1') {
				msg = 'Please refresh page and try again.';
			} else {
				msg = 'Uncaught Error.\n' + jqXHR.responseText;
			}

			form.find(".login_loader").hide();
			form.find(".login_msg.fail").hide();
			form.find(".login_msg.fail").html(msg).show();
    	},
	});
	e.preventDefault();
	}
});

function checkPasswordStrength( wrapper, field ) {
	var meter     = wrapper.find( '.woocommerce-password-strength' ),
		hint      = wrapper.find( '.woocommerce-password-hint' ),
		hint_html = '<small class="woocommerce-password-hint">' + wc_password_strength_meter_params.i18n_password_hint + '</small>',
		strength  = wp.passwordStrength.meter( field.val(), wp.passwordStrength.userInputBlacklist() ),
		error     = '';

	// Reset.
	meter.removeClass( 'short bad good strong' );
	hint.remove();

	if ( meter.is( ':hidden' ) ) {
		return strength;
	}

	// Error to append
	if ( strength < wc_password_strength_meter_params.min_password_strength ) {
		error = ' - ' + wc_password_strength_meter_params.i18n_password_error;
	}

	switch ( strength ) {
		case 0 :
			meter.addClass( 'short' ).html( pwsL10n['short'] + error );
			meter.after( hint_html );
			break;
		case 1 :
			meter.addClass( 'bad' ).html( pwsL10n.bad + error );
			meter.after( hint_html );
			break;
		case 2 :
			meter.addClass( 'bad' ).html( pwsL10n.bad + error );
			meter.after( hint_html );
			break;
		case 3 :
			meter.addClass( 'good' ).html( pwsL10n.good + error );
			break;
		case 4 :
			meter.addClass( 'strong' ).html( pwsL10n.strong + error );
			break;
		case 5 :
			meter.addClass( 'short' ).html( pwsL10n.mismatch );
			break;
	}

	return strength;
}
		
function validateEmail(value){
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	if (reg.test(value) == false) {
		return false;
	}
	return true;
}
function showerror(element){
	element.css("border-color","red");
}
function hideerror(element){
	element.css("border-color","");
}
