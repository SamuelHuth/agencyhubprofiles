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

$acf_group          = $data['acf_group'];
$allowed_domains    = $data['allowed_domains'];
$primary_color      = $data['primary_color'];
$secondary_color    = $data['secondary_color'];
$background_color   = $data['background_color'];


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
        $menu_item[$tab['ID']] = [ $tab['key'], $tab['label'], $tab['type'], $tab['name']];    
    }
    
    $output = ''; //begin building the menu
    $i = 0;
    foreach ( $menu_item as $key => $value ) {

        $activeClass = $i === 0 ? "active" : "";

        $output .= '
            <li class="nav-item" role="presentation">
                <a class="nav-link p-3 bg-white border mb-1 d-flex justify-content-between align-items-center rounded-0 '. $activeClass .'" id="'.$value[3].'-tab" data-toggle="tab" href="#'.$value[3].'" role="tab" aria-controls="'.$value[3].'" aria-selected="true">'.$value[1].' <span class="chevron right"></span></a>
            </li>
            ';
        $i++;
    }
    
    echo $output;
}

function build_profile_tabs($acf_tab_array, $userID) {

    foreach ($acf_tab_array as $tab ) {
        $tabs[$tab['ID']] = [ $tab['key'], $tab['label'], $tab['type'], $tab['name']];    
    }

    
    $i = 0;
    foreach ( $tabs as $key => $value ) {
        
        $activeClass = $i === 0 ? "show active" : "";

        ?>
        
        
        <div class="tab-pane fade bg-white <?= $activeClass; ?> " id="<?= $value[3]; ?>" role="tabpanel" aria-labelledby="<?= $value[3]; ?>-tab">
            <h2 class="p-3 bg-primary text-white mb-0 font-weight-bold"><?= $value[1]; ?></h2>
            <?php
                if ( get_field($tabs[$key][0], 'user_'.$userID) ){

                    while (have_rows( $tabs[$key][0], 'user_'.$userID)){

                        $rows = the_row();

                        foreach($rows as $row => $value){

                            echo "<div class='p-3 bg-white rounded-0 border mb-1'>";
                            
                            $content = get_sub_field_object($row, 'user_'.$userID);

                            if($content['type'] == 'group'){
                                
                                $subcontent = get_sub_field_object($content['key'], 'user_'.$userID);
                                echo "<h3>". $content['label']."</h3>";
                                
                                foreach( $subcontent['sub_fields'] as $subfield ){
                                    echo "<h4>". $subfield['label']."</h4>";
                                    echo "<p>". $content['value'][$subfield['name']]."</p>";
                                    
                                }
                            } else if ($content['type'] == 'repeater') {

                                if( empty( $content['value'] ) ){
                                    
                                    echo "Please add your content to: " . $content['label'];

                                } else {


                                    echo "<h3>". $content['label']."</h3>";
                                    foreach( $content['value'] as $key => $goal ){

                                        echo "<div class='p-2 border-bottom'>";

                                        foreach($goal as $goal_title => $goal_value){

                                            $goal_value_output = $goal_value;

                                            if( gettype($goal_value) == 'array' && $goal_title == 'upload_an_image_of_your_goal' ){
                                                
                                                echo "<img src='". $goal_value['url'] ."' alt='' class='w-100'>";

                                            } else {

                                            ?>

                                            <p><span class="font-weight-bold text-capitalize"><?= str_replace("_", " ", $goal_title);?>:</span> <?= $goal_value_output; ?></p>

                                            <?php

                                            }

                                        }

                                        echo "</div>";

                                    }

                                }

                            } else if($content['type'] == 'image'){

                                echo "<img src='". $content['value']['url'] ."' alt='' class='w-100'>";

                            } else if($content['type'] == 'url'){
                                
                                echo "<h3>". $content['label']."</h3>";

                                ?>

                                    <iframe class="airtable-embed" src="<?= $content['value']; ?>" frameborder="0" onmousewheel="" width="100%" height="533" style="background: transparent; border: 1px solid #ccc;"></iframe>

                                <?php

                            } else {
                                
                                echo "<h3>". $content['label']."</h3>";
                                echo "<p>". $content['value']."</p>";
                                
                            }
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


<section class="py-5" id="profile-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-white">My Profile</h1>
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
                        <a class="nav-link p-3 bg-white border rounded-0 mb-1 d-flex justify-content-between align-items-center" href="/agency-profile-edit" >Edit my profile </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link p-3 bg-white border rounded-0 mb-1 d-flex justify-content-between align-items-center" href="/agency-profile-view" >View all profiles</a>
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
    #profile-header{
        background: <?= $primary_color; ?>;
    }
    #profile-content{
        min-height: 100vh;
        background: <?= $background_color; ?>;
    }
    #profile-content a{
        color: <?= $primary_color; ?>;
    }
    #profile-content a:hover{
        background: <?= $secondary_color; ?>!important;
        /* opacity: .4; */
        color: #fff;
    }
    #profile-content a.active, .bg-primary{
        background-color:<?= $primary_color; ?>!important;
        color: #fff;
    }
    .chevron::before {
        border-style: solid;
        border-width: 0.2em 0.2em 0 0;
        content: '';
        display: inline-block;
        height: .6em;
        left: 0.15em;
        position: relative;
        top: 7px;
        transform: rotate(-45deg);
        vertical-align: top;
        width: .6em;
    }

    .chevron.right:before {
        left: 0;
        transform: rotate(45deg);
    }

</style>

<?php get_footer(); ?>


