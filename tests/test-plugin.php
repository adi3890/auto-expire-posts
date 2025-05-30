<?php
class Plugin_Load_Test extends WP_UnitTestCase {
    public function test_plugin_loaded() {
        $this->assertTrue( class_exists( 'Auto_Expire_Posts' ) );
    }
}
