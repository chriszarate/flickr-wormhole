<?php

/*
	Flickr Wormhole
	https://github.com/chriszarate/flickr-wormhole
*/


#	Include Flickr functions:
	require_once ('flickr_include.php');


#	Initialize REST parameters array:
	$rest_params  = Array();


#	Gather request variables and set parameters:

	$photo_set   = ( empty($_REQUEST['photo_set']) )   ? '' : $_REQUEST['photo_set'];
	$photo_index = ( empty($_REQUEST['photo_index']) ) ? 1  : intval($_REQUEST['photo_index']);
	$photo_size  = ( empty($_REQUEST['size']) )        ? '' : $_REQUEST['size'];

	$photo_size_query = ( $photo_size == 'S' || $photo_size == 'L' ) ? '?size=' . $photo_size : '';

	$flickr_cache_file = 'flickr_sets.txt';

	$rest_params['auth_token'] = $flickr_auth_token;
	$rest_params['api_key']    = $flickr_api_key;
	$rest_params['method']     = 'flickr.photosets.getList';
	$rest_params['format']     = 'php_serial';


#	Get serialized list of photosets:

	$flickr_data = unserialize(FlickrCall($rest_params, $flickr_api_sig, $cache_dir . $flickr_cache_file, true));

	foreach ( $flickr_data['photosets']['photoset'] as $flickr_photoset ):

		if ( isset($flickr_photoset['title']['_content']) && CleanTitle($flickr_photoset['title']['_content']) == $photo_set ):
			$photoset_id    = $flickr_photoset['id'];
			$photoset_title = $flickr_photoset['title']['_content'];
			break;
		endif;

	endforeach;


#	If we found the photoset, process request:

	if ( isset($photoset_id) && $photoset_id ):

		$rest_params['method']      = 'flickr.photosets.getPhotos';
		$rest_params['extras']      = 'original_format,media,o_dims,url_t,url_m,url_l,url_o';
		$rest_params['photoset_id'] = $photoset_id;

		$cache_file = $photo_set . '.txt';

	else:

		trigger_error ( '404', E_USER_ERROR );

	endif;


#	Get photoset metadata:
	$photoset_data = unserialize(FlickrCall($rest_params, $flickr_api_sig, $cache_dir . $cache_file, true));


#	Check to see if requested index exists:

	if ( isset($photoset_data['photoset']['photo'][--$photo_index]) ):

		$photo_count = count($photoset_data['photoset']['photo']);
		$photo_data  = $photoset_data['photoset']['photo'][$photo_index];
		$photo_index++;

		$photo_title = ( trim(strtr(str_replace('IMG', '', $photo_data['title']), 'IMG_0123456789', '              ')) ) ? $photo_data['title'] : '';
		$photo_media = $photo_data['media'];

		if ( $photo_media == 'photo' ):

			switch ( $photo_size ):
				case 'S':
					$photo_src    = $photo_data['url_m'];
					$photo_height = $photo_data['height_m'];
					$photo_width  = $photo_data['width_m'];
					break;
				case 'L':
					$photo_src    = $photo_data['url_l'];
					$photo_width  = $photo_data['width_l'];
					$photo_height = $photo_data['height_l'];
					break;
				case 'M':
				default:
					$photo_src    = $photo_data['url_l'];
					$photo_width  = round(intval($photo_data['width_l'])  * 0.75);
					$photo_height = round(intval($photo_data['height_l']) * 0.75);
			endswitch;

			$photo_download = $photo_data['url_o'];

		elseif ( $photo_media == 'video' ):

			$photo_src      = 'http://www.flickr.com/apps/video/stewart.swf?v=71377&photo_secret=' . $photo_data['secret'] . '&photo_id=' . $photo_data['id'];
			$photo_download = false;

			$photo_width  = 400;
			$photo_height = 320;

			$rest_params['method']   = 'flickr.photos.getSizes';
			$rest_params['photo_id'] = $photo_data['id'];

			unset($rest_params['extras'], $rest_params['photoset_id']);
			$cache_file = 'video_' . $photoset_id . '_' . $photo_data['id'] . '.txt';

			$video_data = unserialize(FlickrCall($rest_params, $flickr_api_sig, $cache_dir . $cache_file, true));

			foreach ( $video_data['sizes']['size'] as $size ):
				if ( $size['label'] == 'Video Player' ):
					$photo_width  = $size['width'];
					$photo_height = $size['height'];
				endif;
			endforeach;

		else:

			trigger_error ( 'media', E_USER_ERROR );

		endif;

	else:

		trigger_error ( '404', E_USER_ERROR );

	endif;


