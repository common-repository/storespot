<?php
class StoreSpot_Deactivator {
	public static function deactivate() {
		delete_option( 'storespot_settings' );
	}
}
