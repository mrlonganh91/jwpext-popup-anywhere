<?php 
if( intval( $jwext_image_id ) > 0 ) {
    // Change with the image size you want to use
    //thumbnail medium large
    $imageL = wp_get_attachment_image_src( $jwext_image_id, 'large');
    $imageS = wp_get_attachment_image_src( $jwext_image_id, 'thumbnail');
}

?>
<a id="jwpext_example1" href="<?php echo $imageL[0];?>" title="Popup Anywhere Plugin" class="jwpext_none">    
    <img alt="jwpext_example1" src="<?php echo $imageS[0];?>" />
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