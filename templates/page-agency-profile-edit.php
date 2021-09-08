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
//  Generate Globals
// ========================================================
$user = wp_get_current_user();
$userID = $user->ID;
$user_email = $user->user_email;

global $wpdb;
$data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}agency_profiles WHERE id = 1", 'ARRAY_A' );

$acf_group = $data['acf_group'];
$allowed_domains = $data['allowed_domains'];

function add_acf_form_head(){
    global $post;
     acf_form_head();
}
add_action( 'wp_head', 'add_acf_form_head', 7 );


// ========================================================
//  Generate Sidebar and Tabs
// ========================================================

$sidebar = [];
$field_group_key = $acf_group;

$fields = acf_get_fields($field_group_key);

foreach ($fields as $field => $data) {

    if ( $data['type'] === 'group' ) { 
        array_push($sidebar, $data); 
    }
}

function build_profile_sidebar($acf_tab_array, $userID) {

    foreach ($acf_tab_array as $tab ) {
        $menu_item[$tab['ID']] = [ $tab['key'], $tab['label']];    
    }
    
    $output = ''; //begin building the menu
    $i = 0;
    foreach ( $menu_item as $key => $value ) {

        $activeClass = $i === 0 ? "active" : "";

        $output .= '
            <li class="nav-item" role="presentation">
                <a class="nav-link p-3 border-primary border mb-1 rounded-0 '. $activeClass .'" id="'.$value[1].'-tab" data-toggle="tab" href="#'.$value[1].'" role="tab" aria-controls="'.$value[1].'" aria-selected="true">'.$value[1].'</a>
            </li>
            ';
        $i++;
    }
    
    echo $output;
}

function build_profile_form($acf_group, $userID){

    if ( ! empty ( $acf_group ) && ! empty ( $userID ) ) {
        $options = array(
            'post_id' => 'user_'.$userID,
            'field_groups' => array( $acf_group ),
            'return' => add_query_arg( 'updated', 'true', get_permalink() ),
            'html_submit_button'  => '<input type="submit" class="btn btn-primary rounded-0" value="%s" />',
            'updated_message' => __("Your changes have been saved", 'acf'),
            'html_updated_message'  => '<div id="message" class="bg-primary text-white p-3"><p>%s</p></div>',
            'submit_value' => "Save changes"
        );
        
        ob_start();
        
        acf_form( $options );
        $form = ob_get_contents();
        
        ob_end_clean();
    }

    echo $form;
}

// ========================================================
//  Start the Page
// ========================================================
get_header(); ?>


<section class="py-5 bg-primary">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-white">Edit Agency Profile</h1>
            </div>
        </div>
    </div>
</section>

<section id="profile-content" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-3 px-3 mb-5">
                <ul class="nav nav-pills flex-column" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link p-3 border-primary border rounded-0 mb-1" id="edit-tab" href="/agency-profile" >View my profile</a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-md-9">
                <div class="tab-content" id="myTabContent">
                    <?php build_profile_form($acf_group, $userID); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    #profile-content{
        min-height: 100vh;
    }
</style>

<?php get_footer(); ?>


