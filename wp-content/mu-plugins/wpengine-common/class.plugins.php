<?php 
/** 
 * Class PluginsConfig Handles customization/configurations needed for third party plugins like woocommerce
 */
if( !class_exists("PluginsConfig") ) {
	class PluginsConfig {
		static $plugins = array( 'woocommerce/woocommerce.php' );
		var $skip = array();

		public function __construct() {}
		
		public static function sniff() {
			foreach( self::$plugins as $plugin ) {
				if( is_plugin_active($plugin) ) {	
					PluginsConfig::config($plugin);		
				}
			}
		}

		public static function config($plugin) {
			$inst = new PluginsConfig();
		
			// cleanup the plugin name
			$plugin = explode("/",$plugin);
			if( is_array( $plugin ) ) {	
				//99% of plugins will be formatted directory/file.php
				$plugin = $plugin[0];
			} else {
				//in the case where it's one file, strip the .php and use the slug
				$plugin = str_replace( ".php", '', $plugin );
			}

			if( method_exists( $inst, $plugin) ) {
				call_user_func_array(array($inst,$plugin), array());
			}
		}

		public function woocommerce() {
			//not using our class cuz it's throwing errors. 
			$uri = "https://api.wpengine.com/1.2/index.php";
			$uri = add_query_arg( array( 
				"method"=>"nginx-profile-add", 
				"profile"=>"woocommerce", 
				"location"=>"nginx-before-in-location",
				"account_name"=>PWP_NAME,
				"wpe_apikey"=>WPE_APIKEY 
				), 
			$uri);
			$resp = wp_remote_get($uri);
			$r = json_decode($resp['body'],1);
			if( @$r['error'] ) {
				error_log("WPE API [error]: ".$r['error_msg']);
			} elseif( @$r['success'] ) {
				error_log("WPE API [success]: Woocommerce ".$r['data']);
			}
		}

	}
}
