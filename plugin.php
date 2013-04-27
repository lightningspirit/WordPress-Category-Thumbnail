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


/*
 * @package Category Thumbnail
 * @author lightningspirit
 * @copyright lightningspirit 2013
 * This code is released under the GPL licence version 2 or later
 * http://www.gnu.org/licenses/gpl.txt
 */



// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}




if ( ! class_exists ( 'Category_Thumbnail' ) ) :
/**
 * Category_Thumbnail
 *
 * @package WordPress
 * @subpackage Category Thumbnail
 * @since 1.0
 */
class Category_Thumbnail {
	
	/** 
	 * @since 1.0
	 */
	function init() {
		// Load the text domain to support translations
		load_plugin_textdomain( 'category-thumbnail', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		// Just to be parsed by gettext
		$plugin_headers = array(
			__( 'Category Thumbnail', 'category-thumbnail' ).
			__( 'Add thumbnail feature to categories', 'category-thumbnail' )
		);
		
		// if new upgrade
		if ( version_compare( (int) get_option( 'category_thumbnail_plugin_version' ), '1.0', '<' ) )
			add_action( 'admin_init', array( __CLASS__, 'do_upgrade' ) );
		
		
		add_action( 'admin_head', array( __CLASS__, 'admin_head' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_media_scripts' ), 10, 2 );
		add_filter( 'manage_edit-category_columns', array( __CLASS__, 'manage_edit_columns' ) );
		add_filter( 'manage_category_custom_column', array( __CLASS__, 'manage_category_custom_column' ), 10, 3 );
		
		add_action( 'category_add_form_fields', array( __CLASS__, 'category_add_thumbnail_field' ), 10, 2 );
		add_action( 'category_edit_form_fields', array( __CLASS__, 'category_edit_thumbnail_field' ), 10, 2 );
		add_action( 'edited_category', array( __CLASS__, 'save_category' ), 10, 2 );
		add_action( 'created_category', array( __CLASS__, 'save_category' ), 10, 2 );
		
	}
	
	/** 
	 * @since 1.0
	 */
	public static function do_upgrade() {
		update_option( 'category_thumbnail_plugin_version', '1.0' );
		
	}
	
	/** 
	 * @since 1.0
	 */
	function admin_head() {
		if ( 'edit-category' == get_current_screen()->id ) : ?>
			
<style type="text/css">
.manage-column.column-thumbnail { width: 8%; }
.widefat .thumbnail.column-thumbnail { overflow: visible; }
.widefat .thumbnail.column-thumbnail img { max-width: 50px; max-height: 50px; }
</style>
		
		<?php endif;
		
	}
	
	/** 
	 * @since 1.0
	 */
	function admin_enqueue_media_scripts() {
		if ( 'edit-category' == get_current_screen()->id ) {
			wp_enqueue_media();
			wp_enqueue_script( 'category-thumbnail', plugin_dir_url( __FILE__ ) . '/assets/category-thumbnail.js', array( 'jquery' ), '2013042702', true );
			
		}
		
	}
	
	/** 
	 * @since 1.0
	 */
	function manage_edit_columns( $columns ) {
		$keys = array_keys( $columns );
		$values = array_values( $columns );
		
		array_splice( $keys, 1, 0, array( 'thumbnail' ) );
		array_splice( $values, 1, 0, array( '' ) );
		
		return array_combine( $keys, $values );
		
	}
	
	/** 
	 * @since 1.0
	 */
	function manage_category_custom_column( $null, $column, $cat_id ) {
		
		if ( 'thumbnail' == $column ) {
			return get_category_thumbnail( $cat_id );
			
		}
		
	}
	
	
	/**
	 * Includes thumbnail category in add
	 * 
	 * @since 1.0
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
				<?php printf( __( 'The thumbnail to this %s', 'category-thumbnail' ), $wp_taxonomies[ $category ]->labels->singular_name ); ?>
			</p>
		</div>
		
		<?php
		
	}
	
	
	/**
	 * Includes thumbnail category in edit
	 * 
	 * @since 1.0
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
				<?php printf( __( 'The thumbnail to this %s', 'category-thumbnail' ), $wp_taxonomies[ $taxonomy ]->labels->singular_name ); ?>
			</p>
			
		</td>
	</tr>
	<?php
		
	}
	
	/**
	 * Save image ID
	 * 
	 * @since 1.0
	 * 
	 */
	function save_category( $category_id, $taxonomy_id ) {
		
		// Load existing option
		$image = get_option( 'category_thumbnail_image' );
			
		if ( isset( $_REQUEST['image'] ) ) {
			// Set active taxonomy in array
			$image[ $category_id ] = (int) $_REQUEST['image'];
			
			// Update the option
			update_option( 'category_thumbnail_image', $image );
			
		} elseif ( array_key_exists( $category_id, $image ) ) {
			// Remove image from option
			unset( $image[ $category_id ] );
			
			// Update the option
			update_option( 'category_thumbnail_image', $image );
			
		}
		
		return $category_id;
	
	}
	
	/**
	 * Get the thumbnail object for a given category object or ID
	 * 
	 * @param int|object $cat the category ID/object
	 * @return object|bool Object or false
	 * 
	 */
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
	
	/**
	 * Get the thumbnail HTML string for a given category object or ID
	 * 
	 * @param int|object $cat the category ID/object
	 * @param array $args attributes to image like 'class', 'style', 'title', ...
	 * @return string the HTML
	 */
	function get_category_thumbnail( $cat = '', $args = '' ) {
		$image = get_category_thumbnail_object( $cat );
		
		if ( function_exists( 'wp_parse_attrs' ) ) {
			$attrs = wp_parse_attrs( $args, array(
				'class' => 'attachment-tag-thumbnail wp-post-image',
			) );
			
		} else {
			$attrs = wp_parse_args( $args, array(
				'class' => 'attachment-tag-thumbnail wp-post-image',
			) );
			foreach ( $attrs as $attr => $value )
				$attrs[ $attr ] = "{$attr}=\"{$value}\"";
			
			$attrs = implode( ' ', $attrs );
		}
		
		if ( is_object( $image ) ) {
			return sprintf( '<img src="%1$s" width="%2$s" height="%3$s" %4$s>', 
				$image->url, $image->width, $image->height, $attrs
			);
			
		}
		
		return;
		
	}
	
	/**
	 * Evaluate if the current given category has thumbnail
	 * 
	 * @param int|object $cat the category ID/object
	 * @return bool
	 * 
	 */
	function has_category_thumbnail( $cat = '' ) {
		global $wp_taxonomies;
		
		if ( is_object( $cat ) )
			$cat_id = $cat->term_id;
		
		if ( is_numeric( $cat ) )
			$cat_id = (int) $cat;
		
		if ( '' == $cat )
			$cat_id = get_category( get_query_var( 'cat' ) )->term_id;
		
		
		$image = get_option( 'category_thumbnail_image' );
		
		if ( is_array( $image ) && array_key_exists( $cat_id, $image ) )
			return true;
		
		return false;
		
	}
	
	
}

add_action( 'plugins_loaded', array( 'Category_Thumbnail', 'init' ) );

/**
 * Get the thumbnail object for a given category object or ID
 * 
 * @param int|object $cat the category ID/object
 * @return object|bool Object or false
 * 
 */
function get_category_thumbnail_object( $cat = '' ) {
	return Category_Thumbnail::get_category_thumbnail_object( $cat );
	
}

/**
 * Get the thumbnail HTML string for a given category object or ID
 * 
 * @param int|object $cat the category ID/object
 * @param array $args attributes to image like 'class', 'style', 'title', ...
 * @return string the HTML
 * 
 */
function get_category_thumbnail( $cat = '', $args = '' ) {
	return Category_Thumbnail::get_category_thumbnail( $cat, $args );
	
}

/**
 * Display the thumbnail HTML string for a given category object or ID
 * 
 * @param int|object $cat the category ID/object
 * @param array $args attributes to image like 'class', 'style', 'title', ...
 * 
 */
function the_category_thumbnail( $cat = '', $args = '' ) {
	echo Category_Thumbnail::get_category_thumbnail( $cat, $args );
	
}

/**
 * Evaluate if the current given category has thumbnail
 * 
 * @param int|object $cat the category ID/object
 * @return bool
 * 
 */
function has_category_thumbnail( $cat = '' ) {
	echo Category_Thumbnail::has_category_thumbnail( $cat );
	
}


/**
 * Register activation hook for plugin
 * 
 * @since 0.1
 */
function category_thumbnail_plugin_activation_hook() {
	// Wordpress version control. No compatibility with older versions. ( wp_die )
	if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
		wp_die( 'Category Thumbnail is not compatible with versions prior to 3.5' );

	}

}
register_activation_hook( __FILE__, 'category_thumbnail_plugin_activation_hook' );

endif;
