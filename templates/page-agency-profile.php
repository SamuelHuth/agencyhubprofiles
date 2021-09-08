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

function build_profile_tabs($acf_tab_array, $userID) {

    foreach ($acf_tab_array as $tab ) {
        $tabs[$tab['ID']] = [ $tab['key'], $tab['label']];    
    }
    
    $i = 0;
    foreach ( $tabs as $key => $value ) {
        
        $activeClass = $i === 0 ? "show active" : "";
        ?>
        
        
        <div class="tab-pane fade <?= $activeClass; ?> " id="<?= $value[1]; ?>" role="tabpanel" aria-labelledby="<?= $value[1]; ?>-tab">
            <h2 class="p-3 bg-primary text-white mb-1"><?= $value[1]; ?></h2>
            <?php
                if ( get_field($tabs[$key][0], 'user_'.$userID) ){
                    
                    $tab_field = get_field($tabs[$key][0], 'user_'.$userID);
                    
                    while (have_rows( $tabs[$key][0], 'user_'.$userID)){

                        $rows = the_row();

                        // echo "<pre>";
                        // print_r($rows);
                        // echo "</pre>";
                        
                        foreach($rows as $row => $value){

                            echo "<div class='p-3 bg-light rounded-0 border border-primary mb-1'>";

                            $content = get_sub_field_object($row, 'user_'.$userID);

                            echo "<h3>". $content['label']."</h3>";
                            echo "<p>". $content['value']."</p>";
                            echo "</div>";
                            
                        }

                    }

                } else {
                    echo "No details found";
                }
            ?>

        </div>
        
        <?php

        $i++;

    }
    
}


// ========================================================
//  Start the Page
// ========================================================
get_header(); ?>


<section class="py-5 bg-primary">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-white">Agency Profile</h1>
            </div>
        </div>
    </div>
</section>

<section id="profile-content" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-3 px-3 mb-5">
                <ul class="nav nav-pills flex-column" id="myTab" role="tablist">

                    <?php build_profile_sidebar($sidebar, $userID); ?>
                    
                    <li class="nav-item" role="presentation">
                        <a class="nav-link p-3 border-primary border rounded-0 mb-1" id="edit-tab" href="/agency-profile-edit" >Edit my profile</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link p-3 border-primary border rounded-0 mb-1" id="edit-tab" href="/agency-profile-view" >View all profiles</a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-md-9">
                <div class="tab-content" id="myTabContent">
                    <?php build_profile_tabs($sidebar, $userID); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    #profile-content{
        min-height: 100vh;
        background: yellow;
    }
</style>

<?php get_footer(); ?>


