<?php
/**
 * Plugin Name: Guest Post_Page Submission
 * Plugin URI:  https://wordpress.org/plugins/guest-post-page/
 * Description: Using this plugin Authors can login and submit a Custom form to create post as pending status for admin approval. And also show posts which are pending on a separate page.
 * Version:     1.0.1
 * Author:      Aakash Sharma
 * Author URI:  https://github.com/WordPress/classic-editor/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: guest-post-submission
 * Domain Path: /
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}


/**
Define custom constant
*/
require_once(ABSPATH . 'wp-admin/includes/image.php');
define('GPURL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
define('GPPATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );


/**
@WP Action hooks and function
Add Submenu under Wordpress Settings menu
Shortcode definittion
*/
add_action("admin_menu", "cspd_imdb_options_submenu");
function cspd_imdb_options_submenu() {
  add_submenu_page(
        'options-general.php',
        'Guest Post Settings',
        'GP Shortcodes',
        'administrator',
        'gp-options',
        'gp_settings_page_content' );
}


/**
This is generic function to show 
the plugin's settings link on plugin 
listing page in admin
*/
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'my_plugin_settings_link' );
function my_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=gp-options">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}


/**
This is generic function to show 
the plugin's settings page in admin
*/
function gp_settings_page_content(){
	?>
    
    <div class="wrap gpbox">
    	<h1>Guest Post Form/Listing Shortcodes</h1>
        <p>This page is showing the shortcodes which you can use to show on any page or post for logged in Role for Authors only.</p>
        <h2>1. Displaying Form</h2>
        <div class="inner_wrapper">
        	<h2>Post/Page Shortcode</h2>
            <code>[show_post_form]</code>
        </div>
        <div class="inner_wrapper">
        	<h2>Template Shortcode</h2>
            <code>&lt;?php echo do_shortcode('[show_post_form]'); ?&gt;</code>
        </div>
        <br />
        <br />
        <h2>2. Displaying Pending Post List</h2>
        <div class="inner_wrapper">
        	<h2>Post/Page Shortcode</h2>
            <code>[show_posts_list]</code>
        </div>
        <div class="inner_wrapper">
        	<h2>Template Shortcode</h2>
            <code>&lt;?php echo do_shortcode('[show_posts_list]'); ?&gt;</code>
        </div>
    </div>
    
    <?php
}


/**
@WP Action hooks and function
Attaching plugin styles and script for front-end UI
*/
add_action('wp_enqueue_scripts', 'ajaxform_enqueuescripts');
function ajaxform_enqueuescripts()
{
	wp_enqueue_script( 'ajaxcontact', GPURL.'/js/ajaxcontact.js', array('jquery'));
	wp_localize_script( 'ajaxcontact', 'form_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_style( 'formstyle', GPURL.'/css/gpstyle.css', array() );
	wp_enqueue_style( 'paginationcss', GPURL.'/css/jPages.css', array() );
	wp_enqueue_style( 'animatecss', GPURL.'/css/animate.css', array() );
	wp_enqueue_script( 'paginatejs', GPURL.'/js/jPages.min.js', array('jquery'));
}


/**
@WP Action hooks and function
Attaching plugin styles/script for Admin UI
*/
add_action('admin_enqueue_scripts', 'load_wp_admin_style');
function load_wp_admin_style(){
    wp_register_style( 'gp_admin_css', GPURL.'/css/admin-style.css', array() );
    wp_enqueue_style( 'gp_admin_css' );
}


/**
@WP Action hooks
Creating Ajax call for form submission
*/
add_action( 'wp_ajax_ajaxform_send_mail', 'ajaxform_send_mail' );
add_action( 'wp_ajax_nopriv_ajaxform_send_mail', 'ajaxform_send_mail' );


/**
Render the Custom form for post submission
This function not accepting any params
*/
function renderForm()
{
?>
<form id="postForm" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" enctype="multipart/form-data">
    <div id="ajaxcontact-text">
        <div id="ajax_response"></div>
        <div class="form-field">
            <label>Post Title </label>
            <input type="text" id="post_title" name="post_title"/>
        </div>
        <div class="form-field">
            <label>Description </label>
            <input type="text" id="post_description" name="post_description"/>
        </div>
        <div class="form-field">
            <label>Excerpt</label>
            <textarea id="post_excerpt" name="post_excerpt" cols="40" rows="4"></textarea>
        </div>
        <div class="form-field">
            <label>Featured Image</label>
            <input type="file" id="post_image" name="post_image" />
            <input type="hidden" id="ajaxurl" name="ajaxurl" value="<?php echo admin_url( 'admin-ajax.php' ); ?>" />
        </div> 
        <div class="form-field">
            <input type="submit" id="submit" name="upload" value="Submit" />
        </div>
    </div>
</form>
<?php
}


/**
AJAX function for handling the form submission
This function not accepting any params
*/
function ajaxform_send_mail()
{
	
	$results      =  '';
	$error        =  0;
	
	// Get fields value from AJAX function
	$title        =  $_POST['post_title'];
	$description  =  $_POST['post_description'];
	$excerpt      =  $_POST['post_excerpt'];
	$thumbnail    =  $_FILES['post_thumbnail']['name'];

	$admin_email  =  get_option('admin_email');
	$authorData   =  wp_get_current_user();
	$authorEmail  =  $authorData->user_email;
	
	// Post ARGS
	$post = array(
		'post_title'       =>  $title,
		'post_content'     =>  $description,
		'post_excerpt'     =>  $excerpt,
		'post_type'        =>  'post',
		'post_status'      =>  'pending'
	);
	
	// Insert New post with pending status
	$post_id = wp_insert_post( $post ); 
	
	if($post_id){
		
		// Add Featured Image to Post
		$upload = wp_upload_bits( $_FILES["post_image"]["name"], null, file_get_contents( $_FILES["post_image"]["tmp_name"] ) );
        if ( ! $upload['error'] ) {
            $post_id = $post_id; //set post id to which you need to add featured image
            $filename = $upload['file'];
            $wp_filetype = wp_check_filetype( $filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name( $filename ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
 
            $attachment_id = wp_insert_attachment( $attachment, $filename, $post_id );
 
            if ( ! is_wp_error( $attachment_id ) ) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
 
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
                wp_update_attachment_metadata( $attachment_id, $attachment_data );
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }
	}
	
	if ( is_wp_error( $post_id ) ) {
	
	 	echo 'There was a problem to register post. Please try again';
	    die;
	
	}else{
			
		// Check for blank fields
		if( strlen($title) == 0 )
		{
			$results = "Post title is missing.";
			$error = 1;
		}
		elseif( strlen($description) == 0 )
		{
			$results = "Post Description is missing.";
			$error = 1;
		}
		elseif( strlen($excerpt) == 0 )
		{
			$results = "Post Excerpt is missing.";
			$error = 1;
		}
		
		if($error == 0)
		{
			$imageUrl = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id), 'thumbnail' );
			$body  = "<br>Hello Admin,<br><br><br>";
        
            $body .= "<strong>USER INFORMATION:</strong><br><br>";
			$body .= "------------------------<br><br>";
			$body .= "<strong>Post Title: </strong> ".$title." <br><br>";
			$body .= "<strong>Post Description: </strong> ".$description." <br><br>";
			$body .= "<strong>Post Excerpt: </strong> ".$excerpt." <br><br>";
			$body .= "<strong>Post Featured Image: </strong> ".$imageUrl[0]." <br><br><br>";
			
			$body .= "Thanks<br><br>";
			$body .= "Have a great day!!<br><br>";

			$subject = "New Post Created by a Author for Moderation:"; // Email Subject
            $headers = "From: ".$authorEmail." \r\n";
			$headers = "From: AUTHOR <".$authorEmail."> \r\n";
            $headers .= "Reply-To: $admin_email \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			if(wp_mail($admin_email, $subject, $body, $headers))
			{
				$results = "*Email sent to Admin.";
			}	
			else{
				$results = "*The mail could not be sent.";
			}
		}
	
		die($results);
	}
}


/**
@WP Action hook and function
Show Form on frontend using shortcode
*/
add_shortcode( 'show_post_form', 'form_shortcode_func' );
function form_shortcode_func( $atts )
{
	ob_start();
	$userData = wcmo_get_current_user_roles();
	if($userData == 'author'):
		renderForm();
	endif;
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


/**
@WP Action hook and function
Show Pending Posts List on frontend 
using shortcode
*/
add_shortcode( 'show_posts_list', 'show_post_shortcode_func' );
function show_post_shortcode_func( $atts )
{
	global $wpdb;
	ob_start();
	$current_user = wp_get_current_user();
	if ( get_query_var('paged') ) {
		$paged = get_query_var('paged');
	} elseif ( get_query_var('page') ) { // 'page' is used instead of 'paged' on Static Front Page
		$paged = get_query_var('page');
	} else {
		$paged = 1;
	}
	$args = array(
		'post_type' => 'post',
		'post_author' => $current_user->ID,
		'post_status' => array('pending'),
		'posts_per_page' => 1,
   	    'paged' => $paged,  
		'order' => 'DESC', 
    	'orderby' => 'date'
	);
	$query = new WP_Query($args);
	?>
	<table id="post_container" class="post_listing" width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <th>Post Title</th>
        <th>Post Excerpt</th>
        <th>Post Thumbnail</th>
        <th>Post Status</th>
       </tr>
		<?php	
        while ( $query->have_posts() ) : $query->the_post();
        $imageUrl = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID), 'thumbnail' );
        ?>
          <tr>
            <td><?php the_title(); ?></td>
            <td><?php the_excerpt(); ?></td>
            <td><img class="list-img" src="<?php echo $imageUrl[0]; ?>" width="70" alt="<?php the_title(); ?>" /></td>
            <td><?php echo get_post_status ( $post->ID ); ?></td>
          </tr>     
		<?php
        endwhile;
        ?>
        </table>
        
		<?php 
		echo '<div class="page-navi">';
		// Custom pagination 
		$total = $query->max_num_pages;
		// Only paginate if we have more than one page
		if ( $total > 1 )  {
			 // Get the current page
			 if ( !$current_page = get_query_var('paged') )
				  $current_page = 1;
			 // Structure of “format” depends on whether we’re using pretty permalinks
			 $format = empty( get_option('permalink_structure') ) ? '&page=%#%' : 'page/%#%/';
			 echo paginate_links(array(
				  'base' => get_pagenum_link(1) . '%_%',
				  'format' => $format,
				  'current' => $current_page,
				  'total' => $total,
				  'mid_size' => 4,
				  'type' => 'list'
			 ));
		}
		echo '</div>';
		wp_reset_postdata();
		
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


/**
Get logged in user/author role
*/
function wcmo_get_current_user_roles() {
 if( is_user_logged_in() ) {
	 $user = wp_get_current_user();
	 $roles = ( array ) $user->roles;
	 return $roles[0];
 } else {
 	return false;
 }
}

