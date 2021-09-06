<?php
/**
 * Plugin Name
 *
 * @package           AgencyProfiles
 * @author            STAFFLINK
 * @copyright         2021 STAFFLINK
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Agency Profiles
 * Plugin URI:        http://stafflink.academy
 * Description:       View/Edit your agency staff members profile. Set goals, tasks or reminders about your agency!
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            STAFFLINK
 * Author URI:       http://www.stafflink.com.au
 * Text Domain:       agency-profiles
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// ========================================================
//  Do not allow if accessed from the direct path
// ========================================================
defined( 'ABSPATH' ) || exit;


// ========================================================
// Define variable paths for the plugin
// ========================================================
define('agency_profiles_location',      dirname(__FILE__));
define('agency_profiles_location_url',  plugins_url(__FILE__));


// ========================================================
//  Hook and Function to run on plugin activation
// ========================================================
register_activation_hook( __FILE__, 'agency_profiles_activate' );

function agency_profiles_activate(){
    
    // Create the Table
    global $wpdb;
    $table_name = $wpdb->prefix . "agency_profiles";
    $my_products_db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {

        $sql = "CREATE TABLE $table_name (
                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                `acf_group` text NOT NULL,
                `allowed_domains` text NOT NULL,
                PRIMARY KEY  (ID)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option('my_db_version', $my_products_db_version);
    }

}


// ========================================================
// Hook and Function to run on plugin deactivations
// ========================================================
register_deactivation_hook( __FILE__, 'agency_profiles_deactivate' );

function aagency_profiles_deactivate(){
    
    // Do anything?

}


// ========================================================
// Hook and Function to run on plugin uninstall
// ========================================================
register_uninstall_hook( __FILE__, 'agency_profiles_uninstall' );

function agency_profiles_uninstall(){
    
    // Remove the table
    global $wpdb;
    $table_name = $wpdb->prefix . 'agency_profiles';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);

}