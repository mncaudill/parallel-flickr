<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	#

	include("include/init.php");
	loadlib("flickr_backups");

	if (! $GLOBALS['cfg']['enable_feature_backups']){
		echo "backups are currently disabled\n";
		exit();
	}

	foreach (flickr_backups_users() as $user){
		echo "backup geobookmarks for {$user['username']}\n";
		$rsp = flickr_backups_get_geobookmarks($user);
		dumper($rsp);
	}

	exit();
?>
