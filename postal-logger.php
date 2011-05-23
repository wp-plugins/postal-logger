<?php
/*
Plugin Name: Postal Logger	
Plugin URI: http://yourdomain.com/
Description: Used to log postal codes of users coming to your blog.
Version: 0.0
Author: Don Kukral
Author URI: http://yourdomain.com
License: GPL
*/


function postal_logger_menu() {
	$log = unserialize(get_option('postal_logger', serialize(array())));
	asort($log);
?>
	<div class="wrap">
	<h2>Postal Logger</h2>
	<p>
	<table class="widefat">
	<thead>
	<tr>
	<th scope="col">Postal Code</th>
	<th scope="col">Count</th>
	</tr>
	</thead>
	<tbody>
<?php
	foreach (array_reverse($log, true) as $k=>$v) {
		print "<tr><td>" . $k . "</td><td>" . $v . "</td></tr>\n";
	}
?>
	</tbody>
	</table>
	</p>
	</div>
<?php
}

function postal_logger_admin_menu() {
    add_management_page('Postal-Logger', 'Postal Logger', 1, 'Postal-Logger', 'postal_logger_menu');
}

add_action('admin_menu', 'postal_logger_admin_menu');

add_action('init', 'capture_postal_code');

function capture_postal_code() {
	if (!session_id())
		session_start();
	$location = geoip_record_by_name($_SERVER['REMOTE_ADDR']);

	$log = unserialize(get_option('postal_logger', serialize(array())));
	if (!$_COOKIE['postal_code']) {
		if ($location) {
			$postal_code = $location['postal_code'];
			if ($postal_code == '') { $postal_code = 'unknown'; }
		} else {
			$postal_code = 'unknown';
		}
		$postal_code = "".$postal_code;
		setcookie("postal_code", $postal_code, time()+3600, "/", 
			str_replace('http://','',get_bloginfo('url')));
		if ($postal_code != 'unknown') {
			if (isset($log[$postal_code])) {
				$log[$postal_code] = intval($log[$postal_code]) + 1;
			} else {
				$log[$postal_code] = 1;
			}
		}

		update_option('postal_logger', serialize($log));
	}

}
?>