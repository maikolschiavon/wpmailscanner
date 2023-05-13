<?php

chdir(WP_MAILSCANNER_ROOTDIR);

add_action( 'wp_mailscanner_read_email_cron', 'wp_mailscanner_read_mail' );

function wp_mailscanner_read_mail(){
    $x = 0;
    $posts = array();
    
    $tool_config = new Wp_mailscanner_config;
    $config = $tool_config->get_config();

    if(!empty($config)){

        foreach($config as $config_values){
            $server = $config_values->hostname;
            $port = $config_values->port;
            $folder_read = $config_values->folder_read;
            $folder_processed = $config_values->folder_processed;
            $user = $config_values->username;
            $pass = $config_values->password;
            $body_email = $config_values->body_email;
            $body_html = $config_values->body_html;
            $process_all_email = $config_values->process_all_email;
            
            $tool_email = new Wp_mailscanner_Email_reader;
            $tool_email->folder_email_processed = $folder_processed;
            $tool_email->body_email = $body_email;
            $tool_email->remove_html_body = $body_html;

            $tool_email->connect($server, $port, $folder_read, $user, $pass);

            $emails = $tool_email->get_emails();
                        
            $content_emails = $tool_email->get_content_emails($emails);

            if(!$process_all_email){
                $tool_hc = new Wp_mailscanner_config_HC;
                $hosts = $tool_hc->get_all_host_categories();
            }

            if(!empty($content_emails)){
                foreach($content_emails as $email_number => $values){

                    if(!empty($values["Subject"])){

                        $title = $values["Subject"];
                        $body = $values["Body"];
                        $attachments = $values["attachments"];
                        $from_host = $values["from_host"];
                        $MailDate = $values["MailDate"];

                        $create_post = true;

                        if(!$process_all_email){
                            if(!in_array($from_host, $hosts)){
                                $create_post = false;
                            }
                        }
                        
                        if($create_post){
                            $data_post = array("title"=>$title, "body"=>$body, "MailDate"=>$MailDate);

                            $tool_post = new Wp_mailscanner_post;
                            $post_id = $tool_post->insert_post($data_post,$from_host);

                            if($post_id > 0){

                                // Sposto la mail per indicare che è stata processata correttamente
                                $tool_email->move($email_number, $folder_processed);

                                $posts[$x]["id"] = $post_id;
                                $posts[$x]["email_number"] = $email_number;

                                $y = 0;
                                foreach($attachments as $file_name){

                                    $attachment = $tool_post->insert_attachment($post_id, $tool_email->folder_attachments, $file_name);

                                    if($attachment["id"] > 0){
                                        $file_url = $attachment["url"];

                                        $tool_post->update_post($post_id,$file_url,$file_name);

                                        $posts[$x]["attachments"][$y]["id"] = $attachment["id"];
                                        $posts[$x]["attachments"][$y]["url"] = $file_url;

                                        $y++;
                                    }
                                }

                                $x++;
                            }
                        }
                    }
                }
            }            
        }
    }

    return $posts;
}
