<?php


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

    public function add_menu(){

        add_menu_page( 'Subscription Renewal Tool', 'Subscription Renewal Tool', 'manage_options', 'subscription-renewal-tool', array( $this, 'menu_page' ) );

        if ( isset( $_GET['page'] ) && 'subscription-renewal-tool' == $_GET['page'] ) {
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', [ 'wc-components' ], \WC_Subscriptions_Core_Plugin::instance()->get_library_version() );
			wp_enqueue_style( 'woocommerce_subscriptions_admin', \WC_Subscriptions_Core_Plugin::instance()->get_subscriptions_core_directory_url( 'assets/css/admin.css' ), array( 'woocommerce_admin_styles' ), \WC_Subscriptions_Core_Plugin::instance()->get_library_version() );
        }
    }

    public function menu_page(){

        if( ! current_user_can( 'manage_woocommerce' ) ){
            return;
        }
    
        ?>

        <div class="wrap">
            <h2>Subscription Renewal Tool</h2>

        <?php

        if( isset( $_GET['subscription_id'] ) ){
            $this->display_subscription_data();
        }elseif( isset( $_GET['error'] ) ){
            $this->display_errors();
            $this->display_form();
        }elseif( isset( $_GET['subscription_renewed'] ) ){
           $this->display_success();
            $this->display_form();
        }
        else{
            $this->display_form();

        }

        ?>

        </div>
        <?php

    }

    private function display_success(){

        $subscription_id = absint(  $_GET['subscription_renewed'] );
        
        echo '<div class="notice notice-success"><p>Subscription #' . $subscription_id . ' successfully renewed</p></div>';
    }

    private function display_errors(){
       
        $error = sanitize_text_field( $_GET['error'] );

        switch( $error ){
            case 'no_subscriptions':
                echo '<div class="notice notice-error"><p>No subscriptions found</p></div>';
                break;
            case 'invalid_date':
                echo '<div class="notice notice-error"><p>Invalid renewal date supplied</p></div>';
                break;
            case 'no_email':
                echo '<div class="notice notice-error"><p>Invalid email supplied</p></div>';
                break;
            case 'no_user':
                echo '<div class="notice notice-error"><p>No user found for that email</p></div>';
                break;
        }
    }

    public function display_form(){

        ?>
        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
            <input type="hidden" name="action" value="get_subscription_from_email">
            <input type="email" name="email" placeholder="Enter email address">
            <button type="submit" class="button save_order button-primary" name="save" value="Update">Get subscription</button>
        </form>
        <?php
    }

   
    public function display_subscription_data(){
        ?>
        <h3>Subscription Data</h3>
        <?php
        
        $subscription_ids = array_filter( array_map( 'wc_clean', (array)  explode( ',', $_GET['subscription_id'] ) ));


        $subscriptions = wcs_get_subscriptions( array( 'subscriptions' => $subscription_ids ) );

        if( empty( $subscriptions ) ){
            echo '<p>No subscriptions found</p>';
            $this->display_form();
        }

        ob_start();

        echo '<table class="wp-list-table widefat fixed striped table-view-list orders wc-orders-list-table wc-orders-list-table-shop_subscription">';

        include( 'admin/views/subscription-list-head-html.php' );

        foreach( $subscriptions as $subscription ){
            include( 'admin/views/subscription-list-html.php' );
        }

        include( 'admin/views/subscription-list-footer-html.php' );

        echo '</table>';

       echo ob_get_clean();

}


}

add_action( 'admin_menu', array( 'SubscriptionRenewalTool\Menu' , 'instance' ) );