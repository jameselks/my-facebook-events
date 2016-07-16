//(function( $ ) {
	'use strict';

	jQuery(function() {
		jQuery('#status').append( '<p>Loading Facebook things.</p>' );
		jQuery('#progress').hide('slow');
	});

	// This is called with the results from from FB.getLoginStatus().
	function statusChangeCallback(response, doExchange) {
		
		// The response object is returned with a status field that lets the app know the current login status of the person.
		// Full docs on the response object can be found in the documentation for FB.getLoginStatus().
		
		if (response.status === 'connected') {
			// Logged into your app and Facebook.
			showStatus();
			if (doExchange) {
				exchangeToken();
			}

		} else if (response.status === 'not_authorized') {
			// The person is logged into Facebook, but not your app.
			document.getElementById('status').innerHTML = 'Please log ' + 'into this app.';

		} else {
			// The person is not logged into Facebook, so we're not sure if
			// they are logged into this app or not.
			document.getElementById('status').innerHTML = 'Please log ' + 'into Facebook.';

		}
	}

	// This function is called when someone finishes with the Login Button.  See the onlogin handler attached to it in the sample code below.
	function checkLoginState() {
		FB.getLoginStatus(function(response) {
		  statusChangeCallback(response, true);
		});
	}

	window.fbAsyncInit = function() {

		jQuery('#status').append( '<p>Downloading the Facebook Javascript SDK.</p>' );
		jQuery('#status').append( '<p>Using app ID ' + e2js.fbAppId + '.</p>' );

		FB.init({
			appId      : e2js.fbAppId, //'490280357839488',
			cookie     : true,  // enable cookies to allow the server to access the session
			xfbml      : true,  // parse social plugins on this page
			version    : 'v2.6' // use graph api version 2.6
		});

		FB.getLoginStatus(function(response) {
			statusChangeCallback(response, false);
		});

	};


	// Load the SDK asynchronously
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = '//connect.facebook.net/en_US/sdk.js';
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));

	// Here we run a very simple test of the Graph API after login is successful. See statusChangeCallback() for when this call is made.
	function showStatus() {
		FB.api('/me', function(response) {
			console.log(response);
			jQuery('#status').append( '<p>Logged in &mdash; ' + response.name + '.</p>' );
		});
	}

	function exchangeToken() {
		jQuery('#status').append( '<p>Getting the long-lived token.</p>' );
		jQuery.ajax({
			type: 'post',
			url: ajaxurl,
			data: {
				'action': 'e2_fb_tokenexchange'
			},
			success: function(result){
				console.log(result);
				jQuery('#status').append( '<p>Long-lived token: ' + result + '</p>' );
				jQuery('#status').append( '<p>Done!</p>' );
				location.reload();
				//jQuery('#fb_longtoken').val( result );
			}
		});			

	}

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

//})( jQuery );