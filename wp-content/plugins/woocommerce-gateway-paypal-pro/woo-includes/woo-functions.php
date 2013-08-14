<?php
/**
 * WC Detection
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {

		if ( defined( 'WOOCOMMERCE_ACTIVE' ) )
			return WOOCOMMERCE_ACTIVE;

		$plugin = 'woocommerce/woocommerce.php';

		if ( is_admin() ) {
			if ( ! function_exists( 'is_plugin_active' ) )
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			define( 'WOOCOMMERCE_ACTIVE', is_plugin_active( $plugin ) );
		} else {
			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() )
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

			if ( in_array( $plugin, $active_plugins ) || isset( $active_plugins[ $plugin ] ) )
				define( 'WOOCOMMERCE_ACTIVE', true );
			else
				define( 'WOOCOMMERCE_ACTIVE', false );
		}

		return WOOCOMMERCE_ACTIVE;
	}
}

/**
 * Queue updates for the WooUpdater
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	function woothemes_queue_update( $file, $file_id, $product_id ) {
		global $woothemes_queued_updates;

		if ( ! isset( $woothemes_queued_updates ) )
			$woothemes_queued_updates = array();

		$plugin             = new stdClass();
		$plugin->file       = $file;
		$plugin->file_id    = $file_id;
		$plugin->product_id = $product_id;

		$woothemes_queued_updates[] = $plugin;
	}
}

/**
 * Load installer for the WooThemes Updater.
 * @return $api Object
 */
if ( ! class_exists( 'WooThemes_Updater' ) && ! function_exists( 'woothemes_updater_install' ) ) {
	function woothemes_updater_install( $api, $action, $args ) {
		$download_url = 'http://woodojo.s3.amazonaws.com/downloads/woothemes-updater/woothemes-updater.zip';

		if ( 'plugin_information' != $action ||
			false !== $api ||
			! isset( $args->slug ) ||
			'woothemes-updater' != $args->slug
		) return $api;

		$api = new stdClass();
		$api->name = 'WooThemes Updater';
		$api->version = '1.0.0';
		$api->download_link = esc_url( $download_url );
		return $api;
	}

	add_filter( 'plugins_api', 'woothemes_updater_install', 10, 3 );
}

/**
 * WooUpdater Installation Prompts
 */
if ( ! class_exists( 'WooThemes_Updater' ) && ! function_exists( 'woothemes_updater_notice' ) ) {

	/**
	 * Display a notice if the "WooThemes Updater" plugin hasn't been installed.
	 * @return void
	 */
	function woothemes_updater_notice() {

		if ( ! function_exists( 'is_plugin_active' ) )
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( 'woothemes-updater/woothemes-updater.php' ) )
			return;

		$slug = 'woothemes-updater';
		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ), 'install-plugin_' . $slug );
		$activate_url = 'plugins.php?action=activate&plugin=' . urlencode( 'woothemes-updater/woothemes-updater.php' ) . '&plugin_status=all&paged=1&s&_wpnonce=' . urlencode( wp_create_nonce( 'activate-plugin_woothemes-updater/woothemes-updater.php' ) );

		$message = '<a href="' . esc_url( $install_url ) . '">Install the WooThemes Updater plugin</a> to get updates for your WooThemes plugins.';
		$is_downloaded = false;
		$plugins = array_keys( get_plugins() );
		foreach ( $plugins as $plugin ) {
			if ( strpos( $plugin, 'woothemes-updater.php' ) !== false ) {
				$is_downloaded = true;
				$message = '<a href="' . esc_url( admin_url( $activate_url ) ) . '">Activate the WooThemes Updater plugin</a> to get updates for your WooThemes plugins.';
			}
		}
		echo '<div class="updated fade"><p>' . $message . '</p></div>' . "\n";
	}

	add_action( 'admin_notices', 'woothemes_updater_notice' );
}

/**
 * Prevent conflicts with older versions
 */
if ( ! class_exists( 'WooThemes_Plugin_Updater' ) ) {
	class WooThemes_Plugin_Updater { function init() {} }
}