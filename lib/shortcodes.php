<?php
/*
Shortcode handlers
*/

// Handler for "testimonial" shortcode.
// This shortcode is used to output a single testimonial
function shortcode_testimonial ( $atts ) {
	
	if( !isset( $atts['id'] ) )
		return;
		
	$testimonial = new WP_Testimonial( $atts['id'] );
	$testimonial->render();

}
add_shortcode( 'testimonial', 'shortcode_testimonial' );

// Handler for "testimonials" shortcode.
// This shortcode is used to output multiple testimonials from a testimonial_category term
function shortcode_testimonials ( $atts ) {

	if( !isset( $atts['category'] ) )
		return;
		
	$category = get_term_by( 'id', $atts['category'], 'testimonial_category' );
	
	$args = array(
	
		'numberposts' => -1,
		'post_type' => 'testimonial',
		'testimonial_category' => $category->slug
	
	);
	
	if( $testimonials = get_posts( $args ) ) {
	
		foreach( $testimonials as &$testimonial ) {
		
			$testimonial = new WP_Testimonial( $testimonial->ID );
			$testimonial->render();
		
		}	
		
	}

}
add_shortcode( 'testimonials', 'shortcode_testimonials' );

// Handler for "testimonial-submission-form" shortcode
// This shortcode outputs a form which visitors can use to submit a testimonial
function shortcode_testimonial_submission ( $atts ) {

	if( isset( $_POST['testimonial-postback'] ) ):
	
		// Require WordPress core functions we require for file upload
		if( !function_exists( 'media_handle_upload' ) ) {
		
			require( ABSPATH . 'wp-admin/includes/image.php' );
			require( ABSPATH . 'wp-admin/includes/file.php' );
			require( ABSPATH . 'wp-admin/includes/media.php' );
			
		}
		
		// Build post array object
		$post = array(
			
			'ID' => NULL,
			'post_content' => apply_filters( 'the_content', $_POST['testimonial_description'] ),
			'post_name' => '',
			'post_type' => 'testimonial',
			'post_status' => 'draft',
			'post_title' => $_POST['testimonial_title']
		
		);
		
		// Insert new testimonial, if successful, update meta data
		if( $post_id = wp_insert_post( $post, false ) ) {
		
			update_post_meta( $post_id, 'testimonial_client_name', $_POST['testimonial_client_name'] );
			update_post_meta( $post_id, 'testimonial_client_company_name', $_POST['testimonial_client_company_name'] );
			update_post_meta( $post_id, 'testimonial_client_email', $_POST['testimonial_client_email'] );
			update_post_meta( $post_id, 'testimonial_client_company_website', $_POST['testimonial_client_company_website'] );
			
			if( !empty( $_FILES['thumbnail']['tmp_name'] ) )
				if( $attachment_id = media_handle_upload( 'thumbnail', $post_id ) )
					update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
			
			echo '<p>We successfully received your testimonial. If approved, it will appear on our website. Thank you!</p>';						
		
		}
		else {
		
			echo '<p class="error">Sorry, but there was a problem with submitting your testimonial. Please try again.</p>';
		
		}
	
	else:
	?>
	
	<form id="add-testimonial" enctype="multipart/form-data" name="add-testimonial" method="POST" action="<?php the_permalink(); ?>">
	
		<label for="testimonial_title">Testimonial Title (eg, &quot;I'm so super happy!&quot;)</label><br />
		<input type="text" name="testimonial_title" required="required"/><br />
		
		<label for="testimonial_description">Your Testimonial (be as descriptive as you like here!)</label><br />
		<textarea name="testimonial_description" rows="10" cols="20" required="required"></textarea><br />
		
		<label for="testimonial_client_name">Your Name</label><br />
		<input type="text" name="testimonial_client_name" required="required"/><br />
		
		<label for="testimonial_client_company_name">Company Name <em>(optional)</em></label><br />
		<input type="text" name="testimonial_client_company_name" /><br />
		
		<label for="testimonial_client_email">Your Email <em>(optional)</em></label><br />
		<input type="text" name="testimonial_client_email" /><br />
		
		<label for="testimonial_client_company_website">Your Website <em>(optional)</em></label><br />
		<input type="text" name="testimonial_client_company_website" /><br />
		
		<label for="thumbnail">Thumbnail <em>(optional)</em></label><br />
		<input type="file" name="thumbnail" /><br />
		
		<!-- hidden postback test field -->
		<input type="hidden" name="testimonial-postback" value="true" />
		
		<input type="submit" id="submit-testimonial" value="Submit Testimonial" />
	
	</form>
	
	<?php
	
	endif;
	
}
add_shortcode( 'testimonial-submission-form', 'shortcode_testimonial_submission' );

?>