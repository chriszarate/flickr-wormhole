<?php

/*
	Flickr Wormhole - Home
	http://chris.zarate.org/flickr-set
*/


#	Include Flickr functions:
	require_once ('flickr_include.php');


#	Initialize REST parameters array:
	$rest_params  = Array();


#	Set parameters for request:

	$flickr_cache_file = 'flickr_collections.txt';

	$rest_params['auth_token'] = $flickr_auth_token;
	$rest_params['api_key']    = $flickr_api_key;
	$rest_params['method']     = 'flickr.collections.getTree';
	$rest_params['format']     = 'php_serial';


#	Get serialized tree of collections and photosets:

	$flickr_data = unserialize(FlickrCall($rest_params, $flickr_api_sig, $cache_dir . $flickr_cache_file, true));


#	Build page:

?>

<!DOCTYPE html>
<html>

	<head>

		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex,nofollow,noarchive">

		<style type="text/css">

			body {
				width: 35em;
				margin: 3em;
				font-size: 1.25em;
				font-family: sans-serif;
				line-height: 1.5em;
			}

			p { margin: 0; }

		</style>

		<title><?= $page_title ?></title>

	</head>

	<body>

		<h1><?= $page_title ?></h1>

		<? array_walk ( $flickr_data, 'FlickrTreeWalk', 3 ); ?>

	</body>

</html>
