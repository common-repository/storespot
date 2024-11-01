<?php

class StoreSpot {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	public function __construct() {
		if ( defined( 'STORESPOT_VERSION' ) ) {
			$this->version = STORESPOT_VERSION;
		} else {
			$this->version = '0.0.0';
		}
		$this->plugin_name = 'storespot';

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init() {
		if ( class_exists( 'WC_Integration' ) ) {
			$loaded = $this->load_dependencies();
			if( $loaded ) {
				$this->define_admin_hooks();
				$this->define_public_hooks();
				$this->define_api_hooks();
				$this->run();
			}
		}
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-storespot-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-storespot-messages.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-storespot-events.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-storespot-facebook-pixel.php';

		if(
			! class_exists( 'StoreSpot_Loader' ) ||
			! class_exists( 'StoreSpot_Messages' ) ||
			! class_exists( 'StoreSpot_Events' ) ||
			! class_exists( 'StoreSpot_Facebook_Pixel' )
		) {
			return false;
		} else {
			$this->loader = new StoreSpot_Loader();
			return true;
		}
	}

	private function define_admin_hooks() {
			$admin_message = new StoreSpot_Messages();
			$this->loader->add_action( 'admin_notices', $admin_message, 'activation_notice' );
	}

	private function define_public_hooks() {
		$plugin_public = new StoreSpot_Events( $this->get_plugin_name(), $this->get_version() );
		$pixel_id = $plugin_public->facebook_pixel->get_pixel_id();
		if( $pixel_id ) {
			$this->loader->add_action( 'wp_head', $plugin_public, 'render_facebook_pixel' );
			$this->loader->add_action( 'wp_footer', $plugin_public, 'render_facebook_pixel_noscript' );
			$this->loader->add_action( 'woocommerce_after_single_product', $plugin_public, 'render_product_view_event' );
			$this->loader->add_action( 'woocommerce_after_shop_loop', $plugin_public, 'render_category_view_event' );
			$this->loader->add_action( 'pre_get_posts', $plugin_public, 'render_search_event' );
			$this->loader->add_action( 'woocommerce_add_to_cart', $plugin_public, 'render_add_to_cart_event', 10, 4 );
			$this->loader->add_action( 'woocommerce_after_checkout_form', $plugin_public, 'render_initiate_checkout_event' );
			$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'render_purchase_event' );
			$this->loader->add_action( 'woocommerce_payment_complete', $plugin_public, 'render_purchase_event' );
			$this->loader->add_action( 'wp_footer', $plugin_public, 'custom_jquery_add_to_cart_script' );
			$this->loader->add_action( 'wc_ajax_stsp_add_to_cart_event', $plugin_public, 'render_ajax_add_to_cart_event' );
		}
	}

	private function define_api_hooks() {
		add_action( 'rest_api_init', function() {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-storespot-api.php';
			if ( class_exists( 'StoreSpot_API' ) ) {
				$api = new StoreSpot_API();
				$api->storespot_api_route();
			}
		});
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}
