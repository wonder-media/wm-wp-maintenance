<?php
/*
Plugin Name: WP-Core | WM Maintenance
Description: Maintain and support custom features on this website, including logging of updates.
Version: 1.3.2
*/

// Include the plugin-update-checker library.
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://wm-streaming.s3.amazonaws.com/wm-apps/wp-plugins/wp-code-wm-maintenance/update.json',
	__FILE__, //Full path to the main plugin file or functions.php.
	'wp-code-wm-maintenance'
);

// Function to log updates (plugins, themes, and core).
function pul_log_updates( $upgrader_object, $options ) {
    $domain_name = get_site_url();
    $plugin_version = "1.3.1";
    $php_version = phpversion();
    $current_user = wp_get_current_user();
    $username = $current_user->user_login;
    $endpoint_url = 'https://script.google.com/macros/s/AKfycbwXtkQ98nCAU7vAnYxDQTAVgk57a8Ca4IbDImpmOf6KzjutKxCERWEgyvvDKch-hl9_/exec';
    
    // Check for plugin updates
    if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
        $plugins = get_plugins();
        $plugin = $plugins[ $options['plugins'][0] ];
        
        $new_version = $plugin['Version'];
        $prev_version = $upgrader_object->skin->plugin_info['Version'];
                
        $endpoint_url = add_query_arg( array(
            'domain' => urlencode( $domain_name ),
            'appName' => urlencode( $plugin['Name'] ),
            'previousVersion' => urlencode( $prev_version ),
            'newVersion' => urlencode( $new_version ),
            'event' => urlencode( 'Plugin Updated' ),
            'updatedBy' => urlencode( $username ),
            'phpVersion' => urlencode( $php_version ),
            'pluginVersion' => urlencode( $plugin_version )
        ), $endpoint_url );

        wp_remote_get( $endpoint_url );
    }

    // Check for theme updates
    if ( 'update' === $options['action'] && 'theme' === $options['type'] ) {
    $theme = new WP_Theme($options['themes'][0], get_theme_root($options['themes'][0]));

    if ($theme instanceof WP_Theme) {
        $prev_version = $theme->get('Version');
        $new_version = $upgrader_object->skin->theme_info['Version'];

        $endpoint_url = add_query_arg( array(
            'domain' => urlencode( $domain_name ),
            'appName' => urlencode( $theme['Name'] ),
            'previousVersion' => urlencode( $prev_version ),
            'newVersion' => urlencode( $new_version ),
            'event' => urlencode( 'Theme Updated' ),
            'updatedBy' => urlencode( $username ),
            'phpVersion' => urlencode( $php_version ),
            'pluginVersion' => urlencode( $plugin_version )
        ), $endpoint_url );

        wp_remote_get( $endpoint_url );
        }
    }
    
    // Check for WordPress core updates
    if ( 'update' === $options['action'] && 'core' === $options['type'] ) {
        // Get the old version
        $previous_version = get_bloginfo('version');

        // Attempt to get the new version from version.php
        include(ABSPATH . WPINC . '/version.php');
        $new_version = isset($wp_version) ? $wp_version : 'Unknown';

        $endpoint_url = add_query_arg( array(
            'domain' => urlencode( $domain_name ),
            'coreUpdate' => urlencode( 'WordPress' ),
            'previousVersion' => urlencode( $previous_version ),
            'newVersion' => urlencode( $new_version ),
            'event' => urlencode( 'Core Updated' ),
            'updatedBy' => urlencode( $username ),
            'phpVersion' => urlencode( $php_version ),
            'pluginVersion' => urlencode( $plugin_version )
        ), $endpoint_url );

        wp_remote_get( $endpoint_url );
    }
}
add_action( 'upgrader_process_complete', 'pul_log_updates', 10, 2 );