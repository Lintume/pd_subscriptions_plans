<?php
/*
Plugin Name: pd_subscriptions_plans
Description: Create price list by shortcode
Version: 1.2
Author: Pari
*/

if(!defined('PRICE_PLUGIN_URL')) 
  define('PRICE_PLUGIN_URL', plugin_dir_url( __FILE__ ));

if(!defined('PRICE_PLUGIN_DIR')) 
  define('PRICE_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

add_action( 'admin_menu', 'my_plugin_menu');//Add a hook to the menu in the console

function my_plugin_menu() //menu and submenu
{
  add_menu_page( 'My Plugin Options', 'PD subscriptions plans', 8, PRICE_PLUGIN_DIR.'notifications.php', '', 'dashicons-groups', 8);
  add_submenu_page( PRICE_PLUGIN_DIR.'notifications.php', 'Notifications', 'Notifications', 8, PRICE_PLUGIN_DIR.'notifications.php' );
  add_submenu_page( PRICE_PLUGIN_DIR.'notifications.php', 'Plans', 'Plans', 8, PRICE_PLUGIN_DIR.'plans.php' );
}

add_action ( 'init', 'myStartSession', 1);//hooks for session

function notif_scripts_basic()  
{  
  wp_register_script( 'custom-script5', PRICE_PLUGIN_URL.'assests/jquery-1.12.1.js', __FILE__ );  
  wp_enqueue_script( 'custom-script5' ); 

  wp_register_script( 'custom-script', PRICE_PLUGIN_URL.'assests/bootstrap.min.js', __FILE__ );  
  wp_enqueue_script( 'custom-script' );  

  wp_register_script( 'custom-script4', PRICE_PLUGIN_URL.'jquery-palette-color-picker-master/ready.js', __FILE__ );  
  wp_enqueue_script( 'custom-script4' );

  wp_register_script( 'custom-script3', PRICE_PLUGIN_URL.'jquery-palette-color-picker-master/src/palette-color-picker.js', __FILE__ );  
  wp_enqueue_script( 'custom-script3' );  
}  
add_action( 'wp_enqueue_scripts', 'notif_scripts_basic' );
add_action( 'admin_enqueue_scripts', 'notif_scripts_basic' );

function notif_style_basic()  
{   
  wp_register_style( 'custom-style3', PRICE_PLUGIN_URL.'css/bootstrap.css', __FILE__ );  
  wp_enqueue_style( 'custom-style3' );  

  wp_register_style( 'custom-style', PRICE_PLUGIN_URL.'jquery-palette-color-picker-master/src/palette-color-picker.css', __FILE__ );  
  wp_enqueue_style( 'custom-style' ); 
}  
add_action( 'wp_enqueue_scripts', 'notif_style_basic' );
add_action( 'admin_enqueue_scripts', 'notif_style_basic' );

function myStartSession() {
   if(!session_id()) {
      session_start();
    }
}

add_action( 'wp_ajax_subscribe_to_plan', 'subscribe_to_plan_callback' );//hook on ajax responce for authorised users

function subscribe_to_plan_callback()//ajax request handler subscribe on plan
{
    $first_name  = $_GET['first_name'];
    $second_name = $_GET['second_name'];
    $phone       = $_GET['phone'];
    $plan_name   = $_GET['plan_name'];
    $user_id     = $_GET['user_id'];

    update_user_meta($user_id, 'first_name', $first_name);
    update_user_meta($user_id, 'last_name', $second_name);
    update_user_meta($user_id, 'phone', $phone);
    update_user_meta($user_id, 'status', 'not aproved');
    update_user_meta($user_id, 'plan', $plan_name);
  ob_clean();
  wp_send_json(array(success => true));
  wp_die();
}

add_action( 'wp_ajax_nopriv_redirect_to_reg', 'redirect_to_reg_callback' );//hook on ajax responce for not authorized users

function redirect_to_reg_callback()//ajax request handler
{
  $_SESSION['session_plan'] = $_GET['session_plan'];
  //file_put_contents(PRICE_PLUGIN_DIR."/test.txt",print_r($_GET['session_plan'], true), FILE_APPEND);
  ob_clean();
  wp_send_json(array(success => true));
  wp_die();
}

add_action( 'user_register', 'add_plan_after_registration', 10, 1 );

function add_plan_after_registration( $user_id ) {

    if ( isset( $_SESSION['session_plan'] ) )
    {
      update_user_meta($user_id, 'status', 'not aproved');
      update_user_meta($user_id, 'plan', $_SESSION['session_plan']);
    }
}

add_shortcode ('plans', 'short_code_plans');

function short_code_plans()
{
  echo <<<HTML
  <style>
  .myPanel
  {
    text-align: center;
    color: #fff;
    margin-right: 0;
    display: flex;
    justify-content: center;
    flex-direction: column;
  }
  </style>
HTML;

  $col_per_row = get_option('col_per_row');
  $col['2'] = "col-md-6";
  $col['3'] = "col-md-4";
  $col['4'] = "col-md-3";
  $col['6'] = "col-md-2";
  $url = admin_url('admin-ajax.php');//url for ajax request in shortcode
  $current_id = get_current_user_id();
  $current_id_first_name = get_user_meta( $current_id, 'first_name', true ); //for subscribe win
  $current_id_second_name = get_user_meta( $current_id, 'last_name', true ); 
  $current_id_phone = get_user_meta( $current_id, 'phone', true ); 
  $is_authorized_flag = is_user_logged_in();
  $is_authorized_flag = ($is_authorized_flag == true? 'true':'false');
  $panels_functions = get_option('panels_functions');
  $redirect_to_reg_url = get_site_url();
  $redirect_to_reg_url = "$redirect_to_reg_url/wp-login.php?action=register";

  global $wpdb;
  $myrows = $wpdb->get_results( "SELECT * FROM wp_plans WHERE visibility = 'yes'" );
  echo "
  <div id = \"panelContainer\">";
  foreach ($myrows as $plan) 
  {
    echo "
    <!--all panels-->
      <div class=\"$col[$col_per_row]\" style=\"padding: 8px; \">
        <!--own panel-->
    <div class = \"myPanel\" data-plan_name=\"$plan->name\" data-color=\"$plan->color\" style=\"box-shadow: 0 2px 5px 0 rgba(0,0,0,.2);\"> 
      <!--panel's header-->
          <div style=\"background: $plan->color; height: 130px; border-bottom: 7px $plan->color outset\">
            <!--text box-->
              <div style=\"padding: 2px\">
                <h2>$plan->name</h2>
                <b>$$plan->price</b>
        </div>
      </div>
      <!--panel's description-->
        <div style = \"height: 200px; color: #000000; border-right: 1px solid $plan->color; border-left: 1px solid $plan->color; border-bottom: 7px $plan->color outset; background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAIklEQVQIW2NkQAIfP378zwjjgzj8/PyMYAEYB8RmROaABAAVMg/XkcvroQAAAABJRU5ErkJggg== ) repeat\">
          <!--text box-->
          <div style=\"padding: 4px\">
              $plan->description
            </div>
        </div>
    </div>
      </div>";      
  }
  echo <<<HTML
</div>
<!-- Modal windows-->
<div class="container">
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
      <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header" id= "myDialog" style="padding:35px 50px;">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h3 style = "text-align: center; color: white">Subscribe to a plan</h3>
                </div>
                <div class="modal-body" style="padding:40px 50px;">
                    <form role="form">
                      <input class="form-control" id = "first_name" placeholder="First name"><br>
                        <input class="form-control" id = "second_name" placeholder="Second name"><br>
                        <input class="form-control" id = "phone" placeholder="Phone"><br>
                        <button type="submit" class="btn btn-success btn-block">Subscribe</button>
                        <input type="hidden" id = "plan_id">
                    </form>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
  </div>

  <div class="modal fade" tabindex="-1" role="dialog" id = infoModal>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p style = "text-align: center">You subscribe on plan</p>
      </div>
      <div class="modal-footer">
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <script> jQuery(document).ready( function (){
      panels_functions = "$panels_functions";
      redirect_to_reg_url = "$redirect_to_reg_url";
      if(panels_functions == 'on')
      {
         jQuery('#panelContainer').on('click', '.myPanel', function(){//handler click on panel 
          is_authorized_flag = $is_authorized_flag;
          plan_name = jQuery(this).data('plan_name');//get plan name from clicked panel
          if (is_authorized_flag)
          {
            jQuery("#myModal").modal();//open a modal window
            console.log(is_authorized_flag);
            jQuery("#first_name").val("$current_id_first_name");
            jQuery("#second_name").val("$current_id_second_name");
            jQuery("#phone").val("$current_id_phone");
            jQuery('#myDialog').css('backgroundColor', jQuery(this).data('color'));//change bgcolor for header modal win
          }
          else
          {
            jQuery.ajax({
              url: "$url",
              method: "GET",
              data: { action: "redirect_to_reg", session_plan: plan_name},
              dataType: "json"
            });
            window.location = redirect_to_reg_url;
          }
    });
        jQuery("#myModal form").submit(function(event)//pressing event type Submit
        {
          event.preventDefault();//disable the default behavior
          jQuery.ajax({//jquery ajax request handler that sends the form data, the method, the data itself, the behavior is working properly and error, expected type of response from the server
              url: "$url",
              method: "GET",
              data: { action: "subscribe_to_plan", first_name: jQuery("#first_name").val(), second_name: jQuery("#second_name").val(), phone: jQuery("#phone").val(), plan_name: plan_name, user_id: "$current_id" },
              success:function(response)//response server
              {
                  if (response.success)
                  {
                      console.log("data Saved");
                      jQuery('#myModal').modal('hide');//Hide form for adding
                      jQuery("#first_name").val("");//fields cleansing
                      jQuery("#second_name").val("");
                      jQuery("#phone").val("");
                      jQuery("#infoModal").modal();//open success info win 
                  }
                  else
                  {
                      console.error("Validation Error");
                  }
              },
              error:function()
              {
                  console.error("Server Error");
                  console.log("data Saved");
                  jQuery('#myModal').modal('hide');//Hide form for adding
                  jQuery("#first_name").val("");//fields cleansing
                  jQuery("#second_name").val("");
                  jQuery("#phone").val("");
                  jQuery("#infoModal").modal();//open success info win 
              },
              dataType: "json"//the expected type of response from the server
          });
    });
  }
});
    </script>

HTML;
}

add_action('admin_head', 'kjl_custom_admin_css'); //the size of the columns in the users
function kjl_custom_admin_css() 
{
  echo '<style>
  .column-plan {width: 10%}
  .column-status {width: 10%}
  </style>';
}

add_filter('manage_users_columns', 'add_custom_columns');//adding columns in the users

function add_custom_columns($columns) 
{
    $columns['status'] = 'Status';
    $columns['plan'] = 'Plan';
    return $columns;
}
 
add_action('manage_users_custom_column',  'show_custom_columns_content', 10, 3);

function show_custom_columns_content($value, $column_name, $user_id) 
{
    if ($column_name == 'status') {
      $return_row = true;//у юзера может быть несколько строк по одному ключу и соответственно функция по умолчанию возвращает array
      $status = get_user_meta($user_id, 'status', $return_row);
        $value = $status;
    } elseif ($column_name == 'plan') {
        $return_row = true;
        $plan = get_user_meta($user_id, 'plan', $return_row);
        $value = $plan;
    }
    return $value;
}
//add phone numbers in the user's profile
add_filter('user_contactmethods', 'custom_user_contactmethods');
 
function custom_user_contactmethods($contactmethods){ 
  $contactmethods['phone'] = 'Phone';
  return $contactmethods;
}
//creating plans of the table in the database

global $jal_db_version;

function plan_install () 
{
   global $wpdb;

   $table_name = $wpdb->prefix . "plans";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
   {
        $sql = "CREATE TABLE " . $table_name . " (
      id          int NOT NULL AUTO_INCREMENT,
      name        varchar(255) NOT NULL,
      price       int NOT NULL,
      description text NOT NULL,
      color       VARCHAR(10),
      visibility  CHAR(15),
      UNIQUE KEY id (id)
      );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
   }
   add_option("col_per_row", '4');
   add_option( 'panels_functions', 'on' );
}

register_activation_hook(PRICE_PLUGIN_DIR.'pd_subscriptions_plans.php','plan_install');