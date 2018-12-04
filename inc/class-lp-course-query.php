<?php

/**
 * Class LP_Course_Query
 *
 * @since 3.x.x
 */
class LP_Course_Query extends LP_Object_Query {

	/**
	 * @return WP_Query
	 */
	public function get_courses() {
		return new WP_Query( $this->get_query_vars() );
	}

	/**
	 * @return array
	 */
	protected function get_default_query_args() {
		return array_merge(
			parent::get_default_query_args(),
			array(
				'post_type' => LP_COURSE_CPT,
				'status'    => array( 'draft', 'pending', 'private', 'publish' )
			)
		);
	}
}

// Old version
class LP_Query_Course extends LP_Course_Query {
	public function __construct( $query = '' ) {
		parent::__construct( $query );
	}
}