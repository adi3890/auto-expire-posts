<?php
/**
 * Plugin Name: Auto-Expire Posts
 * Description: Automatically unpublish posts after a specified expiration date.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: auto-expire-posts
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Auto_Expire_Posts class
 */
class Auto_Expire_Posts {

    /**
     * Constructor
     */
    public function __construct() {
        // Add meta box to post editor
        add_action( 'add_meta_boxes', array( $this, 'add_expiry_meta_box' ) );
        
        // Save meta box data
        add_action( 'save_post', array( $this, 'save_expiry_meta_data' ) );
        
        // Schedule expiration on post save
        add_action( 'save_post', array( $this, 'schedule_post_expiration' ) );
        
        // Hook for expiring posts
        add_action( 'auto_expire_post_event', array( $this, 'expire_post' ) );
    }

    /**
     * Add meta box to post editor
     */
    public function add_expiry_meta_box() {
        add_meta_box(
            'auto_expire_posts_meta_box',
            __( 'Post Expiration', 'auto-expire-posts' ),
            array( $this, 'render_expiry_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render meta box content
     * 
     * @param WP_Post $post Current post object
     */
    public function render_expiry_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'auto_expire_posts_meta_box', 'auto_expire_posts_meta_box_nonce' );
        
        // Get saved value
        $expiry_date = get_post_meta( $post->ID, '_post_expiry_date', true );
        $expiry_action = get_post_meta( $post->ID, '_post_expiry_action', true );
        
        // Default to 'draft' if not set
        if ( empty( $expiry_action ) ) {
            $expiry_action = 'draft';
        }
        
        ?>
        <p>
            <label for="post_expiry_date"><?php _e( 'Expiry Date & Time:', 'auto-expire-posts' ); ?></label>
            <input type="datetime-local" id="post_expiry_date" name="post_expiry_date" value="<?php echo esc_attr( $expiry_date ); ?>" style="width: 100%;">
        </p>
        <p>
            <label for="post_expiry_action"><?php _e( 'Action on expiry:', 'auto-expire-posts' ); ?></label>
            <select id="post_expiry_action" name="post_expiry_action" style="width: 100%;">
                <option value="draft" <?php selected( $expiry_action, 'draft' ); ?>><?php _e( 'Set to Draft', 'auto-expire-posts' ); ?></option>
                <option value="delete" <?php selected( $expiry_action, 'delete' ); ?>><?php _e( 'Delete Post', 'auto-expire-posts' ); ?></option>
            </select>
        </p>
        <p class="description">
            <?php _e( 'Leave empty for no expiration.', 'auto-expire-posts' ); ?>
        </p>
        <?php
    }

    /**
     * Save meta box data
     * 
     * @param int $post_id Post ID
     */
    public function save_expiry_meta_data( $post_id ) {
        // Check if nonce is set
        if ( ! isset( $_POST['auto_expire_posts_meta_box_nonce'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['auto_expire_posts_meta_box_nonce'], 'auto_expire_posts_meta_box' ) ) {
            return;
        }

        // If this is an autosave, don't do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save expiry date
        if ( isset( $_POST['post_expiry_date'] ) ) {
            update_post_meta( $post_id, '_post_expiry_date', sanitize_text_field( $_POST['post_expiry_date'] ) );
        }

        // Save expiry action
        if ( isset( $_POST['post_expiry_action'] ) ) {
            update_post_meta( $post_id, '_post_expiry_action', sanitize_text_field( $_POST['post_expiry_action'] ) );
        }
    }

    /**
     * Schedule post expiration
     * 
     * @param int $post_id Post ID
     */
    public function schedule_post_expiration( $post_id ) {
        // If this is an autosave, don't do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Clear any existing scheduled events for this post
        $this->clear_scheduled_expiration( $post_id );

        // Get expiry date
        $expiry_date = get_post_meta( $post_id, '_post_expiry_date', true );
        
        // If expiry date is set and valid
        if ( ! empty( $expiry_date ) ) {
            // Convert to timestamp
            $expiry_timestamp = strtotime( $expiry_date );
            
            // Schedule the event if timestamp is valid and in the future
            if ( $expiry_timestamp && $expiry_timestamp > time() ) {
                wp_schedule_single_event( $expiry_timestamp, 'auto_expire_post_event', array( $post_id ) );
            }
        }
    }

    /**
     * Clear scheduled expiration for a post
     * 
     * @param int $post_id Post ID
     */
    private function clear_scheduled_expiration( $post_id ) {
        wp_clear_scheduled_hook( 'auto_expire_post_event', array( $post_id ) );
    }

    /**
     * Expire a post
     * 
     * @param int $post_id Post ID
     */
    public function expire_post( $post_id ) {
        // Get expiry action
        $expiry_action = get_post_meta( $post_id, '_post_expiry_action', true );
        
        // Default to 'draft' if not set
        if ( empty( $expiry_action ) ) {
            $expiry_action = 'draft';
        }
        
        if ( $expiry_action === 'delete' ) {
            // Delete the post
            wp_delete_post( $post_id, true );
        } else {
            // Set post to draft
            wp_update_post( array(
                'ID' => $post_id,
                'post_status' => 'draft'
            ) );
        }
    }
}

// Initialize the plugin
new Auto_Expire_Posts();