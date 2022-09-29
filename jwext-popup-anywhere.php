<?php
/*
* Plugin Name: Popup Anywhere
* Plugin URI: https://joomlaweb.site/wordpress_plugin/popup-anywhere-wordpress-plugin.html
* Description: This plugin will create Popup Anywhere where you want show Popup
* Version: 1.0
* Author: Mr.LongAnh
* Author URI: https://joomlaweb.site
* License: GPLv2 or later
* Text Domain: popup-anywhere	
*/

if(!class_exists("JWEXT_POPUP_ANYWHERE")) {    
    define( 'POPUPANW_DIRURL', plugin_dir_url( __FILE__ ));
    define( 'POPUPANW_DIREXT', plugin_dir_path( __FILE__ ));
    define( 'POPUPANW_COREEXT', POPUPANW_DIREXT . "/core" );
    define( 'POPUPANW_TEMP', POPUPANW_DIREXT . "/template" );

    class JWEXT_POPUP_ANYWHERE {

        /**
         * A reference to an instance of this class.
         */
        private static $instance;

        /**
         * The array of templates that this plugin tracks.
         */
        protected $templates;
        public $boll_show;

        /**
         * Returns an instance of this class.
         */
        public static function get_instance() {
            
            if ( null == self::$instance ) {
                self::$instance = new JWEXT_POPUP_ANYWHERE();
            }
               
            return self::$instance;

        }

        /**
         * Initializes the plugin by setting filters and administration functions.
         */
        private function __construct() {
            /**
		    * Loads Plugin textdomain
		    */
			
            load_plugin_textdomain( 'popup-anywhere', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
            $this->templates = array();

            /* kich hoat tao custom post type */           
            add_action('admin_menu', array( $this, 'add_submenu_options_private'));           
            add_shortcode( 'popupanywhere', array( $this, 'popuptag_func'));

            // add script admin
            add_action( 'admin_enqueue_scripts', array( $this, 'sunset_load_admin_scripts') );
            // add script frontend
            add_action('wp_enqueue_scripts', array( $this, 'frontend_scripts_method'));
            
            // Ajax action to refresh the user image
            add_action( 'wp_ajax_jwext_get_image', array( $this, 'jwext_get_image')  );
            
            add_action('wp_head', array( $this, 'frontend_show_popup'));
            
            $jwext_show_when = (int)get_option( 'jwext_show_when','' );
            //if($jwext_show_when=='3'){
            //    add_action('init', array( $this, 'session_init'));
            //}else 
			if($jwext_show_when=='2'){
                add_action('init', array( $this, 'cookie_init'));
            }
            add_action( 'wp_ajax_get_assignshow', array( $this, 'get_assignshow') );
        }
        function cookie_init(){
            if( is_admin() ) {
                return;
            }
            //unset($_COOKIE['jwext_popupanywhere_cookie']);exit;
            
            if ( !array_key_exists( 'jwext_popupanywhere_cookie', $_COOKIE ) ) {
                $current_date_time = current_datetime()->format('U');
                $jwext_livetime_cookie = (int)get_option( 'jwext_livetime_cookie','15' );
                if(!$jwext_livetime_cookie){
                    $jwext_livetime_cookie = 15;//min
                }
                setcookie("jwext_popupanywhere_cookie", $current_date_time, time()+($jwext_livetime_cookie*60) ); 
                // cookie se phai load xong 1 trang moi khoi tao duoc
                // cookie se tu destroy khong giong nhu session
            }
        }
        function session_init(){
            if( is_admin() ) {
                return;
            } 
            /*if (!session_id()) {
                session_start();
            }
            if ( !array_key_exists( 'jwext_popupanywhere_session', $_SESSION ) ) {
                $current_date_time = current_datetime()->format('U');//format('Y-m-d H:i:s');//format('U');
                $_SESSION['jwext_popupanywhere_session']['current']=$current_date_time;
                $_SESSION['jwext_popupanywhere_session']['frist']=1;
            }*/
        }
        function frontend_show_popup() {           
            // only run on front-end
            if( is_admin() ) {
                return;
            }
            
            $boll_show = false;

            $jwext_popup_type       = get_option('jwext_popup_type', 0);
            $jwext_popup_onpages    = get_option('jwext_popup_onpages', '');
            $jwext_show_when        = get_option( 'jwext_show_when','' );
            
            if(!$jwext_popup_type){
                return;
            }
            $jwext_livetime_cookie = (int)get_option( 'jwext_livetime_cookie','15' );
            
            if(!$jwext_livetime_cookie){
                $jwext_livetime_cookie = 15;//min
            }
            //session_destroy();
            //echo "<pre>";
            //print_r($_SESSION);exit;
            // thiet lap session
			
			
            //if($jwext_show_when=='3' && isset($_SESSION['jwext_popupanywhere_session']) && !empty($_SESSION['jwext_popupanywhere_session']) && isset($_SESSION['jwext_popupanywhere_session']['current'])){
			if($jwext_show_when=='3'){				
                //check live time session
                //destroy
                $current_date_time = current_datetime()->format('U');				
				$jwext_popupanywhere_dbsession = sanitize_text_field(get_option('jwext_popupanywhere_dbsession', ''));
				
				if(!$jwext_popupanywhere_dbsession){					
					$jwext_popupanywhere_dbsession = new stdClass();
					$jwext_popupanywhere_dbsession->jwext_current 	= $current_date_time;
					$jwext_popupanywhere_dbsession->jwext_frist		=1;
					update_option('jwext_popupanywhere_dbsession', json_encode($jwext_popupanywhere_dbsession));					
				}else{
					$jwext_popupanywhere_dbsession = json_decode($jwext_popupanywhere_dbsession);					
				}			
				
                $conlai = $current_date_time-(int)$jwext_popupanywhere_dbsession->jwext_current;				
                if($conlai>($jwext_livetime_cookie*60)){
                    //unset($_SESSION['jwext_popupanywhere_session']);
                    //init session
					$jwext_popupanywhere_dbsession->jwext_current=$current_date_time;
					$jwext_popupanywhere_dbsession->jwext_frist=0;
					update_option('jwext_popupanywhere_dbsession', json_encode($jwext_popupanywhere_dbsession));
                }else if($conlai<=($jwext_livetime_cookie*60) && isset($jwext_popupanywhere_dbsession->jwext_frist) && $jwext_popupanywhere_dbsession->jwext_frist ){					
                    $jwext_popupanywhere_dbsession->jwext_frist=0;
					update_option('jwext_popupanywhere_dbsession', json_encode($jwext_popupanywhere_dbsession));
                }else if($conlai<=($jwext_livetime_cookie*60) && isset($jwext_popupanywhere_dbsession->jwext_frist) && !$jwext_popupanywhere_dbsession->jwext_frist ){					
                    return;
                }else{
                    //unset($_SESSION['jwext_popupanywhere_session']);
					delete_option('jwext_popupanywhere_dbsession');
                    return;
                }
            }
			
            // thiet lap cookie
            if($jwext_show_when=='2' && isset($_COOKIE['jwext_popupanywhere_cookie'])){
                return;
            }
            // thiet lap cookie
            
            
            if($jwext_popup_onpages=='on'){
                $boll_show = true;                 
            }else{
                //check page post menu show
                $jwext_popup_assignshow = get_option('jwext_popup_assignshow', '');
                $jwext_popup_menushow   = 0;
                if($jwext_popup_assignshow){
                    $jwext_popup_assignshow = json_decode($jwext_popup_assignshow);
                    $jwext_popup_assignshow = array_flip($jwext_popup_assignshow);
                    if (array_key_exists('jlmenu', $jwext_popup_assignshow)) {
                        $jwext_popup_menushow = 1;                    
                    }
                }   
                $jwext_custom_postype   = get_option('jwext_custom_postype', '');
                $jwext_custom_menu      = get_option('jwext_custom_menu', '');
                if($jwext_custom_postype){
                    $jwext_custom_postype = json_decode($jwext_custom_postype);                
                }else{
                    $jwext_custom_postype = array();
                }
                if($jwext_custom_menu){
                    $jwext_custom_menu = json_decode($jwext_custom_menu);
                }else{
                    $jwext_custom_menu = array();
                }
                
                $home_url = home_url( "/");
                $homepage = is_home();
                
                
                if(empty($jwext_popup_assignshow))  {
                    return;
                }
                global $wp_query;
                global $wp;
                $postid = $wp_query->post->ID;
                $post = get_post();
                
                // get real link , not permarlink struc
                //echo add_query_arg( $wp->query_vars, home_url() );exit;
                
                //term id category, tax, tag
                //$queried_object = get_queried_object();
                //$term_id = $queried_object->term_id;
                
                // if page thi co query['pagename'] hoac is_page
                // if post thi co query['name'] is_single 
                // if custom post type thi co query['post_type'] is_single
                // is_singular page, post co tag trong admin setup

                foreach($jwext_popup_assignshow as $keyas=>$vas){
                    
                    if($keyas=='post' && is_single() && !isset($wp_query->query['post_type']) && $postid){
                        if (in_array($postid, $jwext_custom_postype)) {
                            $boll_show = true;
                            break;
                        }else{
                            $boll_show = false;
                        }   
                    }else if($keyas=='page' && is_page() && $postid){                        
                        if (in_array($postid, $jwext_custom_postype)) {
                            $boll_show = true;
                            break;
                        }else{
                            $boll_show = false;
                        }   
                    }else if($keyas=='jlmenu'){
                        //$menusz = wp_get_nav_menus();
                        // chi lay link cua menu location
                        $menu_locations = get_nav_menu_locations();
                        $uri = sanitize_textarea_field($_SERVER['REQUEST_URI']);  
						
                        // thay the cho uri
                        // va cuoi cua url luon co dau / (http://localhost/wordpress/ thay vi http://localhost/wordpress)
                        
                        $current_url = home_url( add_query_arg( array(), $wp->request )."/" );
                        
                        if(empty($menu_locations)){
                            continue;
                        }else{
                            // chi lay menu co locations
                            $get_menuitem_object = new stdClass();
                            foreach($menu_locations as $location){
                                $menu_items = wp_get_nav_menu_items( $location, array( 'order' => 'DESC' ) );
                                // get menu item id cua link hien tai
                                foreach($menu_items as $item){
                                    // rieng homepage neu dung strpos luc nao cung trung voi cac menu item con lai
                                    if($homepage && substr(trim($item->url), -1)=="/" && trim($item->url)==$current_url){
                                        $get_menuitem_object = $item;
                                        break;
                                    }else if($homepage && substr(trim($item->url), -1)!="/" && (trim($item->url)."/")==$current_url){
                                        $get_menuitem_object = $item;
                                        break;
                                    }else if( !$homepage && strpos($item->url, $uri) !== false ){
                                        $get_menuitem_object = $item;
                                        break;
                                    }
                                }
                                if( !$get_menuitem_object || !isset($get_menuitem_object->ID) ){
                                    break;
                                }
                            }
                            // khong tim duoc menu item id cua link hien tai so voi cac item menu location
                            if( !$get_menuitem_object || !isset($get_menuitem_object->ID) ){
                                continue;
                            }
                            if(in_array("menu_".$get_menuitem_object->ID,$jwext_custom_menu)){
                                $boll_show = true;
                            }
                        }
                    }else if(is_single() && isset($wp_query->query['post_type']) && $postid){
                        //custom post type
                        if (in_array($postid, $jwext_custom_postype)) {
                            $boll_show = true;
                            break;
                        }else{
                            $boll_show = false;
                        }  
                    }else{
                        continue;
                    }
                }
                if(!$boll_show){
                    $this->boll_show = false;
                    return;
                }
            }
            if(!$boll_show){
                $this->boll_show = false;
                return;
            }
            $this->boll_show = true;
            
            $jwext_image_id = get_option( 'jwext_image_id',0 );
            $jwext_content_popup = (int)get_option( 'jwext_content_popup','' );
            $external_link = get_option( 'external_link','' );
            $jwext_width = (int)get_option( 'jwext_width','500' );            
            $jwext_height = (int)get_option( 'jwext_height','400' );
            $jwext_close_s = trim(get_option( 'jwext_close_s','' ));
            $jwext_redirect_link= trim(get_option( 'jwext_redirect_link','https://joomlaweb.site' ));
            /*if($jwext_close_s){
                $jwext_close_s='setTimeout("jQuery.fancybox.close()",'.$jwext_close_s.');';                
            }
            if($jwext_redirect_link){
                $jwext_redirect_link='$(".fancybox-image").wrap($("<a />", {
                    href: "'.$jwext_redirect_link.'",
                    target: "_blank"
                }));';
            }*/
           
            
            if($jwext_popup_type=='1'){
                include_once POPUPANW_DIREXT . '/template/render_image_popup_anywhere.php';
            }else if($jwext_popup_type=='2'){
                include_once POPUPANW_DIREXT . '/template/render_content_popup_anywhere.php';
            }else if($jwext_popup_type=='3'){
                include_once POPUPANW_DIREXT . '/template/render_external_popup_anywhere.php';
            }
        }
        
        
        function jwext_get_image() {
            if(isset($_GET['id']) ){
                $image = wp_get_attachment_image( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ), 'medium', false, array( 'id' => 'jwext-preview-image' ) );
                $data = array(
                    'image'    => $image,
                );
                wp_send_json_success( $data );
            } else {
                wp_send_json_error();
            }
        }
        function popuptag_func( $atts ) {
			
            $popup_attr = shortcode_atts( array(
                'type' => '',
                'url' => '',
                'p' => '',
                'redirect_link'=>'',
                'width'=>'',
                'height'=>''
            ), $atts );
            
            
            // this page da duoc gan popup anywhere trong plugin config
            if($this->boll_show==true){
                return;
            }
			
            if(empty($popup_attr) || !isset($popup_attr['type'])){
                return;
            }
            if(trim($popup_attr['type'])=='image' && !trim($popup_attr['url'])){
                return;
            }else if(trim($popup_attr['type'])=='content' && !trim($popup_attr['p'])){
                return;
            }else if(trim($popup_attr['type'])=='external' && !trim($popup_attr['url'])){
                return;
            }
            
            ob_start();
            $output = '';
            include POPUPANW_TEMP.'/shortcode.php';
            $output = ob_get_contents();
            ob_end_clean();
            return $output;            
        }
        
        function add_submenu_options_private(){
            // Options Private la option rieng cua custom post type
            // jwext options la option cua tong the plugin
            // jwtheme options la option cua tong the theme
            add_menu_page(                
                'Popup Anywhere',
                'Popup Anywhere',
                'manage_options',
                'options_popup_anywhere',
                array( $this, 'render_options_popup_anywhere'), // This function will be defined in a moment
                'dashicons-cover-image',
                999
            );            
        }
        function frontend_scripts_method(){    
            if ( ! wp_script_is( 'jquery', 'enqueued' )) {
                //Enqueue
                wp_enqueue_script( 'jquery' );
            }
            
            //wp_enqueue_script('mousewheel-js', POPUPANW_DIRURL.'template/assets/js/fancy/jquery.mousewheel.pack.js');
            //wp_enqueue_script('fancy-js', POPUPANW_DIRURL.'template/assets/js/fancy/jquery.fancybox.js');
            //wp_enqueue_script('fancy-media-js', POPUPANW_DIRURL.'template/assets/js/fancy/jquery.fancybox-media.js');
            

            //wp_enqueue_script('custom-fancy-js', POPUPANW_DIRURL.'template/assets/js/fancy/custom.fancybox.js');
            //wp_enqueue_style('fancy-css', POPUPANW_DIRURL.'template/assets/css/jquery.fancybox.css');
            //wp_enqueue_style('custom-fancy-css', POPUPANW_DIRURL.'template/assets/css/custom_fancybox.css');
            
            wp_enqueue_script('popup-js', POPUPANW_DIRURL.'template/assets/js/popup/jquery.popup.js');            
            wp_enqueue_style('popup-css', POPUPANW_DIRURL.'template/assets/css/jwpext_popup.css');
            wp_enqueue_style('animate-popup-css', POPUPANW_DIRURL.'template/assets/css/animate.compat.css');
            wp_enqueue_style('custom-popup-css', POPUPANW_DIRURL.'template/assets/css/custom_popup.css');
            
        }
        function sunset_load_admin_scripts(){     
            $current_screen = get_current_screen();
            if ( strpos($current_screen->base, 'popup_anywhere') === false) {
                return;
            }else{  
                wp_enqueue_media();
                //wp_enqueue_style('admin-metro-css', 'https://cdn.korzh.com/metroui/v4/css/metro-all.min.css');
                wp_enqueue_script('admin-metro-js', POPUPANW_DIRURL.'template/assets/js/metro.min.js');
                wp_enqueue_style('admin-popupanywhere-custom-css', POPUPANW_DIRURL.'template/assets/css/css.css');
                wp_enqueue_script('admin-popupanywhere-custom-js', POPUPANW_DIRURL.'template/assets/js/js.js');
                wp_enqueue_style('animate-popup-css', POPUPANW_DIRURL.'template/assets/css/animate.compat.css');
            }
        }
        function get_assignshow() {
            
            if(!isset($_POST['assignshow']) || !sanitize_textarea_field($_POST['assignshow']) ){
                exit('NOT_FOUND_ASSIGN_SHOW_ITEM');
            }
            $data = str_replace('\\','',sanitize_textarea_field($_POST['assignshow']));
            $jwext_popup_assignshow = json_decode($data,true);
            $jwext_popup_assignshow = array_flip($jwext_popup_assignshow);
            
            
            $data = str_replace('\\','',sanitize_textarea_field($_POST['jwext_hid_custom_postype']));
            $jwext_hid_custom_postype = json_decode($data,true);
            
            $data = str_replace('\\','',sanitize_textarea_field($_POST['jwext_hid_custom_menu']));
            $jwext_hid_custom_menu = json_decode($data,true);
            
            
            $result = array();
            $result['jlmenu'] = '';
            $result['posttype'] = '';
            foreach($jwext_popup_assignshow as $kas=>$vas){
                if($kas=='jlmenu'){
                    $result['jlmenu'] =  $this->getItemsCustom('jlmenu',$jwext_hid_custom_menu);  
                }else{
                    $result['posttype'] .=  $this->getItemsCustom($kas,$jwext_hid_custom_postype);
                }
            }
            echo json_encode($result);
            die();
            echo "<pre>";
            print_r($result);exit;
            
            
        }
        function render_options_popup_anywhere() {             

            
            //get_option('awesome_text', 'hey-ho') //Options Private

            if (!current_user_can('manage_options')) {
                wp_die('Unauthorized user');
            }
            
            
            
            if( isset( $_POST['popup-anywhere-page-save-nonce'] ) ) {     
             
                $thongtin_nonce = @$_POST['popup-anywhere-page-save-nonce'];               
                
                if( !wp_verify_nonce( $thongtin_nonce, 'popup-anywhere-page-save' ) ) {
                    return;
                }
				//$jwext_popup_type = wp_kses_post($_POST['jwext_popup_type']);				
                $jwext_popup_type           = isset($_POST['jwext_popup_type'])?sanitize_text_field($_POST['jwext_popup_type']):'1';
                $jwext_popup_onpages        = isset($_POST['jwext_popup_onpages'])?sanitize_text_field($_POST['jwext_popup_onpages']):'';
				
				
				
				
				if(isset($_POST['jwext_popup_assignshow']) && is_array($_POST['jwext_popup_assignshow'])){
					//Sanitizing here:
					foreach ( $_POST['jwext_popup_assignshow'] as $key => $val ) {
						$_POST['jwext_popup_assignshow'][ $key ] = sanitize_text_field( $val );
					}					
					$jwext_popup_assignshow     = json_encode($_POST['jwext_popup_assignshow']);
				}else{
					$jwext_popup_assignshow		= '';
				}
				
				if(isset($_POST['jwext_custom_postype']) && is_array($_POST['jwext_custom_postype'])){
					//Sanitizing here:
					foreach ( $_POST['jwext_custom_postype'] as $key => $val ) {
						$_POST['jwext_custom_postype'][ $key ] = sanitize_text_field( $val );
					}					
					$jwext_custom_postype       = json_encode($_POST['jwext_custom_postype']);
				}else{
					$jwext_custom_postype		= '';
				}
				
				if(isset($_POST['jwext_custom_menu']) && is_array($_POST['jwext_custom_menu'])){
					//Sanitizing here:
					foreach ( $_POST['jwext_custom_menu'] as $key => $val ) {
						$_POST['jwext_custom_menu'][ $key ] = sanitize_text_field( $val );
					}
					$jwext_custom_menu       	= json_encode($_POST['jwext_custom_menu']);
				}else{
					$jwext_custom_menu			= '';
				}
				
                $jwext_image_id             = isset($_POST['jwext_image_id'])?sanitize_text_field($_POST['jwext_image_id']):'';
                $jwext_content_popup        = isset($_POST['jwext_content_popup'])?sanitize_text_field($_POST['jwext_content_popup']):'';
             
                
                $external_link              = isset($_POST['external_link'])?sanitize_url($_POST['external_link']):'';
                
				$jwext_width                = (isset($_POST['jwext_width'])&&$_POST['jwext_width'])?(int)sanitize_text_field($_POST['jwext_width']):'500';
                $jwext_height               = (isset($_POST['jwext_height'])&&$_POST['jwext_height'])?(int)sanitize_text_field($_POST['jwext_height']):'400';
				
				
                $jwext_show_when            = isset($_POST['jwext_show_when'])?(int)sanitize_text_field($_POST['jwext_show_when']):'';
                $jwext_livetime_cookie      = (isset($_POST['jwext_livetime_cookie']) && sanitize_text_field($_POST['jwext_livetime_cookie']))?(int)sanitize_text_field($_POST['jwext_livetime_cookie']):'';
                $jwext_close_s              = (isset($_POST['jwext_close_s'])&&sanitize_text_field($_POST['jwext_close_s']))?(int)sanitize_text_field($_POST['jwext_close_s']):'';
                $jwext_redirect_link        = isset($_POST['jwext_redirect_link'])?sanitize_url($_POST['jwext_redirect_link']):'';
                
                
                update_option('jwext_popup_type', $jwext_popup_type);
                update_option('jwext_popup_onpages', $jwext_popup_onpages);
                update_option('jwext_popup_assignshow', $jwext_popup_assignshow);
                
                update_option('jwext_custom_postype', $jwext_custom_postype);
                update_option('jwext_custom_menu', $jwext_custom_menu);
                
                update_option('jwext_image_id', $jwext_image_id);
                update_option('jwext_content_popup', $jwext_content_popup);

                update_option('external_link', $external_link);
                update_option('jwext_width', $jwext_width);
                update_option('jwext_height', $jwext_height);
                update_option('jwext_show_when', $jwext_show_when);
                update_option('jwext_livetime_cookie', $jwext_livetime_cookie);
                update_option('jwext_close_s', $jwext_close_s);
                update_option('jwext_redirect_link', $jwext_redirect_link);
                
            }
            
            
            
            $jwext_popup_type       = get_option('jwext_popup_type', '');
            $jwext_popup_onpages    = get_option('jwext_popup_onpages', '');
            $jwext_popup_assignshow = get_option('jwext_popup_assignshow', '');
            $jwext_popup_menushow   = 0;
            if($jwext_popup_assignshow){
                $jwext_popup_assignshow = json_decode($jwext_popup_assignshow);
                $jwext_popup_assignshow = array_flip($jwext_popup_assignshow);
                if (array_key_exists('jlmenu', $jwext_popup_assignshow)) {
                    $jwext_popup_menushow = 1;
                    
                }
            }         
            $jwext_custom_postype   = get_option('jwext_custom_postype', '');
            $jwext_custom_menu      = get_option('jwext_custom_menu', '');
            if($jwext_custom_postype){
                $jwext_custom_postype = json_decode($jwext_custom_postype);                
            }else{
                $jwext_custom_postype = array();
            }
            if($jwext_custom_menu){
                $jwext_custom_menu = json_decode($jwext_custom_menu);
            }else{
                $jwext_custom_menu = array();
            }
            
            $jwext_image_id = get_option( 'jwext_image_id',0 );
            $jwext_content_popup = array(get_option( 'jwext_content_popup','' ));


            $external_link = get_option( 'external_link','' );
            $jwext_width = get_option( 'jwext_width','' );
            $jwext_height = get_option( 'jwext_height','' );
            $jwext_show_when = get_option( 'jwext_show_when','' );
            $jwext_livetime_cookie = get_option( 'jwext_livetime_cookie','' );                
            $jwext_close_s= get_option( 'jwext_close_s','' );  
            $jwext_redirect_link= get_option( 'jwext_redirect_link','' );
            
            include_once dirname( __FILE__ ) . '/core/options_popup_anywhere.php';   
        }
        function getItemsCustom($type='',$data=Array()){
            if(!$type){
                return;
            }            
            if($type!='jlmenu'){
                $args = array(
                    'post_type' => $type,
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    );
                
                $query = new WP_Query($args);
                
                $html = '<optgroup label="Type '.$type.'">';
                if($query->have_posts()){
                    while($query->have_posts()):
                        $query->the_post();
                        //get_the_permalink()
                        if(!$title=get_the_title()){
                            $title = __('(no title)','popup-anywhere').'-ID:-'.get_the_ID();
                        }
                        
                        if(in_array(get_the_ID(), $data)){
                            $selected = 'selected';
                        }else{
                            $selected = '';
                        }
                        $html .= '<option value="'.get_the_ID().'" '.$selected.'>'.$title.'</option>';
                    endwhile;
                
                    /* Restore original Post Data */
                    wp_reset_postdata();  
                
                }
                $html .= '</optgroup>';
            }else{
                $menusz = wp_get_nav_menus();                
                $menu_locations = get_nav_menu_locations();
                
                //LONGTQ: update lay $menusz co $menu_locations
                //khong lay menu khac
                // de full xoa doan code trong block comment nay di
                if(empty($menu_locations)){
                    $html = '<optgroup label="Menu Location Not Setup">';
                    return $html;
                }else if(empty($menu_locations)){
                    $html = '<optgroup label="Menu Type Not Setup">';
                    return $html;
                }else{
                    
                    $menu_locations = array_flip($menu_locations);
                    
                    $menuini = array();
                    foreach ($menusz as $jwemenu) {
                        if ( array_key_exists($jwemenu->term_id, $menu_locations)) {
                            $jwemenu->name .= "(".$menu_locations[$jwemenu->term_id].")";
                            $menuini[]=$jwemenu;
                        }
                    }
                    $menusz = $menuini;
                }
                //LONGTQ: end update lay $menusz co $menu_locations
                
                
                if(count($menusz)){
                    $html = '';
                    
                    foreach($menusz as $menu){
                        
                        $html .= '<optgroup label="Type Menu-'.$menu->name.'">';
                        $menuitems = wp_get_nav_menu_items( $menu->term_id, array( 'order' => 'DESC' ) ); 
                        
                        
                        // get link nav menu
                        //$id = get_post_meta( 1636, '_menu_item_object_id', true );
                        //$page = get_page( $id );
                        //$link = get_page_link( $id );

                        // echo "<pre>";
                        // print_r($link);exit;  
                        
                        

                        // foreach($menuitems as $mnu){
                        //     $html .= '<option value="menu_'.$mnu->ID.'">'.$mnu->title.'</option>';
                        // }
                        
                        $count = 0;
                        $submenu = false;
                        $htmlsub = '';
                        
                        foreach( $menuitems as $item ):
                            
                            $link = $item->url;
                            $title = $item->title;
                            
                            if(in_array('menu_'.$item->ID, $data)){
                                $selected = 'selected';
                            }else{
                                $selected = '';
                            }
                            
                            // item does not have a parent so menu_item_parent equals 0 (false)
                            if ( !$item->menu_item_parent ){
                                // save this id for later comparison with sub-menu items
                                $parent_id = $item->ID; //=0    
                                $menuitems[$count]->cap=0;                            
                                $html .= '<option value="menu_'.$item->ID.'" '.$selected.'>'.$title.'</option>';
                            }else if ( $item->menu_item_parent && $parent_id == $item->menu_item_parent ){
                                
                                if ( !$submenu ){ 
                                    $submenu = true;                                    
                                }
                                if(!isset($menuitems[$count]->cap)){
                                    $menuitems[$count]->cap = 1;   
                                }
                                for($i=0;$i<$menuitems[$count]->cap;$i++){
                                    $htmlsub .= '--| ';
                                }

                                $html .= '<option value="menu_'.$item->ID.'" '.$selected.'>'.$htmlsub.$title.'</option>';
                                //if ( $menuitems[ $count + 1 ]->menu_item_parent && $menuitems[ $count + 1 ]->menu_item_parent != $parent_id && $submenu ){
                                if ( $menuitems[ $count + 1 ]->menu_item_parent && $submenu ){                                        
                                    $submenu = true; 
                                    $parent_id =  $menuitems[ $count + 1 ]->menu_item_parent;
                                    if($menuitems[ $count + 1 ]->menu_item_parent==$item->ID){
                                        $menuitems[ $count + 1 ]->cap = $menuitems[$count]->cap++;
                                    }else{
                                        // ve cap 1
                                        $htmlsub = ''; 
                                    } 
                                }else if ( !$menuitems[ $count + 1 ]->menu_item_parent  && $submenu ){                                    
                                    $submenu = false; 
                                    $parent_id = 0; 
                                    $htmlsub = '';  
                                }

                            }

                            // if ( $menuitems[ $count + 1 ]->menu_item_parent != $parent_id ):
                                                       
                            // $submenu = false; 
                            // $htmlsub = '';
                            // endif;

                            $count++; 
                        endforeach;        
                        


                        $html .= '</optgroup>';
                        
                    }
                    
                }
                
                
            }            
            return $html;
        }
                
    }
}
add_action( 'plugins_loaded', array( 'JWEXT_POPUP_ANYWHERE', 'get_instance' ) );
