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
    if ( $data['type'] === 'tab' ) { 
        array_push($sidebar, $data); 
    }
}

function build_profile_sidebar($acf_tab_array) {

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

function build_profile_tabs($acf_tab_array) {

    foreach ($acf_tab_array as $tab ) {
        $menu_item[$tab['ID']] = [ $tab['key'], $tab['label']];    
    }
    
    $output = ''; //begin building the menu
    $i = 0;
    foreach ( $menu_item as $key => $value ) {

        $activeClass = $i === 0 ? "show active" : "";

        $output .= '
            <div class="tab-pane fade '. $activeClass .'" id="'.$value[1].'" role="tabpanel" aria-labelledby="'.$value[1].'-tab">
                <p>'.$value[1].'</p>
            </div>
            ';
        $i++;
    }
    
    echo $output;
}


// ========================================================
//  Start the Page
// ========================================================
get_header(); ?>


<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-5">
                <h1>Agency Profile</h1>
            </div>
            <div class="col-12 col-md-3 px-3">
                <ul class="nav nav-pills flex-column" id="myTab" role="tablist">

                    <?php build_profile_sidebar($sidebar); ?>
                    
                    <li class="nav-item" role="presentation">
                        <a class="nav-link p-3 border-primary border rounded-0 mb-1" id="edit-tab" href="/agency-profile-edit" >Edit Profile</a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-md-9">
                <div class="tab-content p-3 bg-light rounded-0 border border-primary" id="myTabContent">
                    <?php build_profile_tabs($sidebar); ?>
                </div>
            </div>
        </div>
    </div>
</section>








<div id="" class="profile-container">
    <div class="dashboard-view">

        <div class="profile-sidebar">
            <div class="inner-sidebar">
                <?php #build_profile_sidebar($sidebar); ?>
            </div>
        </div>

        <div class="profile-display container">
            <div class="tabs">

    

                <?php 
                

$tabs = acf_get_fields($field_group_key);

    for ($l = 0; $l < sizeof($tabs); $l++ ) {
        
        $type = $tabs[$l]['type'];
        
        if($type == 'tab') {
            
            $start = $l;
            $name = $tabs[$l]['label'];
            $tabID = $tabs[$l]['ID'];
        }
        
        if ( ( $tabs[$l+1]['type'] == 'tab' ) || ( $l == sizeof($tabs) - 1 ) ) {
            
            $end = $l;

            $activeTabClass = $start == 0 ? "active" : "";
            
            echo '<div id="'.$tabID.'" class="tabcontent '. $activeTabClass .'">';
            echo '<h2>'.$name.'</h2>';
            


            while($start <= $end) {

                if ( get_field($tabs[$start]['key'], 'user_'.$userID) && $tabs[$start]['type'] != 'tab' ) : 

                    echo '<strong style="margin:20px;">'.$tabs[$start]['label'].'</strong>';

                    if ( $tabs[$start]['type'] === 'repeater') : 

                        if( have_rows( $tabs[$start]['key'], 'user_'.$userID) ):

                            $goal_number = '1';

                            while( have_rows( $tabs[$start]['key'], 'user_'.$userID) ) : $rows = the_row();
                            
                                echo '<div class="goal card">';

                                foreach ( $rows as $key => $value ) {
                            
                                    $select = get_sub_field_object($key);

                                    if ($select['type'] === 'image') {
      
                                        if( $select['value'] ) {
                                            echo wp_get_attachment_image( $select['value'], 'full' );
                                        }

                                    } else { 

                                        echo '<div class="goal-item"><h4>'.$select['label'].'</h4>';
                                        echo '<p>'.$select['value'].'</p></div>';

                                    }

                                }

                                echo '</div>';
                            
                                $goal_number++;                            

                            endwhile;

                        endif;

                    endif;

                    if ( $tabs[$start]['type'] === 'group') :  


                        echo '<div class="goal card">';

                        if( have_rows($tabs[$start]['name'], 'user_'.$userID) ):

                            $group_object = get_field_object($tabs[$start]['name'], 'user_'.$userID);

                            echo '<h2>'.$group_object['label'].'</h2>';
                        
                            while( have_rows($tabs[$start]['name'], 'user_'.$userID ) ): $rows = the_row(); 

                            foreach ( $rows as $key => $value ) {
                                $select = get_sub_field_object($key);

                                echo '<div class="goal-item"><strong>'.$select['label'].'</strong>';
                                echo '<p>'.$select['value'].'</p></div>';

                            }
                        
                            endwhile;

                       endif;   
                       
                       echo '</div>';

                       if ($group_object['name'] === 'targets') : 

                            echo '<div class="supported-by" style="margin-top: 30px;">';
                            the_field('image_sales_wysiwyg', 'option');
                            
                            echo '</div>';

                       endif;

                    endif;

                    if ( $tabs[$start]['type'] === 'image') :

                        $image = get_field($tabs[$start]['key'], 'user_'.$userID);
                        if( $image ) {
                            echo '<div class="vision-image">'. wp_get_attachment_image( $image['ID'], 'full' ).'</div>';
                        
                        }

                    endif;

                endif;

                $start++;
            
            }

            echo '</div>';
        } 
    
    }
?>

            </div>           </div>

		<?php #astra_primary_content_bottom(); ?>

	</div>
	</div><!-- #primary -->


<style>

/* ========================================= */
/* STYLESSSSSSSSSS */
/* ========================================= 

.card {
    padding: 20px;
    box-shadow: 1px 1px 35px rgb(0 0 0 / 5%);
    margin: 20px;
    border-radius: 10px;
    display: flex;
    flex-wrap: wrap;
    position: relative;
}

.card img {
    width: calc(100% - 20px);
    max-height: 350px;
    border-radius: 5px;
    margin-top: 0px;
    object-fit: cover;
    margin: 0 auto;
}

.card h3 {
    width: 100%;
    margin-left: 10px;
}

.card .goal-item {
    width: 100%;
    padding: 10px;
    margin: 10px;
    border-bottom: 1px solid #efefef;
}

.card .goal-item strong {
    font-size: 14px;
    color: #000;
}

.profile-sidebar .btn.sidebar-cta, .btn.sidebar-cta {
    margin: 15px;
    border: 1px solid #f7941c;
    border-radius: 5px;
    text-align: center;
    line-height: 24px;
    font-size: 14px;
    padding: 10px;
}

.profile-container {
    min-height: 400px;
    max-width: 1400px;
}
article {
	display: none;
}

.notice-bar-wrapper {
	position: relative;
	margin-left: 0px;
	z-index: 2;
}

.profile-display{
    min-height: 100vh;
}

.profile-sidebar {
    width: 240px;
    display: inline-block;
    position: sticky;
    background-color: #fff;
    float: left;
    top: 0;
    min-height: 100vh;
    box-shadow: 1px 1px 35px rgb(0 0 0 / 8%);
    margin-bottom: 20px;
    border-radius: 5px;
}

.admin-bar .profile-sidebar {
    top: 32px;
    min-height: calc(100vh - 32px);
}

.profile-sidebar .inner-sidebar {
    background-color: rgba(0,0,0,0);
    display: flex;
    flex-direction: column;
}

.profile-sidebar .btn {
    font-size: 14px;
    line-height: 38px;
    color: #121212;
    padding: 10px;
    margin: 0;
    font-weight: normal;
    border-width: 1px 0;
    border-radius: 0;
    background: transparent;
    border-top: none;
    border-color: #000!important;
}

.profile-sidebar .btn:hover {
    color: #000;
    background-color: #dedede;
}

.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

.tabcontent {
  display: none;
  padding-top: 40px;
  position: relative;
}
.tabcontent.active {
  display: block;
}

.dashboard-view .profile-display {
    width: calc(100% - 240px);
    padding-left: 40px;
    display: inline-block;
}

.vision-image img {
    max-height: 600px;
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 20px;
}

.tabcontent h2 {
    margin: 20px;
    font-weight: bold;
}
.tabcontent .btn {
    margin-top: 20px;
    position: absolute;
    right: 10px;
    top: 20px;
}

.user-selector{
    background: #ddd;
    padding: 20px;
    display: flex;
    align-items: center;
}
.user-selector *{
    display: inline;
}

.user-selector select{
    width: 200px;
    margin-right: 20px;
}

.user-selector p{
    margin-bottom: 0;
    margin-left: 50px;
}
*/
</style>

<?php get_footer(); ?>


