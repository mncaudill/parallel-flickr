<?php

	include("include/init.php");

	loadlib("flickr_users");
	loadlib("flickr_photos");
	loadlib("flickr_backups");
	loadlib("flickr_photos_utils");
	loadlib("flickr_urls");
	loadlib("flickr_dates");

	#

	$flickr_user = flickr_users_get_by_url();
	$owner = users_get_by_id($flickr_user['user_id']);

	$is_own = ($owner['id'] == $GLOBALS['cfg']['user']['id']) ? 1 : 0;
	$GLOBALS['smarty']->assign("is_own", $is_own);

	$is_registered = flickr_backups_is_registered_user($owner);
	$GLOBALS['smarty']->assign("is_registered", $is_registered);

	#

	$page = get_int32("page");
	$more = array(
		'page' => $page,
		'viewer_id' => $GLOBALS['cfg']['user']['id'],
	);

	if ($ago = get_str('ago')) {

		# Due to the htaccess rule, we know that this will 'some number' then 'some letters'
		preg_match('#^([0-9]+)(.+)$#', $ago, $matches);
		$timestamp = strtotime("-{$matches[1]} {$matches[2]}");

		$with = false;

		if ($timestamp && $timestamp > 0) {
			$with = flickr_photos_first_before_timestamp($owner, $timestamp);
		} 
		
		if (!$with) {
			error_404();
		}

	} else {
		$with = get_int64('with');
	}

	if ($with) {
		$more['with'] = $with;
	}

	$rsp = flickr_photos_for_user($owner, $more);
	$photos = $rsp['rows'];

	flickr_photos_utils_assign_can_view_geo($photos, $GLOBALS['cfg']['user']['id']);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("photos", $photos);

	$pagination_url = flickr_urls_photos_user($owner);
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);

	$GLOBALS['smarty']->display("page_flickr_photos_user.txt");
	exit();
?>
