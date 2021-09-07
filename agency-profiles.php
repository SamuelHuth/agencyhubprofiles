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

        // Set a default empty row for updating
        $data = array(
            'acf_group' => "",
            'allowed_domains'    => ""
        );
        $format = array(
            '%s',
            '%s'
        );
        $success = $wpdb->insert( $table_name, $data, $format );

    }

}


// ========================================================
// Hook and Function to run on plugin deactivations
// ========================================================
register_deactivation_hook( __FILE__, 'agency_profiles_deactivate' );

function agency_profiles_deactivate(){
    
    // Do anything?
    global $wpdb;
    $table_name = $wpdb->prefix . 'agency_profiles';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);

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


// ========================================================
// Add Menu Option
// ========================================================
function my_admin_menu() {
    add_menu_page(
        __( 'Agency Profiles', 'agency-profiles' ),
        __( 'Agency Profiles', 'agency-profiles' ),
        'manage_options',
        'admin-page',
        'my_admin_page_contents',
        'dashicons-groups',
        100
    );
}

add_action( 'admin_menu', 'my_admin_menu' );


// ========================================================
// Create Admin Page
// ========================================================
function my_admin_page_contents() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'agency_profiles';

    $result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name WHERE ID = 1"));

    $acf_group = "";
    $allowed_domains = "";
    
    if($result){
        $acf_group = $result[0]->acf_group;
        $allowed_domains = $result[0]->allowed_domains;

    }

    ?>
    <h1>Agency Profiles</h1>
    <form method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">
        
        <br />
        <label for="acf_group"><b>ACF Group ID</b></label>
        <br />
        <p><i>e.g. group_6136a4705950f</i></p>
        <input type="text" value="<?= $acf_group; ?>" name="acf_group" id="acf_group">
        <br />
        <br />
        <br />
        <label for="allowed_domains"><b>Allowed Email Domains</b></label>
        <br />
        <p>Comma Separated list of domains.</p>
        <p><i>e.g. stafflink.com.au, google.com.au</i></p>
        <input type="text" value="<?= $allowed_domains; ?>" name="allowed_domains" id="allowed_domains">
        <br />
        <br />
        <input type="hidden" name="action" value="save_agency_profile_admin" />
        <input type="submit" value="Save Settings" />
    </form>
    <?php
}


// ========================================================
// Save the Results to the DB
// ========================================================
add_action( 'admin_action_save_agency_profile_admin', 'admin_save_action' );
function admin_save_action()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'agency_profiles';
    $data = array(
        'acf_group' => $_POST['acf_group'],
        'allowed_domains'    => $_POST['allowed_domains']
    );

    // echo "UPDATE $table_name 
    // SET acf_group = '". $data['acf_group'] ."',   
    //     allowed_domains = '". $data['allowed_domains'] ."'  
    // WHERE ID = 1"; exit;

    $success = $wpdb->query( $wpdb->prepare("
        UPDATE $table_name 
        SET acf_group = '". $data['acf_group'] ."',   
            allowed_domains = '". $data['allowed_domains'] ."'  
        WHERE ID = 1")
    );


    // $success=$wpdb->insert( $table, $data, $format );
    if($success){

        wp_redirect( $_SERVER['HTTP_REFERER'] );
        exit();

    } else {
        
        echo "An error occurred. Unable to save to the database table $table";
        exit;
    
    }

}