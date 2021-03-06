<?php

	#################################################################

	function flickr_push_photos_record(&$user, $photo_data){

		$cluster = $user['cluster_id'];

		$photo_data['created'] = time();

		$insert = array();

		foreach ($photo_data as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_users($cluster, 'FlickrPushPhotos', $insert);

		if ($rsp['ok']){
			$rsp['photo'] = $photo_data;
		}

		return $rsp;
	}

	#################################################################

	function flickr_push_photos_for_subscription(&$sub, $older_than=null){

		$user = users_get_by_id($sub['user_id']);
		$cluster = $user['cluster_id'];

		$enc_sub = AddSlashes($sub['id']);

		# TO DO: indexes

		$sql = "SELECT * FROM FlickrPushPhotos WHERE subscription_id='{$enc_sub}'";

		if ($older_than){
			$enc_older = AddSlashes($older_than);
			$sql .= " AND created > '{$enc_older}'";
		}

		$sql .= " ORDER BY created DESC";

		$rsp = db_fetch_users($cluster, $sql);

		$photos = array();

		foreach ($rsp['rows'] as $row){
			$photo = json_decode($row['photo_data'], 'as hash');
			$photo['created'] = $row['created'];

			$photo['display_url'] = str_replace("_s.jpg", ".jpg", $photo['thumb_url']);
			$photos[] = $photo;
		}

		$rsp['rows'] = $photos;
		return $rsp;
	}

	#################################################################

	function flickr_push_photos_purge(){

		$now = time();
		$then = $now - (60 * 60 * 24);

		$enc_then = AddSlashes($then);
		$sql = "DELETE FROM FlickrPushPhotos WHERE created < {$enc_then}";

		foreach ($GLOBALS['cfg']['db_users']['host'] as $cluster_id => $ignore){

			db_write_users($cluster_id, $sql);
		}
	}

	#################################################################
?>
