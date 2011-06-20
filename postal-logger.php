<?php
/*
Plugin Name: Postal Logger	
Plugin URI: http://yourdomain.com/
Description: Used to log postal codes of users coming to your blog.
Version: 0.1.1
Author: Don Kukral
Author URI: http://yourdomain.com
License: GPL
*/


function postal_logger_menu() {
    if ($_GET['view'] == 'city') {
        $log = get_option('postal_logger_city', serialize(array()));
    } else {
        $log = get_option('postal_logger', serialize(array()));
    }
    $log = unserialize($log);
	asort($log);
?>
	<div class="wrap">
	<h2>Postal Logger</h2>
	<ul id="postal_logger_menu">
<?php
    if ($_GET['view'] == 'city') {
        echo '<li><a href="?page=Postal-Logger">Postal Code</a></li>';
        echo '<li class="current">City</li>';
    } else {
        echo '<li class="current">Postal Code</li>';
        echo '<li><a href="?page=Postal-Logger&view=city">City</a></li>';
    }
?>
	</ul>
	<p>
	<table class="widefat">
	<thead>
	<tr>
	<th scope="col">
<?php
    if ($_GET['view'] == 'city') {
        echo 'City';
    } else {
        echo 'Postal Code';
    }
?>	   
	</th>
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
	
	<style type="text/css">
    #postal_logger_menu {
    	line-height: 1.4em;
    	margin: 5px 0 0 0;
    	padding: 0;
    	border-bottom: 1px solid #aaaaaa;
    }
    #postal_logger_menu li { 
    	border: 1px solid #aaaaaa;
    	border-bottom: none;
    	line-height: 1.4em;
    	display: inline-block;
    	margin: 0 5px 0 0;
    	padding: 0;
    	list-style-type: none;
    	list-style-image: none;
    	list-style-position: outside;
    }
    #postal_logger_menu li.current span { 
    	background-color: #ffffff;
    	font-weight: bold;
    	padding: 0 5px 3px 5px;
    }
    #postal_logger_menu li a,
    #postal_logger_menu li a:visited {
    	padding: 0 5px;
    	text-decoration: none;
    }
    #postal_logger_menu li a:hover {
    	background-color: #eaf2fa;
    }
    #postal_logger_menu + .wrap {
    	margin-top: 0;
    }
    </style>
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
	
	$log_city = unserialize(get_option('postal_logger_city', serialize(array())));

	if (!$_COOKIE['city']) {
		if ($location) {
			$city = $location['city'];
			if ($city == '') { $city = 'unknown'; }
		} else {
			$city = 'unknown';
		}
		$city = "".$city;
		setcookie("city", $city, time()+3600, "/", 
			str_replace('http://','',get_bloginfo('url')));
		if ($city != 'unknown') {
			if (isset($log_city[$city])) {
				$log_city[$city] = intval($log_city[$city]) + 1;
			} else {
				$log_city[$city] = 1;
			}
		}

		update_option('postal_logger_city', serialize($log_city));
	}

}
?>