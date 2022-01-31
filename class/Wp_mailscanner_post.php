<?php

chdir(WP_MAILSCANNER_ROOTDIR);

require_once( ABSPATH . '/wp-includes/pluggable.php' ); 

class Wp_mailscanner_post{

    function insert_post($data_post,$from_host){
        $post_id = $thumbnail_id = 0;
        $post_status = "draft";
        $categories = array();

        $title = $data_post["title"];
        $body = $data_post["body"];
        $MailDate = $data_post["MailDate"];

        if(!empty($title)){
            wp_set_current_user(1);
            
            $tool_config = new Wp_mailscanner_config;
            $config = $tool_config->get_config();
            
            if(!empty($config)){
                $post_categories = $config[0]->post_categories;

                $categories = explode(" ## ",$post_categories);

                $post_status = $config[0]->post_status;
            }

            $tool_hc = new Wp_mailscanner_config_HC;
            $config_hc = $tool_hc->get_config($from_host);
            if(!empty($config_hc)){
                $post_categories = $config_hc[0]->categories;

                $categories = explode(" ## ",$post_categories);

                $thumbnail_id = $config_hc[0]->thumbnail_id;
            }

            $post_id = wp_insert_post( array(
                'post_title' => $title,          
                'post_status' => $post_status,
                'post_type' => "post",
                'comment_status' => 'closed',
                'post_category' => $categories,
                'post_content' => $body,
                'post_date' => date("Y-m-d H:i:s", strtotime($MailDate))
            ) );

            set_post_thumbnail( $post_id, $thumbnail_id);
        }

        return $post_id;
    }

    function insert_attachment($post_id, $file_path, $file_name){
        $url = "";
        $attachment_id = 0;
        
        $file = file_get_contents($file_path."/".$file_name);

        $upload_file = wp_upload_bits($file_name, null, $file);
        if (!$upload_file['error']) {
            $url = $upload_file["url"];

            $wp_filetype = wp_check_filetype($file_name, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_parent' => $post_id,
                'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $post_id );

            if (!is_wp_error($attachment_id)) {
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );

                wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            }
        }

        unlink($file_path."/".$file_name);

        return array("id" => $attachment_id, "url"=> $url);
    }

    function generate_block_file($file_url,$file_name){
        $block_file = "";

        if(!empty($file_url) && !empty($file_name)){
            $block_file = '<div class="wp-block-file">
                <a href="'.$file_url.'">'.$file_name.'</a>
                <a href="'.$file_url.'" class="wp-block-file__button" download>Download</a>
            </div>';
        }

        return $block_file;
    }

    function update_post($post_id, $file_url, $file_name){
        
        $post_content = $this->generate_block_file($file_url,$file_name);
        
        if(!empty($post_content)){
            wp_set_current_user(1);

            $post = array();
            $post['ID'] = $post_id;
            $post['post_content'] = $post_content;
            wp_update_post( $post );

            update_post_meta( $post_id, "wp_mailscanner_attach", $file_url); 
        }
    }

}