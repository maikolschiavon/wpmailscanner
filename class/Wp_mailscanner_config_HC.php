<?php

class Wp_mailscanner_config_HC{
    
    static $table_name_host_categories = "wp_mailscanner_host_categories";

    static function get_config($host = ""){
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_host_categories;

        $sql = "SELECT * FROM $table_name";

        if(!empty($host)){
            $sql .= " WHERE host = '$host' ";
        }

        $rows = $wpdb->get_results($sql);

        return $rows;
    }

    static function wp_mailscanner_create_ajax(){
       if(isset($_POST["row"]) && !empty($_POST["row"])){
            $host = sanitize_text_field($_POST["row"]["host_category"]);
            $categories = $_POST["row"]["post_host_categories"];
            $thumbnail_id  = intval($_POST["row"]["mailscanner_image_id"]);

            Wp_mailscanner_config_HC::create_host_categories($host,$categories,$thumbnail_id);
       }
    }

    static function wp_mailscanner_delete_ajax(){
        global $wpdb;
        
        $host = sanitize_text_field($_POST["host"]);

        $table_name = $wpdb->prefix . self::$table_name_host_categories;

        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE host = %s", $host));
    }

    static function create_host_categories($host,$categories,$thumbnail_id){
        global $wpdb;
        
        $exist = Wp_mailscanner_config_HC::exist_host_categories($host);

        if(empty($exist)){
            $table_name = $wpdb->prefix . self::$table_name_host_categories;

            $wpdb->insert($table_name, array(
                'host' => $host,
                'categories' => implode(" ## ",$categories),
                'thumbnail_id' => $thumbnail_id,
            ));

        }
        else{
            $id = $exist[0]->id;
            
            Wp_mailscanner_config_HC::update_host_categories($id,$host,$categories,$thumbnail_id);
        }

    }

    static function update_host_categories($id,$host,$categories,$thumbnail_id){
        global $wpdb;

        $categories = implode(" ## ",$categories);

        $table_name = $wpdb->prefix . self::$table_name_host_categories;
        
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET host = '$host', categories = '$categories', thumbnail_id = '$thumbnail_id' WHERE id = %s", $id));
    }

    static function exist_host_categories($host){
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_host_categories;

        $sql = "SELECT * FROM $table_name WHERE host = '$host' LIMIT 1";        
        $rows = $wpdb->get_results($sql);
        
        return $rows;
    }

    static function get_all_host_categories(){
        $hosts = array();

        $config = Wp_mailscanner_config_HC::get_config();

        foreach($config as $values){
            $hosts[] = $values->host;
        }

        return $hosts;
    }
}