#	Process conditional GET:
	ConditionalGet($cache_file);


?>
<!DOCTYPE html>
<html>

	<head>

		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex,nofollow,noarchive">

		<style type="text/css">

			body {
				margin: 3em 0;
				color: #ccc;
				background: #000;
				font-family: Helvetica, Arial, sans-serif;
			}

			a { color: #999; text-decoration: none; }
			a:hover { color: #000; }
			a.active { color: #000; }

			div#frame {
				width: <?= $photo_width ?>px;
				margin: 0 auto;
				padding: 1em;
				background: #fff;
			}

			div#photo { padding-bottom: 0.75em; line-height: 1.2em; }
			div#thread { float: right; line-height: 1.2em; }
			div#size { color: #000; }

			div#thread span.key { display: inline-block; text-decoration: underline; }

		</style>

		<title><?= $page_title ?> / <?= $photoset_title ?><?= ( $photo_title ) ? ' / ' . $photo_title : '' ?> / #<?= $photo_index ?> of <?= $photo_count ?></title>

	</head>

	<body onload="var controlKeyOff=true;document.onkeydown=function(e){myKey=(e)?e.keyCode:window.event.keyCode;if(myKey==17||myKey==18||myKey==91||myKey==224){controlKeyOff=false;}else if(controlKeyOff&&(myKey==74||myKey==80)){window.location=document.getElementById('prev').href;}else if(controlKeyOff&&(myKey==75||myKey==78)){window.location=document.getElementById('next').href;}};document.onkeyup=function(e){myKey=(e)?e.keyCode:window.event.keyCode;controlKeyOff=(myKey==17||myKey==18||myKey==91||myKey==224)?true:false;};">

		<div id="frame">

			<div id="photo">

				<? if ( $photo_media == 'video' ): ?>

					<object type="application/x-shockwave-flash" width="<?= $photo_width ?>" height="<?= $photo_height ?>" data="<?= $photo_src ?>" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">
						<param name="flashvars" value="flickr_show_info_box=false"></param>
						<param name="movie" value="<?= $photo_src ?>"></param>
						<param name="bgcolor" value="#ffffff"></param>
						<param name="allowFullScreen" value="true"></param>
						<embed type="application/x-shockwave-flash" src="<?= $photo_src ?>" bgcolor="#ffffff" allowfullscreen="true" flashvars="flickr_show_info_box=false" width="<?= $photo_width ?>" height="<?= $photo_height ?>"></embed>
					</object>

				<? elseif ( $photo_index < $photo_count ): ?>

					<a href="<?= $photo_index + 1 ?>.html<?= $photo_size_query ?>" onmouseover="document.getElementById('next').className = 'active';" onmouseout="document.getElementById('next').className = '';">
						<img src="<?= $photo_src ?>" width="<?= $photo_width ?>" height="<?= $photo_height ?>" border="0">
					</a>

				<? else: ?>

					<img src="<?= $photo_src ?>" width="<?= $photo_width ?>" height="<?= $photo_height ?>" border="0">

				<? endif; ?>

			</div>

			<div id="thread">

				<? if ( $photo_index > 1 ): ?>
					<a id="prev" href="<?= $photo_index - 1 ?>.html<?= $photo_size_query ?>">&#x25c4; <span class="key">P</span>rev</a>
				<? endif; ?>

				<? if ( $photo_index < $photo_count ): ?>
					&#160;
					<a id="next" href="<?= $photo_index + 1 ?>.html<?= $photo_size_query ?>"><span class="key">N</span>ext &#x25ba;</a>
				<? endif; ?>

			</div>

			<div id="size">

				<?= ( $photo_size_query == '?size=S' ) ? '&#x2585;' : '<a href="?size=S">&#x2585;</a>' ?>
				<?= ( $photo_size_query == '' )        ? '&#x2586;' : '<a href="' . $photo_index . '.html">&#x2586;</a>' ?>
				<?= ( $photo_size_query == '?size=L' ) ? '&#x2587;' : '<a href="?size=L">&#x2587;</a>' ?> &#160;

				<? if ( $photo_download ): ?>
					<a href="<?= $photo_download ?>">Print</a>&#160;
				<? endif; ?>

				<a href="<?= $home_link ?>">Home</a>

			</div>

		</div>


	</body>

</html>
