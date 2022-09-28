<a id="jwpext_example1" title="Popup Anywhere Plugin" href="<?php echo esc_url($external_link);?>" class="jwpext_none" data-popup-type="video"><?php echo __( 'You have not set up external link ', 'popup-anywhere' );?></a>
<script>
jQuery(document).ready(function($) {
	$('#jwpext_example1').popup({
		width:'<?php echo esc_html($jwext_width);?>',// neu k set width height mac dinh se la kich thuoc anh that
		height:'<?php echo esc_html($jwext_height);?>',
		showClass: 'animated shakeX',
		hideClass: 'animated backOutDown',
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
