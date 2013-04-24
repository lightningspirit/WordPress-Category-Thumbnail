<?php
/*
Plugin Name: Category Thumbnail
Plugin URI: http://www.vcarvalho.com/
Version: 1.0
Text Domain: category-thumbnail
Domain Path: /languages/
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Description: Add thumbnail feature to categories
License: GPLv2
*/



// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}



/**
 * Category_Thumbnail
 * 
 * @since 0.1
 * 
 */
class Category_Thumbnail {
	
	
	/**
	 * 
	 * @since 0.1
	 * 
	 */
	function init() {
		add_action( 'admin_head', array( __CLASS__, 'admin_head' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_media_scripts' ), 10, 2 );
		add_filter( 'manage_edit-category_columns', array( __CLASS__, 'manage_edit_columns' ) );
		add_filter( 'manage_category_custom_column', array( __CLASS__, 'manage_category_custom_column' ), 10, 3 );
		
		add_action( 'category_add_form_fields', array( __CLASS__, 'category_add_thumbnail_field' ), 10, 2 );
		add_action( 'category_edit_form_fields', array( __CLASS__, 'category_edit_thumbnail_field' ), 10, 2 );
		add_action( 'edited_category', array( __CLASS__, 'save_category' ), 10, 2 );
		add_action( 'created_category', array( __CLASS__, 'save_category' ), 10, 2 );
		
	}
	
	
	function admin_head() {
		if ( 'edit-category' == get_current_screen()->id ) : ?>
			
<style type="text/css">
.manage-column.column-thumbnail { width: 8%; }
.widefat .thumbnail.column-thumbnail { overflow: visible; }
.widefat .thumbnail.column-thumbnail img { max-width: 50px; max-height: 50px; }
</style>
		
		<?php endif;
		
	}
	
	
	function admin_enqueue_media_scripts() {
		if ( 'edit-category' == get_current_screen()->id ) {
			wp_enqueue_media();
			wp_enqueue_script( 'category-thumbnail', plugin_dir_url( __FILE__ ) . '/category-thumbnail.js', array( 'jquery' ), '2013030501', true );
			
		}
		
	}
	
	
	function manage_edit_columns( $columns ) {
		$keys = array_keys( $columns );
		$values = array_values( $columns );
		
		array_splice( $keys, 1, 0, array( 'thumbnail' ) );
		array_splice( $values, 1, 0, array( '' ) );
		
		return array_combine( $keys, $values );
		
	}
	
	
	function manage_category_custom_column( $null, $column, $cat_id ) {
		
		if ( 'thumbnail' == $column ) {
			return get_category_thumbnail( $cat_id );
			
		}
		
	}
	
	
	/**
	 * Includes thumbnail category in add
	 * 
	 * @since 0.1
	 * 
	 */
	function category_add_thumbnail_field( $category ) {
		global $wp_taxonomies;
		?>

		<div class="form-field hide-if-no-js">
			<p style="color:#222;font-style:normal;"><?php _e( 'Featured Image' ); ?></p>
			<div id="image-container">
				
				<div id="selected-image"></div>
				
				<a id="set-category-thumbnail" title="<?php _e( 'Set featured image' ); ?>" data-uploader-title="<?php _e( 'Choose a thumbnail for this category', 'category-thumbnail' ); ?>" data-uploader-button-text="<?php _e( 'Set Category Thumbnail', 'category-thumbnail' ); ?>" href="#" class="button thickbox">
					<?php _e( 'Set featured image' ); ?>
				</a>
				
				<a id="remove-category-thumbnail" title="<?php _e( 'Set featured image' ); ?>" href="#" style="display:none;">
					<?php _e( 'Remove featured image' ); ?>
				</a>
				
			</div>
			<input name="image" id="image-id" type="hidden" value="" />
			<p>
				<?php printf( __( 'The thumbnail to this %s' ), $wp_taxonomies[ $category ]->labels->singular_name ); ?>
			</p>
		</div>
		
		<?php
		
	}
	
	
	/**
	 * Includes thumbnail category in edit
	 * 
	 * @since 0.1
	 * 
	 */
	function category_edit_thumbnail_field( $tag, $taxonomy ) {
		global $wp_taxonomies;
		
		$image = get_option( 'category_thumbnail_image' );
		
		if ( is_array( $image ) && array_key_exists( $tag->term_id, $image ) ) {
			$image = $image[ $tag->term_id ];
			$attach = wp_get_attachment_image_src( (int) $image );
		
		} else {
			$image = false;
			
		}
	?>
	<tr class="form-field hide-if-no-js">
		<th scope="row" valign="top">
			<p style="color:#222;font-size:13px;"><?php _e( 'Featured Image' ); ?></p>
		</th>
		<td>
			<div id="image-container">
								
				<?php if ( $image ) : ?>
					
				<div id="selected-image">
					<img src="<?php echo esc_url( $attach[0] ); ?>" />
				</div>
					
				<a id="set-category-thumbnail" title="<?php _e( 'Set featured image' ); ?>" data-uploader-title="<?php _e( 'Choose a thumbnail for this category', 'category-thumbnail' ); ?>" data-uploader-button-text="<?php _e( 'Set Category Thumbnail', 'category-thumbnail' ); ?>" href="#" class="button thickbox" style="display:none;">
					<?php _e( 'Set featured image' ); ?>
				</a>
				
				<a id="remove-category-thumbnail" title="<?php _e( 'Set featured image' ); ?>" href="#">
					<?php _e( 'Remove featured image' ); ?>
				</a>
				
					
				<?php else : ?>
				
				<div id="selected-image"></div>
				
				<a id="set-category-thumbnail" title="<?php _e( 'Set featured image' ); ?>" data-uploader-title="<?php _e( 'Choose a thumbnail for this category', 'category-thumbnail' ); ?>" data-uploader-button-text="<?php _e( 'Set Category Thumbnail', 'category-thumbnail' ); ?>" href="#" class="button thickbox">
					<?php _e( 'Set featured image' ); ?>
				</a>
				
				<a id="remove-category-thumbnail" title="<?php _e( 'Set featured image' ); ?>" href="#" style="display:none;">
					<?php _e( 'Remove featured image' ); ?>
				</a>
				
				<?php endif; ?>
			
			</div>
			
			<input name="image" id="image-id" type="hidden" value="<?php echo $image; ?>" />
			
			<p class="description">
				<?php printf( __( 'The thumbnail to this %s' ), $wp_taxonomies[ $taxonomy ]->labels->singular_name ); ?>
			</p>
			
		</td>
	</tr>
	<?php
		
	}
	
	/**
	 * Save image ID
	 * 
	 * @since 0.1
	 * 
	 */
	function save_category( $category_id, $taxonomy_id ) {
	
		if ( isset( $_REQUEST['image'] ) ) {
			// Load existing option
			$image = get_option( 'category_thumbnail_image' );
			
			// Set active taxonomy in array
			$image[ $category_id ] = (int) $_REQUEST['image'];
			
			// Update the option
			update_option( 'category_thumbnail_image', $image );
			
		}
		
		return $category_id;
	
	}
	
	
	function get_category_thumbnail_object( $cat = '' ) {
		global $wp_taxonomies;
		
		if ( is_object( $cat ) )
			$cat_id = $cat->term_id;
		
		if ( is_numeric( $cat ) )
			$cat_id = (int) $cat;
		
		if ( '' == $cat )
			$cat_id = get_category( get_query_var( 'cat' ) )->term_id;
		
		
		$image = get_option( 'category_thumbnail_image' );
		
		if ( is_array( $image ) && array_key_exists( $cat_id, $image ) ) {
			$image = $image[ $cat_id ];
			$image = wp_get_attachment_image_src( (int) $image );
			
			$return = new stdClass;
			$return->url = $image[0];
			$return->width = $image[1];
			$return->height = $image[2];
			
			return $return;
		
		}
		
		return false;
		
	}
	
	function get_category_thumbnail( $cat = '' ) {
		$image = get_category_thumbnail_object( $cat );
		
		return sprintf( '<img src="%1$s" width="%2$s" height="%3$s">', $image->url, $image->width, $image->height );
		
	}
	
	
}

add_action( 'plugins_loaded', array( 'Category_Thumbnail', 'init' ) );


function get_category_thumbnail_object( $cat = '' ) {
	return Category_Thumbnail::get_category_thumbnail_object( $cat );
	
}


function get_category_thumbnail( $cat = '' ) {
	return Category_Thumbnail::get_category_thumbnail( $cat );
	
}

function the_category_thumbnail( $cat = '' ) {
	echo Category_Thumbnail::get_category_thumbnail( $cat );
	
}

