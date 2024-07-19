<?php
/**
 * Subscriptions Class
 * 
 * This class handles the subscription renewal tool form submission,
 * and processes the subscription renewal.
 * 
 * The process_subscription_renewal method is responsible for creating a renewal order,
 * and updating the subscription dates.
 * 
 * This could be used by any mthod that needs to handle subscription renewals.
 * 
 * 
 * 
 */

 namespace SubscriptionRenewalTool;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Subscriptions{

    /**
     * Subscriptions The instance of Subscriptions
     *
     * @var    object
     * @access private
     * @since  1.0.0
     */
    private static object $instance;

    /**
     * Main Subscriptions Instance
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

    /**
     * This proof of concept uses the admin_post action to handle form submissions
     * and extract the subscription ID and date from the form submission.
     */
    public function __construct(){
        add_action( 'admin_post_get_subscription_from_email', array( $this, 'get_subscription_from_email') );
        add_action( 'admin_post_process_renewal_tool_subscription_renewal', array( $this, 'subscription_renewal_tool') );
    }

    
    /**
     * Handle the subscription renewal tool form submission
     * and grab the subscription ID and date from the form submission.
     * This then submits the subscription ID and date to the process_subscription_renewal method.
     */
    public function subscription_renewal_tool(){

        if( ! current_user_can( 'manage_woocommerce' ) ){
            return;
        }

        check_admin_referer( 'renew-subscription' );

        $subscription_id = absint ( $_POST['subscription_id'] );
       
        if( 'year' === $_POST['next_renewal'] ){
            $next_renewal = date( 'Y-m-d H:i:s', strtotime( 'now +1 year' ) );
        }else{
            $next_renewal = date( 'Y-m-d H:i:s', strtotime(  $_POST['next_renewal'] ) );
        }

        if( $next_renewal < date( 'Y-m-d H:i:s' ) ){
            $this->exit_error( 'invalid_date' );
        }

        if( ! $next_renewal ){
            $this->exit_error( 'invalid_date' );
        }

        $this->process_subscription_renewal( $subscription_id, $next_renewal );       
    }

    public function process_subscription_renewal( $subscription_id, $next_renewal ){

        $subscription = wcs_get_subscription( $subscription_id );

        if( ! $subscription ){
            $this->exit_error( 'no_subscriptions' );
        }
        
        //do some checks here
        $start_date = $subscription->get_date('start');
        $next_payment_date = $subscription->get_date('next_payment');
        $end_date = $subscription->get_date('end');
        $interval = $subscription->get_billing_interval();
        $period = $subscription->get_billing_period();

        // Create a renewal order
        do_action( 'woocommerce_scheduled_subscription_payment', $subscription_id );

        $order_id = $subscription->get_last_order();
        $order = wc_get_order( $order_id );

        if( ! $order ){
            $this->exit_error( 'renewal_failed' );
        }

        $order->payment_complete();

        $order->add_order_note(
            sprintf(
                /* translators: %s: renewal order number */
                __( 'Created via Subscription Renewal Tool - Renewal order %s', 'subscription-renewal-tool' ),
                $order->get_order_number()
            )
        );

        $now_start_date = $subscription->get_date('start');
        $now_next_payment_date = $subscription->get_date('next_payment');
        $now_end_date = $subscription->get_date('end');

        $dates_to_update = array();

        $dates_to_update['next_payment'] = $next_renewal;
    
        // Update the dates
        $subscription->update_dates($dates_to_update);
    
        // Save the subscription to apply changes
        $subscription->save();

        $subscription->add_order_note(
            sprintf(
                /* translators: %s: renewal order number */
                __( 'Renewed via Subscription Renewal Tool - Renewal order %s', 'subscription-renewal-tool' ),
                $order->get_order_number()
            )
        );

        wp_redirect(admin_url('admin.php?page=subscription-renewal-tool&subscription_renewed=' . $subscription_id));
    }

    public function get_subscription_from_email( $email ){
      
        if( ! current_user_can( 'manage_woocommerce' ) ){
            return;
        }

        check_admin_referer( 'search-subscription' );

        $subscriptions = array();
        $subscription_ids = array();
    

        if( isset( $_POST['email'] ) ){
            $email = sanitize_email( $_POST['email'] );
        }

        $user_id = get_user_by('email', $email)->ID;

        if( empty( $user_id ) ){
           $this->exit_error( 'no_user' );
        }

        $args = array(
            'status' => 'active',
            'customer_id' => $user_id,
        );

        $subscriptions = wcs_get_subscriptions( $args );


        /**
         * Bail if no subscriptions are found
         */
        if( empty( $subscriptions ) ){
            $this->exit_error( 'no_subscriptions' );
        }

        
        foreach( $subscriptions as $subscription ){
            if( $subscription->get_requires_manual_renewal() ){
                $subscription_ids[] = $subscription->get_id();
            }else{
                $automated_subs[] = $subscription->get_id();
            }
        }
        
        if( empty( $subscription_ids ) && empty( $automated_subs ) ){
            $this->exit_error( 'no_subscriptions' );
        }

        if( empty( $subscription_ids ) && ! empty( $automated_subs ) ){
            $automated_subs = implode( ',', $automated_subs );
            $this->exit_error( 'automated_subs&subs_ids=' . $automated_subs );
        }


        $subscription_ids = implode( ',', $subscription_ids );

       wp_redirect(admin_url('admin.php?page=subscription-renewal-tool&subscription_id=' . $subscription_ids));

    }

    public function exit_error( $exit_error){
        $exit_error =  $exit_error;
        wp_redirect( admin_url( 'admin.php?page=subscription-renewal-tool&error=' . $exit_error ) );
        exit;
    }

}

add_action( 'admin_init', array( 'SubscriptionRenewalTool\Subscriptions', 'instance' ) );
