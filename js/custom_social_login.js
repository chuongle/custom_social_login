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
	  		});	
	  	});
	  }
	}
})(jQuery)