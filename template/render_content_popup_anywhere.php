<?php 
    
    $post_data = get_post( $jwext_content_popup );    
    if($post_data){
        $content=apply_filters('the_content', get_post_field('post_content', $jwext_content_popup));
    }else{
        $content = __( 'You have not set up Content show in Popup', 'popup-anywhere' );
    }    
?>


<a id="jwpext_example1" title="Popup Anywhere Plugin" href="#jwpext_inline" class="jwpext_none" data-popup-type="inline">
    <?php echo __( 'You have not set up Content show in Popup', 'popup-anywhere' );?>
</a>
<div id="jwpext_inline" class="jwpext_none">
    <?php echo $content;?>
</div>

<script>
jQuery(document).ready(function($) {
	$('#jwpext_example1').popup({
		width:'<?php echo esc_html($jwext_width);?>',// neu k set width height mac dinh se la kich thuoc anh that
		height:'<?php echo esc_html($jwext_height);?>',
		showClass: 'overflow animated shakeX',
		hideClass: 'animated backOutDown',
		timeout:'<?php echo esc_html($jwext_close_s);?>',
		clickOverlayHid:false
	}).trigger('click');
	
	//fix close button not show when adminbar show
	if($("#wpadminbar").size()){
		var height_adminbar = $("#wpadminbar").outerHeight();
		$(".popup-close").addClass("animated rubberBand").css('top',height_adminbar);		
	}
});
</script>