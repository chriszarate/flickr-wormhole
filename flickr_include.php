<?php

/*
	Flickr Wormhole - Functions
	http://chris.zarate.org/flickr-set
*/


#	Start output buffering:
 	ob_start();


#	Set error handler:
	set_error_handler ('FlickrErrorHandler');


#	Include Flickr configuration values:
	require_once ('flickr_config.php');


#	Set content-type and cache headers:
	header ('Content-Type: text/html; charset=utf-8');
	header ('Pragma: public');


#	Make sure cache directory has trailing slash:
	$cache_dir .= ( substr($cache_dir, -1) == '/' ) ? '' : '/';



function ConditionalGet($file)

	{ 

		/*
			Use timestamp to determine if we need to regenerate 
			the page or if we can send a 'Not Modified' header.
		*/


	#	Get config values:
		global $cfg_disable_conditional_get;


	#	If conditional get is disabled, return false:

		if ( $cfg_disable_conditional_get ):
			return false;
		endif;


	#	Get last modified time, if available:

		if ( is_file($file) ):
			$time = filemtime($file);
		else:
			return false;
		endif;


	#	Look for "conditional get" request headers:
		$if_modified_since = ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
		$if_none_match = ( isset($_SERVER['HTTP_IF_NONE_MATCH']) ) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;


	#	Generate our "conditional get" response headers:
		$etag = '"'. md5($time) .'"';
		$date_rfc1123 = substr(gmdate('r', $time), 0, -5) . 'GMT';
		$date_expires_rfc1123 = substr(gmdate('r', (time() + 3)), 0, -5) . 'GMT';
		$date_rfc1036 = gmdate('l, d-M-y H:i:s ', $time) . 'GMT';
		$date_ctime   = gmdate('D M j H:i:s', $time);


	#	Send "conditional get" response headers:
		header ('Last-Modified: ' . $date_rfc1123);
		header ('ETag: ' . $etag);
		header ('Expires: ' . $date_expires_rfc1123);


	#	By default, do not send 'Not Modified' header:
		$send_mod_header = false;


	#	Check "etag" request header:

		if ( $if_none_match ):

			if ( $if_none_match != $etag ):
				return false;
			endif;

			if ( ($if_none_match == $etag) && !($if_modified_since)):
				$send_mod_header = true;
			endif;

		endif;


	#	Check "modified since" request header:

		if ( $if_modified_since && ( ($if_modified_since == $date_rfc1123) || ($if_modified_since == $date_rfc1036) || ($if_modified_since == $date_ctime) ) ):
			$send_mod_header = true;
		endif;


		if ( $send_mod_header ):
			header ( 'HTTP/1.0 304 Not Modified' );
			exit;
		endif;


		return false;


	}



function FlickrCall($rest_params, $api_sig, $cache_file, $return_result)

	{


	#	If caching is requested, check cache first:

		if ( $cache_file && is_file($cache_file) && (time() - filemtime($cache_file)) < 86400 ):
			return ( $return_result ) ? file_get_contents ( $cache_file ) : false;
		endif;


	#	Make API call:

		$rest_url = 'http://api.flickr.com/services/rest/?';


		if ( $api_sig ):

			ksort ( $rest_params );

			foreach ( $rest_params as $param => $value ):
				$api_sig .= $param . $value;
				$rest_url .= $param . '=' . $value . '&';
			endforeach;

			$rest_url .= 'api_sig=' . md5 ( $api_sig );

		else:

			foreach ( $rest_params as $param => $value ):
				$rest_url .= $param . '=' . $value . '&';
			endforeach;

			$rest_url = substr($rest_url, 0, -1);

		endif;


		$result = file_get_contents ( $rest_url );


	#	Write to cache:

		if ( $result && (strpos($result, '<rsp stat="ok">') || substr($result, -21) == 's:4:"stat";s:2:"ok";}') ):

			if ( $cache_file ):

				$file_pointer = fopen($cache_file, 'w');

				if ( $file_pointer ):
					fwrite($file_pointer, $result);
					fclose($file_pointer);
				else:
					trigger_error ( 'cache', E_USER_ERROR );
				endif;

			endif;

		else:

			trigger_error ( 'flickr', E_USER_ERROR );

		endif;


		return ( $return_result ) ? $result : true;


	}



function FlickrTreeWalk(&$flickr_object, $flickr_type, $level)

	{

		switch ( $flickr_type ):

			case 'collection':
				array_walk($flickr_object, 'FlickrTreeWalk', $level++);
				break;

			case 'set':
				print '<ul>' . "\n";
				array_walk($flickr_object, 'FlickrTreeWalk', 0);
				print '</ul>' . "\n";
				break;

			case 'title':
				print ( $level ) ? '<h' . $level . '>' . $flickr_object . '</h' . $level . '>' . "\n" : '<li><a href="' . CleanTitle($flickr_object) . '/">' . $flickr_object . '</a></li>' . "\n";
				break;

			default:

				if ( is_array($flickr_object) ):
					array_walk($flickr_object, 'FlickrTreeWalk', $level);
				endif;

				break;

		endswitch;

	}



function CleanTitle($title)

	{

		return ( preg_replace('/ +/', '-', trim(strtr(str_replace("'s", '', $title), "&\\/,()'", '       '))) );

	}



function FlickrErrorHandler($error_type, $error_string, $error_file, $error_line)

	{


		switch ( $error_string ):

			case '404':

				$error_title   = '404 Not Found';
				$error_header  = 'Not Found';
				$error_message = 'The requested URL was not found on this server.';
				break;

			case 'flickr':

				$error_title   = '500 Internal Server Error';
				$error_header  = 'Internal Server Error';
				$error_message = 'The server could not connect to Flickr.';
				break;

			case 'media':

				$error_title   = '500 Internal Server Error';
				$error_header  = 'Internal Server Error';
				$error_message = 'Flickr returned an unknown media type.';
				break;

			case 'cache':

				$error_title   = '500 Internal Server Error';
				$error_header  = 'Internal Server Error';
				$error_message = 'The server could not write to the data cache.';
				break;

			default:

				$error_title   = '500 Internal Server Error';
				$error_header  = 'Internal Server Error';
				$error_message = $error_string . '<br>' . $error_file . ', line ' . $error_line;

		endswitch;


		ob_clean();

		header ( 'HTTP/1.0 ' . $error_title );
		header ( 'Content-Type: text/html');

		print '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n";
		print '<html><head>' . "\n";
		print '<title>' . $error_title . '</title>' . "\n";
		print '</head><body>' . "\n";
		print '<h1>' . $error_header . '</h1>' . "\n";
		print '<p>' . $error_message . '</p>' . "\n";
		print '</body></html>' . "\n";

		exit;

		return false;

	}


?>
