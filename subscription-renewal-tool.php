<?php
/**
 * Plugin Name: Subscription Renewal Tool
 * Plugin URI:,https://github.com/nicdwilson/subscription-renewal-tool
 * Description: A tool to speed up manual renewal processes for subscription products
 * Version: Beta
 * Author: nicw
 * Author URI:
 * Requires Plugins:  woocommerce, woocommerce-subscriptions
 *
*/


namespace SubscriptionRenewalTool;

define( 'SRT_MIN_WC_VER', '8' );
define( 'SRT_MIN_SUBS_VER', '3.0' );

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SubscriptionRenewalTool_Loader{

    /**
	 * Contains load errors.
	 *
	 * @var array
	 */
	public static $errors = array();

    
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init();
    }
    
    private function define_constants() {
        define( 'SRT_PLUGIN_FILE', __FILE__ );
        define( 'SRT_PLUGIN_DIR', plugin_dir_path( SRT_PLUGIN_FILE ) );
        define( 'SRT_PLUGIN_URL', plugin_dir_url( SRT_PLUGIN_FILE ) );
    
    }
    
     private function includes() {
        require_once SRT_PLUGIN_DIR . 'includes/class-subscriptions.php';
        require_once SRT_PLUGIN_DIR . 'includes/class-menu.php';
        
    }

    private function init() {

        if ( self::check() ) {
			   
        // Declare compatibility for WooCommerce features.
        add_action( 'before_woocommerce_init', [ __CLASS__, 'declare_feature_compatibility' ] );
        
        }else{
			// Display admin notices.
			add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ] );
		}
    }
    /**
	 * Checks if the plugin should load.
	 *
	 * @return bool
	 */
	public static function check() {
		$passed = true;

		/* translators: Plugin name. */
		$inactive_text = '<strong>' . sprintf( __( '%s is inactive.', 'automatewoo' ), __( 'Subscription Renewal Tool', 'subscription-renewal-tool' ) ) . '</strong>';

		if ( ! self::is_subs_version_ok() ) {
			/* translators: %1$s inactive plugin text, %2$s minimum Woo Subscriptions version */
			self::$errors[] = sprintf( __( '%1$s The plugin requires Woo Subscriptions version %2$s or newer.', 'subscription-renewal-tool' ), $inactive_text, SRT_MIN_SUBS_VER );
			//$passed         = false;
		}

		return $passed;
	}

    /**
	 * Checks if the installed WooCommerce version is ok.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_version_ok() {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}
		if ( ! SRT_MIN_WC_VER ) {
			return true;
		}
		return version_compare( WC()->version, SRT_MIN_WC_VER, '>=' );
	}

     /**
	 * Checks if the installed WooCommerce version is ok.
	 *
	 * @return bool
	 */
	public static function is_subs_version_ok() {
		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return false;
		}
		if ( ! SRT_MIN_SUBS_VER ) {
			return true;
		}
        $subs = new \WC_Subscriptions_Plugin;
        $version = $subs->get_plugin_version();
		return version_compare( $version, SRT_MIN_SUBS_VER, '>=' );
	}

     /**
	 * Displays any errors as admin notices.
	 */
	public static function admin_notices() {
		if ( empty( self::$errors ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>';
		echo wp_kses_post( implode( '<br>', self::$errors ) );
		echo '</p></div>';
	}

        /**
         * Declare compatibility for WooCommerce features.
         */
        public static function declare_feature_compatibility() {
            if ( class_exists( FeaturesUtil::class ) ) {
                FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
                FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__ );
                FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__ );
            }
        }
}

new SubscriptionRenewalTool_Loader(); 
