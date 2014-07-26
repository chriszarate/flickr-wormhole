<?php

/*
	Flickr Wormhole
	https://github.com/chriszarate/flickr-wormhole
*/


#	Include Flickr functions:
	require_once ('flickr_include.php');


#	Initialize REST parameters array:
	$rest_params = Array();


#	Gather request variables and set parameters:

	$photo_set   = ( empty($_REQUEST['photo_set']) )   ? '' : $_REQUEST['photo_set'];
	$photo_index = ( empty($_REQUEST['photo_index']) ) ? 1  : intval($_REQUEST['photo_index']);

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

		$photo_link  = 'http://www.flickr.com/photos/' . $photoset_data['photoset']['ownername'] . '/' . $photo_data['id'] . '/';
		$photo_title = ( trim(strtr(str_replace('IMG', '', $photo_data['title']), 'IMG_0123456789', '              ')) ) ? $photo_data['title'] : '';
		$photo_media = $photo_data['media'];

		if ( $photo_media == 'photo' ):

			$photo_src      = $photo_data['url_l'];
			$photo_width    = $photo_data['width_l'];
			$photo_height   = $photo_data['height_l'];
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

			/* Lato Web font */

			@font-face {
				font-family: 'Lato';
				font-style: normal;
				font-weight: 400;
				src: local('Lato Regular'), local('Lato-Regular'), url(http://themes.googleusercontent.com/static/fonts/lato/v6/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format('woff');
			}

			@font-face {
				font-family: 'Lato';
				font-style: normal;
				font-weight: 700;
				src: local('Lato Bold'), local('Lato-Bold'), url(http://themes.googleusercontent.com/static/fonts/lato/v6/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format('woff');
			}


			/* Default viewport */

			body {

				max-width: 1024px;

				margin: 0 auto;
				margin-bottom: 1em;
				padding: 0;

				color: #333;
				background: #fff;

				font-family: Lato, Helvetica, sans-serif;
				font-size: 1em;

				line-height: 1.5em;

			}

			p {
				margin: 0 auto;
				padding: 0;
				text-align: center;
			}


			/* Links */

			a {
				color: #00f;
				text-decoration: none;
			}

			a:hover {
				text-decoration: underline;
			}

			.key {
				text-decoration: underline;
			}


			/* Photos */

			#photos img {
				max-width: 100%;
				max-height: 100%;
				width: auto;
				height: auto;
			}


			/* Meta */

			#meta {
				color: #99f;
			}

			#meta a {
				margin: 0 0.25em;
			}


			@media only screen and (max-width: 1024px) {

			/* Clean */

			.arrow {
				display: none;
			}

			.key {
				text-decoration: none;
			}

		</style>

		<title><?= $page_title ?> / <?= $photoset_title ?><?= ( $photo_title ) ? ' / ' . $photo_title : '' ?> / #<?= $photo_index ?> of <?= $photo_count ?></title>

	</head>

	<body>

		<div id="photos">

			<p>

				<? if ( $photo_media == 'video' ): ?>

					<object type="application/x-shockwave-flash" width="<?= $photo_width ?>" height="<?= $photo_height ?>" data="<?= $photo_src ?>" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">
						<param name="flashvars" value="flickr_show_info_box=false"></param>
						<param name="movie" value="<?= $photo_src ?>"></param>
						<param name="bgcolor" value="#ffffff"></param>
						<param name="allowFullScreen" value="true"></param>
						<embed type="application/x-shockwave-flash" src="<?= $photo_src ?>" bgcolor="#ffffff" allowfullscreen="true" flashvars="flickr_show_info_box=false" width="<?= $photo_width ?>" height="<?= $photo_height ?>"></embed>
					</object>

				<? elseif ( $photo_index < $photo_count ): ?>

					<a href="<?= $photo_index + 1 ?>.html" onmouseover="document.getElementById('next').className = 'active';" onmouseout="document.getElementById('next').className = '';">
						<img src="<?= $photo_src ?>" width="<?= $photo_width ?>" height="<?= $photo_height ?>" border="0">
					</a>

				<? else: ?>

					<img src="<?= $photo_src ?>" width="<?= $photo_width ?>" height="<?= $photo_height ?>" border="0">

				<? endif; ?>

			</p>

			<p id="meta">

				<a href="<?= $home_link ?>">Home</a>

				<? if ( $photo_download ): ?>
					<a href="<?= $photo_download ?>">Full-size</a>
				<? endif; ?>

				<a href="<?= $photo_link ?>">@Flickr</a>

				•

				<? if ( $photo_index > 1 ): ?>
					<a id="prev" href="<?= $photo_index - 1 ?>.html"><span class="arrow">←</span> <span class="key">P</span>rev</a>
				<? endif; ?>

				<? if ( $photo_index < $photo_count ): ?>
					<a id="next" href="<?= $photo_index + 1 ?>.html"><span class="key">N</span>ext <span class="arrow">→</span></a>
				<? endif; ?>

			</p>

		</div>

		<script>

			var nextLink = document.getElementById('next');
			var prevLink = document.getElementById('prev');

			document.onkeydown = function (e) {

				var keyCode  = e.keyCode || e.which;
				var prevKey = [37, 75, 80].indexOf(keyCode) !== -1;      // Left, K, P
				var nextKey = [32, 39, 74, 78].indexOf(keyCode) !== -1;  // Space bar, Right, J, N

				if (e.ctrlKey || e.altKey || e.metaKey) {
					return;
				}

				if (prevKey && prevLink) {
					window.location = prevLink.href;
				}
				if (nextKey && nextLink) {
					window.location = nextLink.href;
				}

			};

		</script>

	</body>

</html>
