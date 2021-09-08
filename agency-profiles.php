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

// test

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

    // Detect if ACF is installed first.
    $active_plugins = get_option('active_plugins');
    if( !in_array('advanced-custom-fields/acf.php', $active_plugins) ){
        echo "<b>Please ensure Advanced Custom Fields is installed and active!</b>";
        exit;
    }
    
    // Create the Table
    global $wpdb;
    $table_name = $wpdb->prefix . "agency_profiles";
    $my_products_db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    // Create Table and Put default data in it
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

    // Create the /agency-profile
    if ( null === $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'agency-profile'", 'ARRAY_A' ) ) {

        $current_user = wp_get_current_user();
        
        // create post object
        $page = array(
            'post_title'        => __( 'Agency Profile' ),
            'post_status'       => 'publish',
            'post_author'       => $current_user->ID,
            'post_type'         => 'page',
            'page_template'     => dirname( __FILE__ ) . '/templates/page-agency-profile.php',
        );
        
        // insert the post into the database
        wp_insert_post( $page );
    }

    // Create the /agency-profile-edit
    if ( null === $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'agency-profile-edit'", 'ARRAY_A' ) ) {

        $current_user = wp_get_current_user();
        
        // create post object
        $page = array(
            'post_title'  => __( 'Agency Profile Edit' ),
            'post_status' => 'publish',
            'post_author' => $current_user->ID,
            'post_type'   => 'page',
            // 'page_template'   => 'page',
        );
        
        // insert the post into the database
        wp_insert_post( $page );
    }

    // Create the /agency-profile-view pages
    if ( null === $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'agency-profile-view'", 'ARRAY_A' ) ) {

        $current_user = wp_get_current_user();
        
        // create post object
        $page = array(
            'post_title'  => __( 'Agency Profile View' ),
            'post_status' => 'publish',
            'post_author' => $current_user->ID,
            'post_type'   => 'page',
            // 'page_template'   => 'page',
        );
        
        // insert the post into the database
        wp_insert_post( $page );
    }
    
}


// ========================================================
// Hook and Function to run on plugin deactivations
// ========================================================
register_deactivation_hook( __FILE__, 'agency_profiles_deactivate' );

function agency_profiles_deactivate(){

    // YOU COULD PROBABLY AVOID TURNING OFF THE DB TABLE
    // THAT WAY ALL ITEMS AREE SAVED
    // ONLY CLEAR EVERYTHING ON DELETE/UNINSTALL
    
    // Turn off the table
    global $wpdb;
    $table_name = $wpdb->prefix . 'agency_profiles';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);

    // Delete the pages
    // Agency Profile
    $agency_page_ID = get_page_by_path('/agency-profile');
    wp_delete_post($agency_page_ID->ID);
    
    // Agency Profile Edit
    $agency_page_ID = get_page_by_path('/agency-profile-edit');
    wp_delete_post($agency_page_ID->ID);
    
    // Agency Profile View
    $agency_page_ID = get_page_by_path('/agency-profile-view');
    wp_delete_post($agency_page_ID->ID);
    
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
    
    // Delete the pages
    // Agency Profile
    $agency_page_ID = get_page_by_path('/agency-profile');
    wp_delete_post($agency_page_ID->ID);
    
    // Agency Profile Edit
    $agency_page_ID = get_page_by_path('/agency-profile-edit');
    wp_delete_post($agency_page_ID->ID);
    
    // Agency Profile View
    $agency_page_ID = get_page_by_path('/agency-profile-view');
    wp_delete_post($agency_page_ID->ID);
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

    $success = $wpdb->query( $wpdb->prepare("
        UPDATE $table_name 
        SET acf_group = '". $data['acf_group'] ."',   
            allowed_domains = '". $data['allowed_domains'] ."'  
        WHERE ID = 1")
    );

    if($success){

        wp_redirect( $_SERVER['HTTP_REFERER'] );
        exit();

    } else {
        
        echo "An error occurred. Unable to save to the database table $table";
        exit;
    
    }

}

// ========================================================
// SET THE PAGE TEMPLATES FOR THE AGENCY PAGES
// ========================================================
add_action("template_redirect", 'my_theme_redirect');

function my_theme_redirect() {

    global $wp;
    $plugindir = dirname( __FILE__ );

    if ($wp->query_vars["pagename"] == 'agency-profile') {
        $templatefilename = 'page-agency-profile.php';
        $return_template = $plugindir . '/templates/' . $templatefilename;
        do_theme_redirect($return_template);
    } else if ($wp->query_vars["pagename"] == 'agency-profile-edit') {
        $templatefilename = 'page-agency-profile-edit.php';
        $return_template = $plugindir . '/templates/' . $templatefilename;
        do_theme_redirect($return_template);
    } else if ($wp->query_vars["pagename"] == 'agency-profile-view') {
        $templatefilename = 'page-agency-profile-view.php';
        $return_template = $plugindir . '/templates/' . $templatefilename;
        do_theme_redirect($return_template);
    }
}

function do_theme_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}