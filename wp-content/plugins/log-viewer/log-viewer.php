<?php
/*
 Plugin Name: Log Viewer
 Plugin URI: http://wordpress.org/extend/plugins/log-viewer/
 Description: This plugin provides an easy way to view log files directly in the admin panel.
 Author: mfisc
 Author URI: http://www.codein.at/
 Tag: 2013.05.19
 Version: 2013.05.19
 Timestamp: 2013.05.19.0054
 */

if (!function_exists('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}


if (!is_admin()) {
    return;
}

require 'helper.inc';
require 'class.plugin.php';

$ciLogViewer = new ciLogViewer();
