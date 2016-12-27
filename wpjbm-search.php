<?php
/**
 * Plugin Name: WPJBM search

 * Description: This plugin adds salery field frontend and backend and ads salary field to search in WP Job Manager
 * Author: Drazen Duvnjak
 * Version: 1.0.0
 * License: GPL2
 */
 
 
 add_filter( 'submit_job_form_fields', 'frontend_add_salary_field' );
function frontend_add_salary_field( $fields ) {
  $fields['job']['job_salary'] = array(
    'label'       => __( 'Salary ($)', 'job_manager' ),
    'type'        => 'text',
    'required'    => true,
    'placeholder' => 'e.g. 20000',
    'priority'    => 7
  );
  return $fields;
}
add_filter( 'job_manager_job_listing_data_fields', 'admin_add_salary_field' );
function admin_add_salary_field( $fields ) {
  $fields['_job_salary'] = array(
    'label'       => __( 'Salary ($)', 'job_manager' ),
    'type'        => 'text',
    'placeholder' => 'e.g. 20000',
    'description' => ''
  );
  return $fields;
}
add_action( 'single_job_listing_meta_end', 'display_job_salary_data' );
function display_job_salary_data() {
  global $post;

  $salary = get_post_meta( $post->ID, '_job_salary', true );

  if ( $salary ) {
    echo '<li>' . __( 'Salary:' ) . ' $' . esc_html( $salary ) . '</li>';
  }
}
 
 

add_action( 'job_manager_job_filters_search_jobs_end', 'filter_by_salary_field' );

function filter_by_salary_field() {
	?>
	<div class="search_categories">
		<label for="search_categories"><?php _e( 'Salary', 'wp-job-manager' ); ?></label>
		<select name="filter_by_salary" class="job-manager-filter">
			<option value=""><?php _e( 'Any Salary', 'wp-job-manager' ); ?></option>
			<option value="upto20"><?php _e( 'Up to $20,000', 'wp-job-manager' ); ?></option>
			<option value="20000-40000"><?php _e( '$20,000 to $40,000', 'wp-job-manager' ); ?></option>
			<option value="40000-60000"><?php _e( '$40,000 to $60,000', 'wp-job-manager' ); ?></option>
			<option value="over60"><?php _e( '$60,000+', 'wp-job-manager' ); ?></option>
		</select>
	</div>
	<?php
}


add_filter( 'job_manager_get_listings', 'filter_by_salary_field_query_args', 10, 2 );

function filter_by_salary_field_query_args( $query_args, $args ) {
	if ( isset( $_POST['form_data'] ) ) {
		parse_str( $_POST['form_data'], $form_data );

		
		if ( ! empty( $form_data['filter_by_salary'] ) ) {
			$selected_range = sanitize_text_field( $form_data['filter_by_salary'] );
			switch ( $selected_range ) {
				case 'upto20' :
					$query_args['meta_query'][] = array(
						'key'     => '_job_salary',
						'value'   => '20000',
						'compare' => '<',
						'type'    => 'NUMERIC'
					);
				break;
				case 'over60' :
					$query_args['meta_query'][] = array(
						'key'     => '_job_salary',
						'value'   => '60000',
						'compare' => '>=',
						'type'    => 'NUMERIC'
					);
				break;
				default :
					$query_args['meta_query'][] = array(
						'key'     => '_job_salary',
						'value'   => array_map( 'absint', explode( '-', $selected_range ) ),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC'
					);
				break;
			}

			
			add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
		}
	}
	return $query_args;
}