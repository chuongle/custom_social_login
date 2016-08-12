(function ($) {
	Drupal.behaviors.custom_social_login = {
	  attach: function (context, settings) {
	  	$('body').once('custom-login').each(function() {
	  		$(document).ready(function() {
	  			$('.sign-in-link').click(function(e) {
	  				e.preventDefault();
	  				$('.login-steps').addClass('w-hidden-main');
	  				$('#email-login-wrapper').removeClass('w-hidden-main');
	  			});
	  			$('.social-login-link').click(function(e) {
	  				e.preventDefault();
	  				$('.login-steps').addClass('w-hidden-main');
	  				$('#social-login-wrapper').removeClass('w-hidden-main');
	  			});
	  			$('.create-an-account-link').click(function(e) {
	  				e.preventDefault();
	  				$('.login-steps').addClass('w-hidden-main');
	  				$('#create-account-wrapper').removeClass('w-hidden-main');
	  			});
	  			$('.forgot-password-link').click(function(e) {
	  				e.preventDefault();
	  				$('.login-steps').addClass('w-hidden-main');
	  				// $('#create-account-wrapper').removeClass('w-hidden-main');
	  			});
	  			$('.email-login-link').click(function(e) {
	  				e.preventDefault();
	  				$('.login-steps').addClass('w-hidden-main');
	  				$('#email-login-wrapper').removeClass('w-hidden-main');
	  			});
	  			$('.btn-login.fb, .btn-login.tw, .btn-login.gl').on('click', function(e) {
	  				Cookies.set('current_page', window.location.pathname+window.location.search);
	  			});
	  			var ix = Webflow.require('ix');
	  			var modal_reset_pass_trigger = {"type":"click","selector":".modal-reset-pass","stepsA":[{"display":"block"},{"opacity":1,"transition":"transform 200ms ease 0ms, opacity 200ms ease 0ms","scaleX":1,"scaleY":1,"scaleZ":1}],"stepsB":[]};
	  			$(document).ready(function() {
	  				if(GetQueryParam('setpassword')) {
	  					ix.run(modal_reset_pass_trigger);
	  				}
	  			});
	  		});	
	  	});
	  }
	}	

	function GetQueryParam(VarSearch){
	  var SearchString = window.location.search.substring(1);
	  var VariableArray = SearchString.split('&');
	  for(var i = 0; i < VariableArray.length; i++){
	    var KeyValuePair = VariableArray[i].split('=');
	    if(KeyValuePair[0] == VarSearch){
	      return KeyValuePair[1];
	    }
	  }
	}

})(jQuery)
