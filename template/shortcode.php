<?php 
$jwext_popup_type 	= trim($popup_attr['type']);
$jwext_width 		= (int)trim($popup_attr['width'])?trim($popup_attr['width']):(int)get_option( 'jwext_width','500' );
$jwext_height 		= (int)trim($popup_attr['height'])?trim($popup_attr['height']):(int)get_option( 'jwext_height','400' );
$jwext_close_s 		= trim(get_option( 'jwext_close_s','' ));

$jwext_redirect_link= trim($popup_attr['redirect_link'])?esc_url(trim($popup_attr['redirect_link'])):esc_url(trim(get_option( 'jwext_redirect_link','' )));


if($jwext_popup_type=='image'){
    $url = trim($popup_attr['url']);
    if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)){
        $url = home_url('/').$url;
    }
    ?>
    	<a id="jwpext_example1" href="<?php echo esc_url($url);?>" title="Popup Anywhere Plugin" style="display:none">    
    		<img alt="jwpext_example1" src="<?php echo esc_url($url);?>" />
        </a>
        <script>
        jQuery(document).ready(function($) {
        	$('#jwpext_example1').popup({
        		width:'<?php echo esc_html($jwext_width);?>',// neu k set width height mac dinh se la kich thuoc anh that
        		height:'<?php echo esc_html($jwext_height);?>',
        		showClass: 'overflow animated shakeX',
        		hideClass: 'animated backOutDown',
        		redirect_link:'<?php echo wp_kses_post($jwext_redirect_link);?>',
        		timeout:'<?php echo esc_html($jwext_close_s);?>',
        		clickOverlayHid:true
        	}).trigger('click');
        	
        	//fix close button not show when adminbar show
        	if($("#wpadminbar").size()){
        		var height_adminbar = $("#wpadminbar").outerHeight();
        		$(".popup-close").addClass("animated rubberBand").css('top',height_adminbar);		
        	}
            
        });
        </script>
    <?php
}else if($jwext_popup_type=='content'){
    $jwext_content_popup = trim($popup_attr['p']);
    include_once POPUPANW_DIREXT . '/template/render_content_popup_anywhere.php';
}else if($jwext_popup_type=='external'){
    $external_link = trim($popup_attr['url']);
    include_once POPUPANW_DIREXT . '/template/render_external_popup_anywhere.php';
}
?>