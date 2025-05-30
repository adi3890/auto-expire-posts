<?php
/**
 * Uninstall Auto-Expire Posts
 *
 * Removes all plugin data when uninstalled
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete all post meta related to this plugin
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_post_expiry_date'" );
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_post_expiry_action'" );

// Clear any scheduled events
$scheduled_posts = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
        '%auto_expire_post_event%'
    )
);

foreach ( $scheduled_posts as $option_name ) {
    delete_option( $option_name );
}