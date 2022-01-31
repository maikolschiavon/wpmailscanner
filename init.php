<?php
/*
Plugin Name: Mailscanner WP
Description: enable cron to read every hour your emails and create one post with title equal subject to email and content equal body message with all attachments to email
Version: 1
Author: Maikol Schiavon
Author URI: #
*/

//ini_set('display_errors',1);
//error_reporting(E_ALL);

define('WP_MAILSCANNER_ROOTDIR', plugin_dir_path(__FILE__));

require_once(WP_MAILSCANNER_ROOTDIR . 'class/Wp_mailscanner_Email_reader.php');
require_once(WP_MAILSCANNER_ROOTDIR . 'class/Wp_mailscanner_post.php');
require_once(WP_MAILSCANNER_ROOTDIR . 'class/Wp_mailscanner_config_HC.php');
require_once(WP_MAILSCANNER_ROOTDIR . 'class/Wp_mailscanner_config.php');

require_once(WP_MAILSCANNER_ROOTDIR . 'cron.php');

function wp_mailscanner_install() {
    global $wpdb;

    $table_name_config = $wpdb->prefix . "wp_mailscanner_config";
    $table_name_config_hc = $wpdb->prefix . "wp_mailscanner_host_categories";

    $sql_config = "CREATE TABLE {$table_name_config} (
        id int(11) unsigned NOT NULL AUTO_INCREMENT,
        hostname varchar(100) DEFAULT NULL,
        port int(10) DEFAULT NULL,
        username varchar(100) DEFAULT NULL,
        password varchar(100) DEFAULT NULL,
        folder_read varchar(100) DEFAULT NULL,
        folder_processed varchar(100) DEFAULT NULL,
        download_att int(1) DEFAULT NULL,
        body_email int(1) DEFAULT NULL,
        body_html int(1) DEFAULT NULL,
        post_categories varchar(100) DEFAULT NULL,    
        post_status varchar(100) DEFAULT NULL,    
        process_all_email int(1) DEFAULT NULL,     
        PRIMARY KEY (id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta($sql_config);

    $sql_config_hc = "CREATE TABLE {$table_name_config_hc} (
        id int(11) unsigned NOT NULL AUTO_INCREMENT,
        host varchar(255) NOT NULL,
        categories varchar(255) DEFAULT NULL,
        thumbnail_id int(11) DEFAULT NULL,
        PRIMARY KEY (id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    dbDelta($sql_config_hc);

    if ( ! wp_next_scheduled( 'wp_mailscanner_read_email_cron' ) ) {
        wp_schedule_event( time(), 'hourly', 'wp_mailscanner_read_email_cron' ); // plugin_cron_refresh_cache is a hook
   }
}


// run the install scripts upon plugin activation
register_activation_hook(__FILE__, 'wp_mailscanner_install');

//menu items
add_action('admin_menu','wp_mailscanner_modifymenu');
function wp_mailscanner_modifymenu() {

    //this is the main item for the menu
	add_menu_page('WP Mail Scanner', //page title
	'WP Mail Scanner', //menu title
	'manage_options', //capabilities
	'wp_mailscanner_panel_config', //menu slug
	'wp_mailscanner_panel_config', //function
    'dashicons-email' // icon_url
	);
}

function wp_mailscanner_panel_config() {
    $hostname = $username = $password = $folder_read = $folder_processed = "";
    $download_att = $body_email = $thumbnail_id = 0;
    $post_status = "draft";
    $post_categories = array();
    
    if(!current_user_can('manage_options')){
        wp_die(_("You do not have sufficient permissions to access thi page. "));
    }

    $tool_config = new Wp_mailscanner_config;
    $config = $tool_config->get_config();
    
    if(!empty($config)){
        $hostname = $config[0]->hostname;
        $username = $config[0]->username;
        $password = $config[0]->password;
        $port = $config[0]->port;
        $folder_read = $config[0]->folder_read;
        $folder_processed = $config[0]->folder_processed;
        $download_att = $config[0]->download_att;
        $post_categories = explode(" ## ", $config[0]->post_categories);
        $body_email = $config[0]->body_email;
        $body_html = $config[0]->body_html;
        $post_status = $config[0]->post_status;
        $process_all_email = $config[0]->process_all_email;
    }

    $wp_post_categories = get_categories( array( 'hide_empty' => false) );

    $wp_post_statuses = get_post_statuses();

    $hc = new Wp_mailscanner_config_HC;
    $config_hc = $hc->get_config();

    $host_first = "";
    $post_categories_first = array();
    if(!empty($config_hc)){
        $host_first = $config_hc[0]->host;
        $post_categories_first = explode(" ## ", $config_hc[0]->categories);
        $thumbnail_id_first = $config_hc[0]->thumbnail_id;
    }

    include( WP_MAILSCANNER_ROOTDIR . "templates/panel_config.php");    
}

add_action('wp_ajax_sumbit_mailscanner_config', 'wp_mailscanner_sumbit_config');

function wp_mailscanner_sumbit_config() {
    $tool = new Wp_mailscanner_config;

    if ( empty($_POST) || !wp_verify_nonce($_POST['security-code-here'],'add_transfer') ) {
        echo 'You targeted the right function, but sorry, your nonce did not verify.';
        wp_die();
    } else {
        // do your function here 
        wp_redirect($tool->save_config());
    }
}

add_action('wp_ajax_force_mailscanner_exe', 'wp_mailscanner_force_exe');

function wp_mailscanner_force_exe() {
    $result = wp_mailscanner_read_mail();

    echo json_encode($result);
    wp_die();
}

wp_enqueue_style( 'wp-mailscanner', '/wp-content/plugins/WP-Mailscanner/css/wp-mailscanner.css',false,'1.0','all');
wp_enqueue_script( 'wp-mailscanner-js', '/wp-content/plugins/WP-Mailscanner/js/wp-mailscanner.js', array(), true );
wp_enqueue_script( 'wp-mailscanner-upload-js', '/wp-content/plugins/WP-Mailscanner/js/wp-mailscanner-upload.js', array(), true );

add_action( 'rest_api_init', 'add_wp_mailscanner_attach' );

function add_wp_mailscanner_attach() {
    register_rest_field(
    'post', 
    'wp_mailscanner_attach', //New Field Name in JSON RESPONSEs
    array(
        'get_callback'    => 'get_wp_mailscanner_attach_url', // custom function name 
        'update_callback' => null,
        'schema'          => null,
        )
    );
}

function get_wp_mailscanner_attach_url( $object, $field_name, $request ) {
    $fieldvalue = "";

    $post_id = $object["id"];

    $post_meta = get_post_meta($post_id);

    if(!empty($post_meta) && isset($post_meta["wp_mailscanner_attach"])){
        
        $fieldvalue = $post_meta["wp_mailscanner_attach"][0];
    }

    return $fieldvalue;
}

// AJAX

add_action( 'wp_ajax_wp_mailscanner_hc_create', array('Wp_mailscanner_config_HC','wp_mailscanner_create_ajax'));
add_action( 'wp_ajax_wp_mailscanner_hc_delete', array('Wp_mailscanner_config_HC','wp_mailscanner_delete_ajax'));

add_action( 'admin_enqueue_scripts', 'load_wp_media_files' );
function load_wp_media_files( $page ) {
    wp_enqueue_media();  
}

add_action( 'wp_ajax_mailscanner_get_image', 'mailscanner_get_image'   );
function mailscanner_get_image() {
    if(isset($_GET['id']) ){
        $image_url = wp_get_attachment_url( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ) );        
        $data = array(
            'image'    => $image_url,
        );
        wp_send_json_success( $data );
    } else {
        wp_send_json_error();
    }
}