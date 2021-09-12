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
                `primary_color` text NOT NULL,
                `secondary_color` text NOT NULL,
                `background_color` text NOT NULL,
                PRIMARY KEY  (ID)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option('my_db_version', $my_products_db_version);

        // Set a default empty row for updating
        $data = array(
            'acf_group' => "",
            'allowed_domains'   => "",
            'primary_color'     => "",
            'secondary_color'   => "",
            'background_color'  => ""
        );
        $format = array(
            '%s',
            '%s',
            '%s',
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
    $primary_color = "";
    $secondary_color = "";
    $background_color = "";
    
    if($result){
        $acf_group = $result[0]->acf_group;
        $allowed_domains = $result[0]->allowed_domains;
        $primary_color = $result[0]->primary_color;
        $secondary_color = $result[0]->secondary_color;
        $background_color = $result[0]->background_color;

    }

    ?>
    <h1>Agency Profiles</h1>
    <p><i>Welcome to Agency Hub Profiles!</i></p>
    <p>Here you can set some custom fields to display on your agency profile. Set fields for goals, strategies or anything.<br />Your staff members will be able to view and edit these anytime.</p>
    <hr />
    <p><b>Instructions</b></p>
    <p>Create your custom fields in the "Advanced Custom Fields plugin".</p>
    <p>Every top level field must be a group. This defines your tabs on the Profile Pages.</p>
    <p>In each group, you can input as many fields as you can. You can add one sub-group level to each tab if you wish to segment fields.</p>
    <p>You can enter a URL field to display an Airtable Report Embed.</p>
    <p>Enter the Advanced Custom Field Group ID into the field below.</p>
    <p>Enter your valid email address domains to enable profile viewing.</p>
    <p>Customise your page with your brand colours</p>
    <br />
    <p><small>Any issues contact <a href="mailto:sam@stafflink.com.au">sam@stafflink.com.au</a></small></p>
    <hr />
    <form method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">
        
        <br />
        <label for="acf_group"><b>ACF Group ID</b></label>
        <br />
        <p><i>e.g. group_6136a4705950f</i></p>
        <input type="text" value="<?= $acf_group; ?>" name="acf_group" id="acf_group" required>
        <br />
        <br />
        <br />
        <label for="allowed_domains"><b>Allowed Email Domains</b></label>
        <br />
        <p>Comma Separated list of domains.</p>
        <p><i>e.g. stafflink.com.au, google.com.au</i></p>
        <input type="text" value="<?= $allowed_domains; ?>" name="allowed_domains" id="allowed_domains" required>
        <br />
        <br />
        <br />
        <label for="primary_color"><b>Primary Colour</b></label>
        <br />
        <small>Main Colours</small>
        <br />
        <br />
        <input type="color" value="<?= $primary_color; ?>" name="primary_color" id="primary_color">
        <br />
        <br />
        <br />
        <label for="secondary_color"><b>Secondary Colour</b></label>
        <br />
        <small>Hover States</small>
        <br />
        <br />
        <input type="color" value="<?= $secondary_color; ?>" name="secondary_color" id="secondary_color">
        <br />
        <br />
        <br />
        <label for="background_color"><b>background Colour</b></label>
        <br />
        <small>Content Background</small>
        <br />
        <br />
        <input type="color" value="<?= $background_color; ?>" name="background_color" id="background_color">
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
        'allowed_domains'   => $_POST['allowed_domains'],
        'primary_color'     => $_POST['primary_color'],
        'secondary_color'   => $_POST['secondary_color'],
        'background_color'  => $_POST['background_color'],
    );
    echo "<pre>";
    print_r($data);

    $success = $wpdb->query( $wpdb->prepare("
        UPDATE $table_name 
        SET acf_group = '". $data['acf_group'] ."',   
            allowed_domains = '". $data['allowed_domains'] ."',  
            primary_color = '". $data['primary_color'] ."',  
            secondary_color = '". $data['secondary_color'] ."',  
            background_color = '". $data['background_color'] ."'  
        WHERE ID = 1")
    );

    if($success){

        wp_redirect( $_SERVER['HTTP_REFERER'] );
        exit();

    } else {
        
        echo "An error occurred. Unable to save to the database table $table";
        echo "<br />";
        echo "This might be that weird bug where if you dont make any changes and press update it fails... cause there is nothing to update";
        echo "<br />";
        echo "Just press the back button";
        exit;
    
    }

}

// ========================================================
// SET THE PAGE TEMPLATES FOR THE AGENCY PAGES
// ========================================================

add_filter( 'template_include', 'set_custom_template', 99 );

function set_custom_template( $template ) {

    $new_template = '';


    global $wp;

    if( $wp->query_vars["pagename"] == 'agency-profile'){
        $plugin_template = dirname( __FILE__ ) . "/templates/page-agency-profile.php";
        
    } else if ($wp->query_vars["pagename"] == 'agency-profile-edit'){
        $plugin_template = dirname( __FILE__ ) . "/templates/page-agency-profile-edit.php";
        
    } else if ($wp->query_vars["pagename"] == 'agency-profile-view'){
        $plugin_template = dirname( __FILE__ ) . "/templates/page-agency-profile-view.php";

    }
    

    if( file_exists( $plugin_template ) ) {
    
        return $plugin_template;
    
    } else {
        echo "Failed to find template, contact STAFFLINK";
    }

    return $template;
}