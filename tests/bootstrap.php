<?php
/**
 * PHPUnit bootstrap file for Auto-Expire Posts plugin.
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find $_tests_dir/includes/functions.php, have you installed WordPress test suite?" . PHP_EOL;
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _load_auto_expire_posts_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/auto-expire-posts.php';
}
tests_add_filter( 'muplugins_loaded', '_load_auto_expire_posts_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
