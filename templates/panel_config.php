<h2> WP Mailscanner </h2>

<p> enable cron to read every hour your emails and create one post with title equal subject to email and content equal body message with all attachments to email </p>

<input type="button" name="submit" id="submit" class="button button-secondary" value="Read Now" style="float: left;" onclick="wp_maiscanner_read_now();">

<span class="spinner" style="float: left;"></span>

<br><br>

<pre id="log-read-now"></pre>

<form method="post" id="form-entity" action="<?php echo admin_url('admin-ajax.php'); ?>">
    <?php wp_nonce_field('add_transfer','security-code-here'); ?>

    <input name="action" value="sumbit_mailscanner_config" type="hidden">

    <table class="form-table" role="presentation">
        
        <tr>
            <th>
                <label for="hostname">Hostname <span class="description">(required)</span></label>
            </th>
            <td>
                <input type="text" name="hostname" id="hostname" value="<?php echo $hostname?>" class="regular-text ltr" required="">
            </td>
        </tr>
        <tr>
            <th>
                <label for="hostname">Port <span class="description">(required)</span></label>
            </th>
            <td>
                <input type="number" name="port" id="port" value="<?php echo $port?>" class="regular-text ltr" required="">
            </td>
        </tr>
        <tr>
            <th>
                <label for="username">Username <span class="description">(required)</span></label>
            </th>
            <td>
                <input type="text" name="username" id="username" value="<?php echo $username?>" class="regular-text ltr" required="">
            </td>
        </tr>
        <tr>
            <th>
                <label for="password">Password <span class="description">(required)</span></label>
            </th>
            <td>
                <input type="password" name="password" id="password" value="<?php echo $password?>" class="regular-text ltr" required="">
            </td>
        </tr>
        <tr>
            <th>
                <label for="folder">Folder name to read<span class="description">(required)</span></label>
            </th>
            <td>
                <input type="text" name="folder_read" id="folder_read" value="<?php echo $folder_read?>" class="regular-text ltr" required="">
            </td>
        </tr>
        <tr>
            <th>
                <label for="folder">Folder name Emails processed<span class="description">(required)</span></label>
            </th>
            <td>
                <input type="text" name="folder_processed" id="folder_processed" value="<?php echo $folder_processed?>" class="regular-text ltr" required="">
            </td>
        </tr>
        

        <tr>
            <th>
                <label for="download_att">Download Attachments</label>
            </th>
            <td>
                <input name="download_att" type="checkbox" id="download_att" <?php if($download_att == 1){ ?> checked <?php } ?>>
            </td>
        </tr>

        <tr>
            <th>
                <label for="body_email">Body Email</label>
            </th>
            <td>
                <input name="body_email" type="checkbox" id="body_email" <?php if($body_email == 1){ ?> checked <?php } ?>>
            </td>
        </tr>

        <tr>
            <th>
                <label for="body_html">Remove Tag HTML Body Email</label>
            </th>
            <td>
                <input name="body_html" type="checkbox" id="body_html" <?php if($body_html == 1){ ?> checked <?php } ?>>
            </td>
        </tr>

        <tr>
            <th>
                <label for="post_category">Post Status</label>
            </th>
            <td>
                <select name="post_status" id="post_status">
                    <?php foreach($wp_post_statuses as $status => $lbl_status) { ?>
                        <option value="<?php echo $status; ?>" <?php if( empty($post_status) && $status == "draft" || $status == $post_status ){ ?> selected <?php } ?> > <?php echo $lbl_status; ?> </option>
                    <?php } ?>
                </select>                
            </td>
        </tr>

        <tr>
            <th>
                <label for="post_category">Post Categories</label>
            </th>
            <td>
                <lable for="post_category_default">Dedault</label> <br>
                <select name="post_categories[]" id="post_categories" multiple>
                    <?php foreach($wp_post_categories as $category) { ?>
                        <option value="<?php echo $category->term_id; ?>" <?php if( empty($post_categories) && $category->term_id == 1 || in_array($category->term_id, $post_categories)){ ?> selected <?php } ?> > <?php echo $category->name; ?> </option>
                    <?php } ?>
                </select>                
            </td>
        </tr>

        <tr>
            <th>
                <label for="process_email_not_config">Process All Email</label>
                <p class="description">Process all email also without config Host</p>
            </th>
            <td>
                <input name="process_all_email" type="checkbox" id="process_all_email" <?php if($process_all_email == 1){ ?> checked <?php } ?>>
            </td>
        </tr>        
        
        <tr>      
            <td colspan="2">                
                <?php require_once("panel_config_host_categories.php"); ?>
            </td>
        </tr>
        
        <tr>
            <td>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Configuration" >
                
            </td>
        </tr>

    </table>
</form>