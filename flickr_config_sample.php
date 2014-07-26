<?php

/*
	Flickr Wormhole - Configuration
	https://github.com/chriszarate/flickr-wormhole
*/


/* IMPORTANT: Copy this file to `flickr_config.php` and edit. */


#	API values:
	$flickr_api_key     = '';
	$flickr_api_sig     = '';
	$flickr_auth_token  = '';


#	Cache directory (relative or literal; must be writeable by PHP):
	$cache_dir = 'cache/';


#	Page title:
	$page_title = 'My Flickr Sets';


#	URL or path for "Home" links:
	$home_link = '/';


#	API endpoint:
	$cfg_flickr_api_endpoint = 'https://api.flickr.com/services';


#	Debugging options:
	$cfg_disable_caching = false;
	$cfg_disable_conditional_get = false;


?>
