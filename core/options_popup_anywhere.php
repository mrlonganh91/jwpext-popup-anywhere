<?php
/**
 * Renders the content of the menu page for the Popup Anywhere plugin.
 *
 * @since    1.0.0
 *
 * @package     Popup Anywhere
 * @subpackage  core
 */

//GET CUSTOM POST TYPE
$args = array(
    'public'   => true,//false
    //'_builtin' => false
);

$output = 'names'; // 'names' or 'objects' (default: 'names')
$operator = 'and'; // 'and' or 'or' (default: 'and')

$post_types = get_post_types( $args, $output, $operator );
if(isset($post_types['attachment'])){
    unset( $post_types['attachment'] );
}
$post_types['jlmenu'] = 'menu';

$class_menu_hide = '';
$class_postype_hide = '';

if(!$jwext_popup_menushow){
    $class_menu_hide = 'jlmenu-hide';
}
if(empty($jwext_popup_assignshow)){
    $class_postype_hide = 'jl-postype-hide';
}

?>

<div class="container">

	<h1 class="animated bounce">Popup Anywhere</h1>
	
	<div class="grid">
        <div class="row">
            <div class="cell-12">
                <div class="itemIntroText">
                    <ul>
                        <li>This plugin will show popup anywhere on your webpage.</li>
                        <li>It help you easier to do an advertisement or a notification when customers/clients visits your website.</li>
                        <li>Or you can use short code:
                            <ul>
                                <li>[popupanywhere url="wp-content/uploads/2022/09/demo.png" type="image" redirect_link="https://joomlaweb.site" width="600" height="400"]</li>
                                <li>[popupanywhere type="content" p="post id" width="600" height="400"]</li>
                                <li>[popupanywhere type="external" url="https://www.youtube.com/watch?v=8XyNtP5eiE8" width="600" height="400"]</li>
                                <li>Session & cookie not support in shortcode tag</li>
                            </ul>
                        </li>            
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="cell-6">
                <form action="#" method="post" id="popup-form">
                	<div data-role="accordion" data-one-frame="true" data-show-active="true">
                		<div class="frame">
                            <div class="heading"><?php echo __( 'Basic Config', 'popup-anywhere' );?></div>
                            <div class="content">
                                <div class="p-2">
                                	<div class="form-group">
                                        <label><?php echo __( 'Select type', 'popup-anywhere' );?></label>                    
                                        <select data-role="select" name="jwext_popup_type">                            
                                            <option value="1" <?php echo $jwext_popup_type==1?"selected":"" ?>><?php echo __( 'Image', 'popup-anywhere' );?></option>
                                            <option value="2" <?php echo $jwext_popup_type==2?"selected":"" ?>><?php echo __( 'Content', 'popup-anywhere' );?></option>
                                            <option value="3" <?php echo $jwext_popup_type==3?"selected":"" ?>><?php echo __( 'External Link', 'popup-anywhere' );?></option>                            
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo __( 'Select image type desc', 'popup-anywhere' );?></label><br/>                    
                                        <?php 
                                        if( intval( $jwext_image_id ) > 0 ) {
                                            // Change with the image size you want to use
                                            $image = wp_get_attachment_image( $jwext_image_id, 'small', false, array( 'id' => 'jwext-preview-image' ) );
                                        } else {
                                            // Some default image
                                            $image = '<img id="jwext-preview-image" src="'.PLUGIN_DIR_URL_POPUP_ANYWHERE.'/template/assets/images/test.png" />';
                                        }
                                        
                                        echo $image; 
                                        ?>
                                        <br/>
                                        <input type="hidden" name="jwext_image_id" id="jwext_image_id" value="<?php echo esc_attr( $jwext_image_id ); ?>" class="regular-text" />
                                        <input type='button' class="button-primary" value="<?php echo  __( 'Select a Image', 'popup-anywhere' ); ?>" id="jwext_media_manager"/>
                                    </div>
                                     <div class="form-group">
                                        <label><?php echo __( 'Redirect to a link when click Image Popup', 'popup-anywhere' );?></label><br/>                    
                                        <input type="text" data-role="input" data-prepend="Redirect to a link: " name="jwext_redirect_link" value="<?php echo esc_url($jwext_redirect_link);?>">
                                    </div>                                 
                                    
                                    <div class="form-group">
                                        <label><?php echo __( 'Select external type desc', 'popup-anywhere' );?></label><br/>                    
                                        <?php 
                                        if(!empty($post_types)){
                                            
                                            echo '<select data-role="select" id="select-content-popup-item" name="jwext_content_popup">';
                                            foreach($post_types as $kp=>$vp){
                                                if($kp=='jlmenu'){
                                                    continue;
                                                }
                                                echo $this->getItemsCustom($kp,$jwext_content_popup);
                                            }
                                            echo '</select>';
                                        }
                                        ?>
                                    </div>
                                    <div class="form-group setupinputext">
                                     
                                        <label><?php echo __( 'social network', 'popup-anywhere' );?></label><br/>                    
                                        <input type="text" data-role="input" data-prepend="External Link:" name="external_link" value="<?php echo esc_url($external_link);?>">
                                        
                                        <label><?php echo __( 'Setup width & height Popup)', 'popup-anywhere' );?></label><br/>                    
                                        <input type="text" data-role="input" data-prepend="Max Width Popup: " name="jwext_width" value="<?php echo esc_html($jwext_width);?>">                                        
                                        <input type="text" data-role="input" data-prepend="Max Height Popup: " name="jwext_height" value="<?php echo esc_html($jwext_height);?>">
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo __( 'Show When?', 'popup-anywhere' );?></label>                    
                                        <select data-role="select" name="jwext_show_when">      
                                            <option value="1" <?php echo $jwext_show_when==1?"selected":"" ?>><?php echo __( 'Always', 'popup-anywhere' );?></option>                      
                                            <option value="2" <?php echo $jwext_show_when==2?"selected":"" ?>><?php echo __( 'Cookie', 'popup-anywhere' );?></option>
                                            <option value="3" <?php echo $jwext_show_when==3?"selected":"" ?>><?php echo __( 'Session DB', 'popup-anywhere' );?></option>                                            
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" data-role="input" data-prepend="Live time cookie/session(min):" name="jwext_livetime_cookie" value="<?php echo esc_html($jwext_livetime_cookie);?>">
                                    </div>  
                                    <div class="form-group">
                                        <label><?php echo __( 'Close Popup after loaded Popup(5000=5s)', 'popup-anywhere' );?></label><br/>                    
                                        <input type="text" data-role="input" data-prepend="Setup Close Popup: " name="jwext_close_s" value="<?php echo esc_html($jwext_close_s);?>">
                                    </div>  

                                </div>
                            </div>
                        </div>     
                		<div class="frame">
                            <div class="heading"><?php echo __( 'Advance Config', 'popup-anywhere' );?></div>
                            <div class="content">
                            	<div class="p-2">
                                    <div class="form-group">
                                        <label><?php echo __( 'Show on all pages (if using this feature, the customization below will not be used)', 'popup-anywhere' );?></label><br/>                    
                                        <input type="checkbox" data-role="switch" <?php echo $jwext_popup_onpages=='on'?"checked":"" ?> data-material="true" name="jwext_popup_onpages" id="jwext_popup_onpages">
                                    </div>
                                    <hr/>
                                    <div class="form-group" id="popup-assign-show">
                                        <label><?php echo __( 'Assign Show Popup', 'popup-anywhere' );?></label><br/>
                                        <select data-role="select" multiple name="jwext_popup_assignshow[]" id="jwext_popup_assignshow">
                                            <?php
                                                if(count($post_types)){
                                                    foreach($post_types as $k=>$v){
                                                        ?>
                                                            <option value="<?php echo esc_html($k);?>" <?php echo $jwext_popup_assignshow && array_key_exists(esc_html($k), $jwext_popup_assignshow)?"selected":"" ?>><?php echo esc_html($v);?></option>            
                                                        <?php
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group <?php echo $class_postype_hide;?>" id="popup-item-form-group">
                                        <label><?php echo __( 'Items Assign Custom Posts Type', 'popup-anywhere' );?></label><br/>
										
                                        <div id="div-render-popup-item">
                                              <?php 
                                                echo '<select data-role="select" multiple id="select-render-popup-item" name="jwext_custom_postype[]">';
                                                if(!empty($jwext_popup_assignshow)){
                                                    unset($jwext_popup_assignshow['jlmenu']);
                                                    foreach($jwext_popup_assignshow as $kas=>$vas){
                                                        echo $this->getItemsCustom(esc_html($kas),$jwext_custom_postype);
                                                    }
                                                }
                                                echo '</select>';
                                              ?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo $class_menu_hide;?>" id="popup-menu-item-form-group">
                                        <label><?php echo __( 'Items Assign Menu Items', 'popup-anywhere' );?></label><br/>                    
                                        <div id="div-render-popup-menu-item">
                                              <?php 
                                                echo '<select data-role="select" multiple id="select-render-popup-menu-item" name="jwext_custom_menu[]">';
                                                if($jwext_popup_menushow){
                                                    unset($jwext_popup_assignshow['jlmenu']);
                                                        echo $this->getItemsCustom('jlmenu',$jwext_custom_menu);                                    
                                                }
                                                echo '</select>';
                                              ?>
                                        </div>
                                    </div>
                                 </div>  
                            </div><!-- content -->
                        </div><!-- end frame -->
                               
                        
                    </div><!-- accordion -->
                    <div class="form-group">
                    	<input type="hidden" value='<?php echo addslashes(json_encode($jwext_custom_postype));?>' name="jwext_hid_custom_postype" id="jwext_hid_custom_postype"/>
                        <input type="hidden" value='<?php echo addslashes(json_encode($jwext_custom_menu));?>' name="jwext_hid_custom_menu" id="jwext_hid_custom_menu"/>
                    	<input type="hidden" value="<?php echo admin_url('admin-ajax.php');?>" name="jwext_admin_url" id="jwext_admin_url"/>
                        <button id="jwext-btn-save" class="button-primary"><?php echo __('Save','popup-anywhere');?></button>     
                        <?php //submit_button( 'Save' ); ?>
                        <?php wp_nonce_field( 'popup-anywhere-page-save', 'popup-anywhere-page-save-nonce' ); ?>                   
                    </div>
                </form>
            </div>
        </div>
    </div>    
	

</div><!-- .wrap -->
