<?php

	include("include/init.php");

	loadlib("flickr_photos");
	loadlib("flickr_photos_metadata");
	loadlib("flickr_photos_permissions");
	loadlib("flickr_geo_permissions");

	loadlib("flickr_users");
	loadlib("flickr_urls");
	loadlib("flickr_places");

	$photo_id = get_int64("id");

	if (! $photo_id){
		error_404();
	}

	$photo = flickr_photos_get_by_id($photo_id);

	if (! $photo['id']){
		error_404();
	}

	# This is two things. One, a quick and dirty hack to ensure
	# that we display a notice if the path alias (on Flickr) has
	# been taken by a local user. See notes in flickr_users_get_by_url
	# and note that we are explicitly setting the "do not 404" flag
	# here. Two, make sure the photo is actually owned by the user
	# pointed to by the path alias or NSID. (20111203/straup)

	$flickr_user = flickr_users_get_by_url(0);

	if ($flickr_user['user_id'] != $photo['user_id']){
		error_404();
	}

	if ($photo['deleted']){
		error_410();
	}

	$perms_more = array(
		'allow_if_is_faved' => 1
	);

	if (! flickr_photos_permissions_can_view_photo($photo, $GLOBALS['cfg']['user']['id'], $perms_more)){
		error_403();
	}

	$perms_map = flickr_photos_permissions_map();
	$photo['str_perms'] = $perms_map[$photo['perms']];

	$GLOBALS['smarty']->assign_by_ref("photo", $photo);

	$owner = users_get_by_id($photo['user_id']);
	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$is_own = ($owner['id'] == $GLOBALS['cfg']['user']['id']) ? 1 : 0;
	$GLOBALS['smarty']->assign("is_own", $is_own);

	# context (next and previous)

	$context = get_str("context");

	if ($context == 'faves'){
		# please write me
	}

	else if ($context == 'place'){
		# please write me
	}

	else {

		$more = array(
			'viewer_id' => $GLOBALS['cfg']['user']['id'],
		);

		$bookends = flickr_photos_get_bookends($photo, $more);
	}

	$GLOBALS['smarty']->assign_by_ref("before", $bookends['before']);
	$GLOBALS['smarty']->assign_by_ref("after", $bookends['after']);

	# meta, geo, etc.

	# $meta = flickr_photos_metadata_load($photo);
	# $GLOBALS['smarty']->assign_by_ref("metadata", $meta['data']);

	$photo['can_view_geo'] = ($photo['hasgeo'] && flickr_geo_permissions_can_view_photo($photo, $GLOBALS['cfg']['user']['id'])) ? 1 : 0;

	if ($photo['can_view_geo']){

		$geo_perms_map = flickr_geo_permissions_map();
		$photo['str_geoperms'] = $geo_perms_map[$photo['geoperms']];

		# NOTE: this has the potential to slow things down if the
		# Flickr API is being wonky. On the other hand if you're
		# just running this for yourself (or maybe a handful of
		# friends) it shouldn't be a big deal. Also, caching.

		if ($place = flickr_places_get_by_woeid($photo['woeid'])){
			$GLOBALS['smarty']->assign_by_ref("place", $place);
		}
	}

	# check to see if the logged in user is the photo owner and has write perms
	# if we ever do suggestions, etc. then maybe we'll care if this isn't also
	# the photo owner (20120216/straup)

	if ($is_own){

		$_flickr_user = flickr_users_get_by_user_id($GLOBALS['cfg']['user']['id']);
		$has_write_token = flickr_users_has_token_perms($_flickr_user, 'write');

		$GLOBALS['smarty']->assign('has_write_token', $has_write_token);
	}

	$GLOBALS['smarty']->display("page_flickr_photo.txt");
	exit();
?>
