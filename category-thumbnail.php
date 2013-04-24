<?php

add_action( 'admin_print_scripts-edit-tags.php', 'competisport_admin_print_scripts_edit_tags' );
add_action( 'admin_print_styles-edit-tags.php', 'competisport_admin_print_styles_edit_tags' );
add_action( 'admin_print_scripts-media-upload-popup', 'competisport_admin_print_scripts_media_upload' );
add_action( 'admin_print_styles-media-upload-popup', 'competisport_admin_print_styles_media_upload' );

add_action( 'product_categories_add_form_fields', 'competisport_product_categories_add', 10, 2);
add_action( 'product_categories_edit_form_fields', 'competisport_product_categories_edit', 10, 2);
add_action( 'edited_product_categories', 'competisport_product_categories_save', 10, 2);
add_action( 'save_product_categories', 'competisport_product_categories_save', 10, 2);



function competisport_admin_print_scripts_edit_tags() {
	
	/** Print scripts in edit-tags.php?taxonomy=product_categories **/
	if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'product_categories' ) {
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'edit-tags-product_categories', get_stylesheet_directory_uri(). '/js/edit-tags-product_categories.js', array( 'media-upload', 'thickbox' ), '1.0' );
		wp_localize_script( 'edit-tags-product_categories', 'edit_tags_product_categories', array(
			'link_remove_image' => __( 'Remover Imagem de Destaque' ),
			'link_add_image' => __( 'Inserir Imagem de Destaque' ),
			)
		);
	}
	
}




/**
 * Prints Style for edit-tags.php
 * @since 0.1
 */
function competisport_admin_print_styles_edit_tags() {
	
	/** Print scripts in edit-tags.php?taxonomy=product_categories **/
	if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'product_categories' ) {
		wp_enqueue_style( 'thickbox' );
		
	}
	
}




/**
 * Prints Scripts in Media Uploader Popup
 * @since 0.1
 */
function competisport_admin_print_scripts_media_upload() {
	
	/** Prints if this is opened by products_categories **/
	if ( isset( $_GET['source'] ) && $_GET['source'] == 'product_categories' ) {
		wp_enqueue_script( 'media-upload-product_categories', get_stylesheet_directory_uri(). '/js/media-upload-product_categories.js', array( 'jquery' ), '1.0' );
		wp_localize_script( 'media-upload-product_categories', 'media_upload_product_categories', array(
			'media_insert_val' => __( 'Usar como Imagem de Destaque' ),
			)
		);
	}
	
}




/**
 * Prints Styles in Media Uploader Popup
 * @since 0.1
 */
function competisport_admin_print_styles_media_upload() {
	
	/** Prints if this is opened by products_categories **/
	if ( isset( $_GET['source'] ) && $_GET['source'] == 'product_categories' ) {
		wp_enqueue_style( 'media-upload-product_categories', get_stylesheet_directory_uri(). '/css/media-upload-product_categories.css', null, '1.0' );
		
	}
	
}





/**
 * includes new custom fields in certain taxonomies
 * @since 0.1
 */
function competisport_product_categories_add( $tag ) {
?>

<div class="form-field hide-if-no-js">
	<p style="color:#222;font-style:normal;"><?php _e( 'Imagem de Destaque' ); ?></p>
	<div id="image-container">
		<a title="<?php _e( 'Usar como Imagem de Destaque' ); ?>" href="media-upload.php?type=image&amp;post_id=0&amp;source=<?php echo $tag; ?>&amp;TB_iframe=true&amp;width=640&amp;height=417" id="set-tag-thumbnail" class="thickbox">
			<?php _e( 'Usar como Imagem de Destaque' ); ?>
		</a>
	</div>
	<input name="image" id="image" type="hidden" value="" />
	<p>
		<?php printf( __( 'Adicionar Imagem de Destaque para esta %s.' ), __( 'Categoria' ) ); ?>
	</p>
</div>


<?php
	
}




/**
 * includes new custom fields in certain taxonomies
 * @since 0.1
 */
function competisport_product_categories_edit( $tag, $taxonomy ) {
	
	$image = get_option( 'product_categories_image' );
	
	if ( is_array( $image ) && array_key_exists( $tag->term_id, $image ) ) {
		$image = $image[$tag->term_id];
	
	} else {
		$image = false;
		
	}
?>
<tr class="form-field hide-if-no-js">
	<th scope="row" valign="top">
		<p style="color:#222;font-size:13px;"><?php _e( 'Imagem de Destaque' ); ?></p>
	</th>
	<td>
		<div id="image-container">
			<a title="<?php _e( 'Adicionar Imagem de Destaque' ); ?>" href="media-upload.php?type=image&amp;post_id=0&amp;source=<?php echo $taxonomy; ?>&amp;TB_iframe=true&amp;width=640&amp;height=417" id="set-tag-thumbnail" class="thickbox">
			<?php if ( ! empty( $image ) ) : ?>
				<img class="alignnone size-medium"src="<?php echo $image; ?>" />
			</a>
			<br>
			<a id="remove-post-thumbnail" href="#" onClick="javascript:return false;">
				<?php _e( 'Remover Imagem de Destaque' ); ?>	
			</a>
			<?php else : ?>
				<?php _e( 'Adicionar Imagem de Destaque' ); ?>
			</a>
			<?php endif; ?>
		
		</div>
		<input name="image" id="image" type="hidden" value="<?php echo $image; ?>" />
		<p class="description">
			<?php printf( __( 'Adicionar Imagem de Destaque para esta %s.' ), __( 'Categoria' ) ); ?>
		</p>
		
	</td>
</tr>
<?php
	
}



/**
 * Saves the new custom fields of certain taxonomies
 * @since 0.1
 */
function competisport_product_categories_save( $term_id ) {
	
	if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) or ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) )
		return true;
	
	
	if ( isset( $_POST['image'] ) ) {
		// Load existing option
		$image = get_option( 'product_categories_image' );
		
		// Set active taxonomy in array
		$image[$term_id] = $_POST['image'];
		
		// Update the option
		update_option( 'product_categories_image', $image );	
		
	}
	
	return $term_id;
	
}




