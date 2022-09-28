jQuery(document).ready(function() {

	//console.log(select.val());
	//jQuery('#jwext_popup_onpages').attr("checked",false);
	//$('#popup-form').append('<input type="checkbox" data-role="switch" checked data-material="true" name="jwext_popup_onpages2" id="jwext_popup_onpages2"/>');
	//$('#jwext_popup_assignshow').html('<option value="evps0">eVPS-TEST (30 дней)</option><option value="evps1" selected="selected">eVPS-1</option><option value="evps2">eVPS-2</option>');

	//c1
	//var select = Metro.getPlugin("#jwext_popup_assignshow", 'select');
	//select.data('<option value="evps0">eVPS-TEST (30 дней)</option><option value="evps1" selected="selected">eVPS-1</option><option value="evps2">eVPS-2</option>');

	//c2 
	//$('#jwext_popup_assignshow').html('<option value="evps0">eVPS-TEST (30 дней)</option><option value="evps1" selected="selected">eVPS-1</option><option value="evps2">eVPS-2</option>');
	//Metro.makePlugin("#jwext_popup_assignshow", "select");

	//c3:https://metroui.org.ua/m4q-population.html
	//$('#jwext_popup_assignshow').html('<option value="evps0">eVPS-TEST (30 дней)</option><option value="evps1" selected="selected">eVPS-1</option><option value="evps2">eVPS-2</option>');
	//jQuery("#jwext_popup_assignshow").select();
	
});

jQuery(document).ready(function($) {

	jQuery('input#jwext_media_manager').click(function(e) {

		e.preventDefault();
		var image_frame;
		if (image_frame) {
			image_frame.open();
		}
		// Define image_frame as wp.media object
		image_frame = wp.media({
			title : 'Select Media',
			multiple : false,
			library : {
				type : 'image',
			}
		});

		image_frame.on('close', function() {
			// On close, get selections and save to the hidden input
			// plus other AJAX stuff to refresh the image preview
			var selection = image_frame.state().get('selection');
			var gallery_ids = new Array();
			var my_index = 0;
			selection.each(function(attachment) {
				gallery_ids[my_index] = attachment['id'];
				my_index++;
			});
			var ids = gallery_ids.join(",");
			jQuery('input#jwext_image_id').val(ids);
			Refresh_Image(ids);
		});

		image_frame.on('open', function() {
			// On open, get the id from the hidden input
			// and select the appropiate images in the media manager
			var selection = image_frame.state().get('selection');
			var ids = jQuery('input#jwext_image_id').val().split(',');
			ids.forEach(function(id) {
				var attachment = wp.media.attachment(id);
				attachment.fetch();
				selection.add(attachment ? [ attachment ] : []);
			});

		});

		image_frame.open();
	});
	
	// AJAX ASSIGN SHOW POPUP
	$("#jwext_popup_assignshow").change(function(){
		var selected = ($(this).val());
		//alert(selected[0]);
		var admin_url = $('#jwext_admin_url').val();
		
		
		if(!selected || !admin_url){
			alert('Not found Popup Assign Item');
			return;
		}
		var jwext_hid_custom_postype = $('#jwext_hid_custom_postype').val();
		var jwext_hid_custom_menu = $('#jwext_hid_custom_menu').val();
		
		$.ajax({
            type : "post", 
            url : admin_url,
            data : {
                action: "get_assignshow",
                assignshow : JSON.stringify(selected),
                jwext_hid_custom_postype : jwext_hid_custom_postype,
                jwext_hid_custom_menu : jwext_hid_custom_menu,
            },
            context: this,
            beforeSend: function(){
            	$("div#popup-assign-show").append('<div id="jwext-progress" data-role="progress" data-type="line" data-small="true"></div>');
            },
            success: function(response) {
            	 $("#jwext-progress").remove();	
            	 var obj = jQuery.parseJSON(response);
            	 if(obj.jlmenu!=undefined){
            		//$('#select-render-popup-item').html(obj.jlmenu);
     	 			//$("#select-render-popup-item").select();
            		var select = Metro.getPlugin("#select-render-popup-menu-item", 'select');
     	 			select.data(obj.jlmenu);
     	 			$("#popup-menu-item-form-group").removeClass( "jlmenu-hide" );
            	 }
    	 		 if(obj.posttype!=undefined){
    	 			var select = Metro.getPlugin("#select-render-popup-item", 'select');
    	 			select.data(obj.posttype);
    	 			//var select  = $('#select-render-popup-item').data('select');
    	 		    //select.data(obj.posttype);
    	 			$("#popup-item-form-group").removeClass( "jl-postype-hide" );
            	 }
                
            },
            error: function( jqXHR, textStatus, errorThrown ){
                
            }
        }) 
	});	

});

//Ajax request to refresh the image preview
function Refresh_Image(the_id) {
	var data = {
		action : 'jwext_get_image',
		id : the_id
	};
	jQuery.get(ajaxurl, data, function(response) {
		if (response.success === true) {
			jQuery('#jwext-preview-image').replaceWith(response.data.image);
		}
	});
}
//And the Ajax action to refresh the image preview:

