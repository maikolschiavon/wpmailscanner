<button type="button" class="button" onclick="wp_maiscanner_add_host_categories();">Add Host Categories</button>
<table id="tbl_host_categories" class="widefat fixed" cellspacing="0">
    <thead>
        <tr>
            <th id="column-host" class="manage-column column-host" scope="col"> Host </th> 
            <th id="column-categories" class="manage-column column-categories" scope="col"> Category </th> 
            <th id="column-featured-image" class="manage-column column-featured-image" scope="col"> Featured image </th>
            <th id="column-actions" class="manage-column column-actions" scope="col"> Actions </th>       
        </tr>
    </thead>

    <tfoot>
        <tr>
            <th class="manage-column column-host" scope="col"> Host </th>
            <th class="manage-column column-categories" scope="col"> Category </th>
            <th class="manage-column column-featured-image" scope="col"> Featured image </th>
            <th class="manage-column column-actions" scope="col"> Actions </th>
        </tr>
    </tfoot>

    <tbody>
        <tr id="tr_0" class="alternate">
            
            <td class="column-host">
                <input type="text" name="host_category" id="host_category0" value="<?php echo $host_first?>" class="regular-text ltr" <?php if($host_first) {?> readonly <?php } ?> >
            </td>

            <td class="column-categories">
                <select name="post_host_categories[]" id="post_host_categories0" multiple>
                    <?php foreach($wp_post_categories as $category) { ?>
                        <option value="<?php echo $category->term_id; ?>" <?php if( empty($post_categories) && $category->term_id == 1 || in_array($category->term_id, $post_categories_first)){ ?> selected <?php } ?> > <?php echo $category->name; ?> </option>
                    <?php } ?>
                </select>   
            </td>

            <td class="column-featured-image">
                <input type="hidden" class="regular-text ltr" name="mailscanner_image_id" id="mailscanner_image_id0" value="<?php echo esc_attr( $thumbnail_id_first ); ?>" class="regular-text" />
                <img id="mailscanner-preview-image0" name="mailscanner-preview-image" class="mailscanner-preview-image" src="  <?php if( !empty($thumbnail_id_first) ) { echo wp_get_attachment_url( $thumbnail_id_first ); } ?>" />
                <input type='button' class="button-primary mailscanner_media_manager" name="mailscanner_media_manager" n_host_categories="0" value="<?php esc_attr_e( 'Select a image' ); ?>" />
            </td>

            <td class="column-actions">
                <span class="save_host_categories" n_host_categories="0">Save</span>
                <span> | </span>
                <span class="delete_host_categories" n_host_categories="0">Delete</span>
            </td>
            
        </tr>
        
        <?php
            foreach($config_hc as $i => $config_hc_values){                
                if($i > 0) {
        ?>
            <tr id="tr_<?php echo $i; ?>" class="<?php if( $i % 2 != 0){ ?>alternate <?php } ?>">
            
            <td class="column-host">
                <input type="text" name="host_category" id="host_category<?php echo $i; ?>" value="<?php echo $config_hc_values->host; ?>" class="regular-text ltr" readonly>
            </td>

            <td class="column-categories">
                <select name="post_host_categories[]" id="post_host_categories<?php echo $i; ?>" multiple>
                    <?php foreach($wp_post_categories as $category) { ?>
                        <option value="<?php echo $category->term_id; ?>" <?php if( empty($post_categories) && $category->term_id == 1 || in_array($category->term_id, explode(" ## ", $config_hc_values->categories) )){ ?> selected <?php } ?> > <?php echo $category->name; ?> </option>
                    <?php } ?>
                </select>   
            </td>
            
            <td class="column-featured-image">
                <input type="hidden" class="regular-text ltr" name="mailscanner_image_id" id="mailscanner_image_id<?php echo $i; ?>" value="<?php echo esc_attr( $config_hc_values->thumbnail_id ); ?>" class="regular-text" />
                <img id="mailscanner-preview-image<?php echo $i; ?>" name="mailscanner-preview-image" class="mailscanner-preview-image" src=" <?php if( !empty($config_hc_values->thumbnail_id) ) { echo wp_get_attachment_url( $config_hc_values->thumbnail_id ); } ?>" />
                <input type='button' class="button-primary mailscanner_media_manager" name="mailscanner_media_manager" n_host_categories="<?php echo $i; ?>" value="<?php esc_attr_e( 'Select a image' ); ?>" />
            </td>

            <td class="column-actions">
                <span class="save_host_categories" n_host_categories="<?php echo $i; ?>">Save</span>
                <span> | </span>
                <span class="delete_host_categories" n_host_categories="<?php echo $i; ?>">Delete</span>
            </td>
            
        </tr>
        <?php
            }
        }
        ?>

    </tbody>
</table>