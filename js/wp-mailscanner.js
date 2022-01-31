function wp_maiscanner_read_now(){
    
    jQuery(".spinner").addClass("is-active");

    var data = {
        'action': 'force_mailscanner_exe',
    };

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data,        
        success:function(response) {
            jQuery(".spinner").removeClass("is-active");

            response = JSON.parse(response);

            response = JSON.stringify(response, null, 4);
            
            jQuery("#log-read-now").html(response);

        }
    });
}

function wp_maiscanner_add_host_categories(){

    var n_row = jQuery("#tbl_host_categories tbody tr").length + 1;

    var last_className = jQuery("#tbl_host_categories tbody tr:last")[0].className;

    var tr_id = "tr_" + n_row;
    var tr = document.createElement("tr"); 
    tr.id = tr_id;

    if(last_className == ""){
        tr.className = "alternate";
    }
    jQuery("#tbl_host_categories").append(tr);

    for(var i = 0; i < jQuery("#tbl_host_categories tbody tr:first td").length; i++){
        var column_id = jQuery("#tbl_host_categories tbody td")[i].className;
        var elements = jQuery("#tbl_host_categories tbody td")[i].children;
        
        for(var x = 0; x < elements.length; x++){
            
            var ele = elements[x];
            
            if(x == 0){
                var td_id = column_id + '-' + n_row;
                var td = document.createElement("td"); 
                td.id = td_id;
        
                jQuery("#tbl_host_categories #" + tr_id).append(td);
            }

            var className = ele.className;
            var id = ele.name + n_row;
            var name = ele.name;
            var type = ele.type;
            var text = ele.innerText

            var div = document.createElement(ele.localName);
            div.id = id;
            div.className = className;
            div.type = type;
            div.name = name;
            div.innerText = text;

            if(type == 'select-multiple'){
                div.multiple = true;
            }
        
            if(ele.localName == "span" || ele.type == "button" ){
                div.setAttribute('n_host_categories',n_row);
            }

            if(ele.type == "button"){
                div.value = ele.value;
            }

            if(typeof ele.length != 'undefined'){

                for(var x = 0; x < ele.length; x++){
                    var option_val = ele[x].value;
                    var option_lbl = ele[x].innerText;
                    var selected = ele[x].selected;
                
                    div.options[x] = new Option(option_lbl,option_val,'',selected);
                }

            }
            
            jQuery("#tbl_host_categories #" + td_id).append(div);
        }
    }
}

function wp_maiscanner_feedback(n_row,type){
    var color = "";

    if(type == "success"){
        color = "#9CCC65";
    }
    else if(type == "error"){
        color = "#FF7043";
    }

    jQuery("#tr_"+n_row).css("background",color);

    setTimeout(function(){ jQuery("#tr_"+n_row).css("background",""); }, 3000);
}

function wp_maiscanner_save_host_categories(n_row,host_categories_val){    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            'action': 'wp_mailscanner_hc_create',
            'row': host_categories_val,
        },
        success:function(data) {
            wp_maiscanner_feedback(n_row,"success");
            jQuery("#host_category"+n_row).prop("readonly", true);
        },
        error: function(errorThrown){
            wp_maiscanner_feedback(n_row,"error");
            console.log(errorThrown);
        }
    });
}

function wp_maiscanner_delete_host_categories(n_row,host){
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            'action': 'wp_mailscanner_hc_delete',
            'host': host,
        },
        success:function(data) {
            setTimeout(function(){ jQuery("#tr_"+n_row).remove(); });

            if(n_row == 0){ location.reload('true'); }
        },
        error: function(errorThrown){
            wp_maiscanner_feedback(n_row,"error");
            console.log(errorThrown);
        }
    });
}

jQuery(document).on('click', '.save_host_categories', function () {
    var host_categories_val = new Object;
    var n_host_categories = jQuery(this).attr("n_host_categories");
    
    for(var i = 0; i < jQuery("#tbl_host_categories tbody #tr_"+n_host_categories+" td").length; i++){
        var ele = jQuery("#tbl_host_categories tbody #tr_"+n_host_categories+" td")[i].firstElementChild;
        
        if(ele.localName == "input" || ele.localName == "select"){
            
            var name = ele.name.replace('[]', '');

            //console.log(ele);
            //console.log(jQuery(ele).val());

            host_categories_val[name] = jQuery(ele).val();
        }
    }

    wp_maiscanner_save_host_categories(n_host_categories,host_categories_val);
});

jQuery(document).on('click', '.delete_host_categories', function () {
    var n_host_categories = jQuery(this).attr("n_host_categories");

    var host = jQuery("#host_category"+n_host_categories)[0].value;

    wp_maiscanner_delete_host_categories(n_host_categories,host);
});