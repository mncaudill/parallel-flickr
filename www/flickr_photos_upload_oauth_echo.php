<?php

	require('include/init.php');
	loadlib('http');
	loadlib("flickr_users");
	loadlib("flickr_backups");
	loadlib('flickr_photos_upload');

	if (! $GLOBALS['cfg']['enable_feature_uploads']){
		error_disabled();
	}

	$headers = array('Authorization' => $_SERVER['HTTP_X_VERIFY_CREDENTIALS_AUTHORIZATION']);
	$res = http_get($_SERVER['HTTP_X_AUTH_SERVICE_PROVIDER'], $headers);

	if (! $res['ok']) {
		exit;
	}

	$body = json_decode($res['body'], true);
	$twitter_id = $body['id'];

	// TODO: look up id here
	//nolancaudill3:853340942 maps to flickruser id  399
	$user = users_get_by_id(399);

	$is_registered = flickr_backups_is_registered_user($user);
	$can_upload = $is_registered;

	if ($can_upload){
		$flickr_user = flickr_users_get_by_user_id($user['id']);
		$can_upload = flickr_users_has_token_perms($flickr_user, "write");
	}

	if (! $can_upload) {
		exit;
	}

	$filepath = $_FILES['media']['tmp_name'];

	if (! $filepath) {
		exit;
	}

	$res = flickr_photos_upload($user, $filepath);

	if ($res['ok']) {
		print "<mediaurl>http://www.flickr.com/photos/{$flickr_user['nsid']}/{$res['photo_id']}/</mediaurl>";
	}

