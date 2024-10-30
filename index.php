<?php
/*
Plugin Name: LoginToDownload
Plugin URI: http://markramosonline.com/wp-plugins/login-to-download/mlr-dl_link.zip
Description: User must be registered or Logged in before you can start to download the file. It will add a Meta Box on your Post/Page editor and let's you enter the link where the file to be downloaded.
If Meta box is empty it will not show.
Version: 0.1
Author: Mark Lyndon Ramos
Author URI: http://markramosonline.com
License: A "Slug" license name e.g. GPL2
*/
/*  Copyright 2013  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(is_admin()){
	/* Define the custom box */
	
	add_action( 'add_meta_boxes', 'mlrdl_add_custom_box' );
	
	// backwards compatible (before WP 3.0)
	// add_action( 'admin_init', 'myplugin_add_custom_box', 1 );
	
	/* Do something with the data entered */
	add_action( 'save_post', 'mlrdl_save_postdata' );
	
	/* Adds a box to the main column on the Post and Page edit screens */
	function mlrdl_add_custom_box() {
	    add_meta_box( 
	        'mlrdl_sectionid',
	        __( 'Downloadable Link', 'mlrdl_textdomain' ),
	        'mlrdl_inner_custom_box',
	        'post' 
	    );
	    add_meta_box(
	        'myplugin_sectionid',
	        __( 'Downloadable Link', 'mlrdl_textdomain' ), 
	        'mlrdl_inner_custom_box',
	        'page'
	    );

	}
	
	/* Prints the box content */
	function mlrdl_inner_custom_box( $post ) {
	
	  // Use nonce for verification
	  wp_nonce_field( plugin_basename( __FILE__ ), 'mlrdl_noncename' );
	
	  // The actual fields for data entry
	  echo '<label for="mlrdl_new_field">';
	       _e("Link to Source", 'mlrdl_textdomain' );
	  echo '</label> ';
	  echo '<input type="text" id="mlrdl_new_field" name="mlrdl_new_field" value="" style="width: 300px" />';
	}
	
	/* When the post is saved, saves our custom data */
	function mlrdl_save_postdata( $post_id ) {
	  // verify if this is an auto save routine. 
	  // If it is our form has not been submitted, so we dont want to do anything
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	      return;
	
	  // verify this came from the our screen and with proper authorization,
	  // because save_post can be triggered at other times
	
	  if ( !wp_verify_nonce( $_POST['mlrdl_noncename'], plugin_basename( __FILE__ ) ) )
	      return;
	
	  
	  // Check permissions
	  if ( 'page' == $_POST['post_type'] ) 
	  {
	    if ( !current_user_can( 'edit_page', $post_id ) )
	        return;
	  }
	  else
	  {
	    if ( !current_user_can( 'edit_post', $post_id ) )
	        return;
	  }
	
	  // OK, we're authenticated: we need to find and save the data
	
	  $mydata = $_POST['mlrdl_new_field'];
	  if(!empty($mydata) || $mydata != ""){
	  
			add_post_meta($post_id, 'mlrdl_linkpath', $mydata, true)
					or
			update_post_meta($post_id, 'mlrdl_linkpath', $mydata);
	  }
	
	  // Do something with $mydata 
	  // probably using add_post_meta(), update_post_meta(), or 
	  // a custom table (see Further Reading section below)
	}
}

if(!function_exists('mlrdl_content_filter')){
	add_filter( 'the_content', 'mlrdl_content_filter', 20 );
	
	function mlrdl_content_filter( $content ) {
	
		global $wpdb, $post, $table_prefix, $current_user;
		
      	get_currentuserinfo();
		
		$meta_values = get_post_meta($post->ID, 'mlrdl_linkpath', true);
		
		if(!empty($meta_values) || $meta_values != ""){
			if(!is_user_logged_in()) {
			 //no user logged in

			    if(is_single()){
			 	$msg = "To download this \"File\" you must be registered and logged in.";  //"To register/login click <a href=\"javascript:void(0);\" id=\"mlr_loginCaller\" class=\"\">here</a>.";
				//$msg .= "<div style=\"display: none\" id=\"mlr_loginForm\">".wp_login_form(  )."</div>";
				//$msg .= '
				//    <script type="text/javascript">
				//	jQuery(document).ready(function(){
				//	    jQuery("#mlr_loginCaller").click(function(){
				//		jQuery("#mlr_loginForm").dialog();
				//	    });
				//	});
				//	
				//    </script>
				//';				
			    }

			}else{
				$msg = 'Download this File <a href="'.$meta_values.'">here</a>.';
			}
			
		}
		
		
		return $content."<br>".$msg;
	}
}
?>