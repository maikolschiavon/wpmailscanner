<?php

class Wp_mailscanner_config{

    function get_config(){
        global $wpdb;

        $table_name_config = $wpdb->prefix . "wp_mailscanner_config";

        $sql = "SELECT * FROM $table_name_config LIMIT 1";
        $rows = $wpdb->get_results($sql);

        return $rows;
    }

    function save_config(){
        global $wpdb;
        
     //   echo "<pre>";print_r($_REQUEST);die;
    
        $table_name = $wpdb->prefix . "wp_mailscanner_config";
    
        $config = Wp_mailscanner_config::get_config();
    
        $hostname = sanitize_text_field($_REQUEST["hostname"]);
        $port = intval($_REQUEST["port"]);
        $username = sanitize_text_field($_REQUEST["username"]);
        $password = sanitize_text_field($_REQUEST["password"]);
        $folder_read = sanitize_text_field($_REQUEST["folder_read"]);
        $folder_processed = sanitize_text_field($_REQUEST["folder_processed"]);
        $post_status = sanitize_text_field($_REQUEST["post_status"]);
            
        if(isset($_REQUEST["post_categories"])){
            $post_categories = implode(" ## ",$_REQUEST["post_categories"]);
        }
        
        $download_att = 0;
        if(isset($_REQUEST["download_att"]) && $_REQUEST["download_att"] == "on"){
            $download_att = 1;
        }
    
        $body_email = 0;
        if(isset($_REQUEST["body_email"]) && $_REQUEST["body_email"] == "on"){
            $body_email = 1;
        }    
    
        $body_html = 0;
        if(isset($_REQUEST["body_html"]) && $_REQUEST["body_html"] == "on"){
            $body_html = 1;
        }

        $process_all_email = 0;
        if(isset($_REQUEST["process_all_email"]) && $_REQUEST["process_all_email"] == "on"){
            $process_all_email = 1;
        }
    
        if(!empty($config)){
            $id = $config[0]->id;
    
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET hostname = '$hostname', port = '$port', username = '$username', password = '$password', folder_read = '$folder_read', folder_processed = '$folder_processed', download_att = '$download_att', body_email = '$body_email', body_html = '$body_html', post_categories = '$post_categories', post_status = '$post_status', process_all_email = '$process_all_email' WHERE id = %s", $id));
    
        }
        else{
            
            $wpdb->insert($table_name, array(
                'hostname' => $hostname,
                'port' => $port,
                'password' => $password,
                'folder_read' => $folder_read,
                'folder_processed' => $folder_processed,
                'download_att' => $download_att,
                'body_email' => $body_email,
                'body_html' => $body_html,
                'post_categories' => $post_categories,
                'post_status' => $post_status,
                'process_all_email' => $process_all_email,
            ));
        }
    
        wp_redirect( $_SERVER["HTTP_REFERER"], 302, 'WordPress' );
        exit;
    }
}