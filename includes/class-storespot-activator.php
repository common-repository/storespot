<?php

class StoreSpot_Activator {

	public static function activate() {

		$stsp_settings = get_option('storespot_settings');

		if( !$stsp_settings ) {
			$options = [
				'pixel_id' => null,
				'pixel_enabled' => false,
			];
			add_option( 'storespot_settings', $options );
		}

		set_transient( 'stsp-admin-activation-notice', true, 5 );

	}

}
