<?php

class Wp_mailscanner_Email_reader {

    // imap server connection
	public $conn;

	// inbox storage and inbox message count
	public $inbox;
	public $msg_cnt;

    // folder name save attachments
    public $folder_attachments = "attachments";

    // folder name move email after processed
    public $folder_email_processed = "";

    // get body mail
    public $body_email = 1;

    // remove html by body
    public $remove_html_body = 1;

	// connect to the server and get the inbox emails
	function __construct() {
//		$this->connect();
//		$this->inbox();
	}

	// close the server connection
	function close() {
		$this->inbox = array();
		$this->msg_cnt = 0;

		imap_close($this->conn);
	}

	// open the server connection
	// the imap_open function parameters will need to be changed for the particular server
	// these are laid out to connect to a Dreamhost IMAP server
	function connect($server, $port, $folder, $user, $pass) {
		$this->conn = imap_open('{'.$server.':'.$port.'/ssl}'.$folder, $user, $pass);
        //$this->conn = imap_open("{imap.googlemail.com:993/ssl}INBOX", $this->user, $this->pass);
	}

	// move the message to a new folder
	function move($msg_index, $folder='INBOX.Processed') {
		// move on server
		imap_mail_move($this->conn, $msg_index, $folder);
		imap_expunge($this->conn);

		// re-read the inbox
		//$this->inbox();
	}
    
    /*
	// get a specific message (1 = first email, 2 = second email, etc.)
	function get($msg_index=NULL) {
		if (count($this->inbox) <= 0) {
			return array();
		}
		elseif ( ! is_null($msg_index) && isset($this->inbox[$msg_index])) {
			return $this->inbox[$msg_index];
		}

		return $this->inbox[0];
	}

	// read the inbox
	function inbox() {
		$this->msg_cnt = imap_num_msg($this->conn);

		$in = array();
		for($i = 1; $i <= $this->msg_cnt; $i++) {
			$in[] = array(
				'index'     => $i,
				'header'    => imap_headerinfo($this->conn, $i),
				'body'      => imap_body($this->conn, $i),
				'structure' => imap_fetchstructure($this->conn, $i)
			);
		}

		$this->inbox = $in;
	}*/

    function get_emails(){
        $emails = imap_search($this->conn, 'UNSEEN');

        return $emails;
    }

    function get_header_email($email_number){
        $header = array();

        if(!empty($email_number)){
            $header = imap_headerinfo($this->conn, $email_number);
        }

        return $header;
    }

    function get_content_emails($emails){
        $body = "";
        $content_emails = array();

        if(!empty($emails)) {
            
            /* put the newest emails on top */
            rsort($emails);

            /* for every email... */
            foreach($emails as $email_number){
                
                $header = $this->get_header_email($email_number);

                $content_emails[$email_number]["MailDate"] = $header->MailDate;

                $content_emails[$email_number]["Subject"] = $header->Subject;

                $overview = imap_fetch_overview($this->conn,$email_number,0);

                $obj_from = $header->from;
                $from_host = $obj_from[0]->host;
                
                $content_emails[$email_number]["from_host"] = $from_host;

                if($this->body_email == 1){
                    $body = imap_fetchbody($this->conn,$email_number,2);

                    if($this->remove_html_body == 1){
                        $body = strip_tags($body);
                    }
                }
                $content_emails[$email_number]["Body"] = $body;
               
                $attachments = $this->get_attachments($email_number);
                $content_emails[$email_number]["attachments"] = $this->attachments_download($attachments);
                
                /*
                if(!empty($this->folder_email_processed)){
                    $this->move($email_number, $this->folder_email_processed);
                }
                */
            }
        }

        return $content_emails;
    }

    function get_attachments($email_number){
        
        $attachments = array();

        if(!empty($email_number)) {

            /* get mail structure */
            $structure = imap_fetchstructure($this->conn, $email_number);

            $attachments = array();

            /* if any attachments found... */
            if(isset($structure->parts) && count($structure->parts)){
                for($i = 0; $i < count($structure->parts); $i++){
                    $attachments[$i] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => '',
                        'email_number' => $email_number,
                    );

                    if($structure->parts[$i]->ifdparameters){
                        foreach($structure->parts[$i]->dparameters as $object) {

                            if(strtolower($object->attribute) == 'filename') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['filename'] = $object->value;
                            }
                        }
                    }

                    if($structure->parts[$i]->ifparameters) {
                        foreach($structure->parts[$i]->parameters as $object) {

                            if(strtolower($object->attribute) == 'name') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['name'] = $object->value;
                            }
                        }
                    }

                    if($attachments[$i]['is_attachment']) {
                        $attachments[$i]['attachment'] = imap_fetchbody($this->conn, $email_number, $i+1);
                        
                        /* 3 = BASE64 encoding */
                        if($structure->parts[$i]->encoding == 3) { 
                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        }
                        /* 4 = QUOTED-PRINTABLE encoding */
                        elseif($structure->parts[$i]->encoding == 4) {
                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                    }
                }
            }
        }

        return $attachments;
    }

    function attachments_download($attachments){
        chdir(WP_MAILSCANNER_ROOTDIR);

        $attachments_downloaded = array();

        foreach($attachments as $attachment){
            if($attachment['is_attachment'] == 1){
                $email_number = $attachment['email_number'];
                $filename = $attachment['name'];

                if(empty($filename)) $filename = $attachment['filename'];

                if(empty($filename)) $filename = time() . ".dat";

                if(!is_dir($this->folder_attachments)){
                    mkdir($this->folder_attachments);
                }
                
                $filaname_downloaded = $email_number . "-" . $filename;

                $fp = fopen("./". $this->folder_attachments ."/". $filaname_downloaded, "w+");
                fwrite($fp, $attachment['attachment']);
                fclose($fp);

                if(file_exists($this->folder_attachments ."/". $filaname_downloaded)){
                    $attachments_downloaded[] = $filaname_downloaded;
                }
            }
        }

        return $attachments_downloaded;
    }
}