<?php

class StoreSpot_API extends WC_REST_Posts_Controller {
	public $namespace = 'wc/v2';

	public function storespot_api_route() {
		register_rest_route( $this->namespace, '/storespot/', array(
			'methods' 						=> WP_REST_SERVER::EDITABLE,
			'callback'						=> array( $this, 'update_storespot_settings' ),
			'permission_callback' => array( $this, 'update_settings_permission_check' ),
		) );

		register_rest_route( $this->namespace, '/storespot/', array(
			'methods' 						=> WP_REST_SERVER::READABLE,
			'callback'						=> array( $this, 'get_storespot_settings' ),
			'permission_callback' => array( $this, 'get_settings_permission_check' ),
		));
	}

	public function update_settings_permission_check() {
		if ( !wc_rest_check_user_permissions('create') ) {
			return new WP_Error(
				'woocommerce_rest_cannot_create',
				__( 'Woops, it seems you are not allowed to do this.', 'storespot' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	public function get_settings_permission_check() {
		if ( !wc_rest_check_user_permissions('read') ) {
			return new WP_Error(
				'woocommerce_rest_cannot_read',
				__( 'Woops, it seems you are not allowed to do this.', 'storespot' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	public function update_storespot_settings($request) {
		$new_settings = $request['settings'];
		$current_settings = get_option( 'storespot_settings' );

		foreach( $new_settings as $key => $value ) {
			$current_settings[$key] = $value;
		}

		$updated = update_option( 'storespot_settings', $current_settings );
		$data = array( 'updated' => $updated );

		$response = rest_ensure_response($data);
		$response->set_status(200);
		return $response;
	}

	public function get_storespot_settings($request) {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );

		$data = array(
			'settings'		=> get_option( 'storespot_settings' ),
			'logo'			=> $image[0],
			'stsp_version'	=> STORESPOT_VERSION,
			'wp_version'	=> get_bloginfo( 'version' ),
			'wc_version'	=> get_option( 'woocommerce_version' ),
		);

		$response = rest_ensure_response($data);
		$response->set_status(200);
		return $response;
	}

}
