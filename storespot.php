<?php
/**
 * Plugin Name:  StoreSpot
 * Plugin URI:   https://storespot.io/
 * Description:  Stop leaving money on the table. Automate your retargeting ads with StoreSpot.
 * Version:      1.1.3
 * Author:       StoreSpot
**/

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! defined( 'WPINC' ) ) { die; }

define( 'STORESPOT_VERSION', '1.1.3' );

function activate_storespot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-storespot-activator.php';
	StoreSpot_Activator::activate();
}

function deactivate_storespot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-storespot-deactivator.php';
	StoreSpot_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_storespot' );
register_deactivation_hook( __FILE__, 'deactivate_storespot' );

require plugin_dir_path( __FILE__ ) . 'includes/class-storespot.php';

function run_storespot() {
	$plugin = new StoreSpot();
}

run_storespot();
