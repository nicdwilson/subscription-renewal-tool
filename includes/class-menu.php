<?php
/**
 * The Menu class
 * 
 * This handles a very simple presentation of a page with a form to get a subscription by email
 * and the subsequent display of the subscription data.
 * 
 */

namespace SubscriptionRenewalTool;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Menu{

    /**
	 * Menu The instance of Menu
	 *
	 * @var    object
	 * @access private
	 * @since  1.0.0
	 */
	private static object $instance;

    /**
	 * Main Menu Instance
	 *
	 * Ensures only one instance of Menu is loaded or can be loaded.
	 *
	 * @return Menu instance
	 * @since  1.0.0
	 * @static
	 */
	public static function instance(): object {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

    public function __construct(){
        $this->add_menu();
    }

    /**
     * Add the menu page
     */
    public function add_menu(){

        add_menu_page( 'Subscription Renewal Tool', 'Subscription Renewal Tool', 'manage_options', 'subscription-renewal-tool', array( $this, 'menu_page' ) );

        /**
         * We need some WooCommerce styles for the admin page
         */
        if ( isset( $_GET['page'] ) && 'subscription-renewal-tool' == $_GET['page'] ) {
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', [ 'wc-components' ], \WC_Subscriptions_Core_Plugin::instance()->get_library_version() );
			wp_enqueue_style( 'woocommerce_subscriptions_admin', \WC_Subscriptions_Core_Plugin::instance()->get_subscriptions_core_directory_url( 'assets/css/admin.css' ), array( 'woocommerce_admin_styles' ), \WC_Subscriptions_Core_Plugin::instance()->get_library_version() );
        }
    }

    /**
     * The menu page content
     * Keeping it simple. There are no dependancies other than Woo Subscriptions CSS.
     */
    public function menu_page(){

        /**
         * Only allow access to this page for users who can manage WooCommerce
         */
        if( ! current_user_can( 'manage_woocommerce' ) ){
            return;
        }
    
        
        // Start the page
        echo '<div class="wrap">';
        echo '<h2>Subscription Renewal Tool</h2>';

        if( isset( $_GET['subscription_id'] ) ){
            // Display the result of the email search
            $this->display_subscription_data();
        }elseif( isset( $_GET['error'] ) ){
            // Display any errors plus the form for a retry
            $this->display_errors();
            $this->display_form();
        }elseif( isset( $_GET['subscription_renewed'] ) ){
            // Display success message plus the form for the next subscription search
           $this->display_success();
            $this->display_form();
        }
        else{
            // Display the form for the first time
            $this->display_form();

        }

        // end the page
        echo '</div>';

    }

    /**
     * Display a success message if we've just processed a subscription renewal
     */
    private function display_success(){

        $subscription_id = absint(  $_GET['subscription_renewed'] );
        $edit_url = admin_url( 'post.php?post=' . $sub_id . '&action=edit' );
        echo '<div class="notice notice-success"><p>Subscription <href="' . $edit_url. '">#' . $subscription_id . '</a> successfully renewed</p></div>';
    }

    /**
     * 
     * Display any errors that have occurred
     * 
     * - we could not find a subscription for the email
     * - the email was invalid
     * - the renewal date was invalid
     * - no user found for the email
     * 
     */
    private function display_errors(){
       
        $error = sanitize_text_field( $_GET['error'] );

        switch( $error ){
            case 'no_subscriptions':
                echo '<div class="notice notice-error"><p>No subscriptions found - remember, this tool works only for manual subscriptions.<br>';
                echo 'Your customer may have a credit card stored against their subscription.</p></div>';
                break;
            case 'invalid_date':
                echo '<div class="notice notice-error"><p>Invalid renewal date supplied</p></div>';
                break;
            case 'no_email':
                echo '<div class="notice notice-error"><p>Invalid email supplied</p></div>';
                break;
                case 'automated_subs':
                    if( isset( $_GET['subs_ids'] ) ){
                        $subs_ids = explode( ',', sanitize_text_field( $_GET['subs_ids'] ));
                    }
                    if( ! empty( $subs_ids ) ){
                        $message = '';
                        foreach( $subs_ids as $sub_id ){
                            $edit_url = admin_url( 'post.php?post=' . $sub_id . '&action=edit' );
                            $message .= '<p>Subscription <a href="' . $edit_url . '">#' . $sub_id . '</a> is automated</p>';
                        }
                    }
            
                echo '<div class="notice notice-error"><p>This customer only has automated subscriptions.</p>' . $message . '</div>';
                break;
            case 'no_user':
                echo '<div class="notice notice-error"><p>No user found for that email</p></div>';
                break;
        }
    }

    /**
     * Display the form to get a subscription by email
     */
    public function display_form(){

        ?>
        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
            <?php echo wp_nonce_field( 'search-subscription' ); ?>
            <input type="hidden" name="action" value="get_subscription_from_email">
            <input type="email" name="email" placeholder="Enter email address">
            <button type="submit" class="button save_order button-primary" name="save" value="Update">Get subscription</button>
        </form>
        <?php
    }


   /**
    * Display the subscription data
    * This is a very simple table of subscription data
    */
    public function display_subscription_data(){

        // Get the subscription IDs from the URL and clean them
        $subscription_ids = array_filter( array_map( 'wc_clean', (array)  explode( ',', $_GET['subscription_id'] ) ));

        // Start the output buffer and display the subscription data
        ob_start();

        echo '<h3>Subscriptions</h3>';

        echo '<table class="wp-list-table widefat fixed striped table-view-list orders wc-orders-list-table wc-orders-list-table-shop_subscription">';

        include( 'admin/views/subscription-list-head-html.php' );

        foreach( $subscription_ids as $subscription_id ){

            $subscription = wcs_get_subscription( $subscription_id );

            // Bail if the subscription is not found
            if( ! $subscription ){
                continue;
            }

            include( 'admin/views/subscription-list-html.php' );
        }

        include( 'admin/views/subscription-list-footer-html.php' );

        echo '</table>';

       echo ob_get_clean();

}


}

add_action( 'admin_menu', array( 'SubscriptionRenewalTool\Menu' , 'instance' ) );