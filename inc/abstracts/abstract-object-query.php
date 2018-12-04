<?php

/**
 * Class LP_Object_Query
 *
 * @since 3.x.x
 */
abstract class LP_Object_Query {

	/**
	 * Query args.
	 *
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * LP_Object_Query constructor.
	 *
	 * @param string $query
	 */
	public function __construct( $query = '' ) {

		$this->query_vars = wp_parse_args( $query, $this->get_default_query_args() );
	}

	/**
	 * Get all query vars.
	 *
	 * @since 3.x.x
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return $this->query_vars;
	}

	/**
	 * Get an variable from query vars.
	 *
	 * @param string $var
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public function get( $var, $default = '' ) {
		return ! empty( $this->query_vars[ $var ] ) ? $this->query_vars[ $var ] : $default;
	}

	/**
	 * Set value for a query var.
	 *
	 * @since 3.x.x
	 *
	 * @param string $var
	 * @param mixed  $value
	 */
	public function set( $var, $value ) {
		$this->query_vars[ $var ] = $value;
	}

	/**
	 * Get default query args.
	 *
	 * @since 3.x.x
	 *
	 * @return array
	 */
	protected function get_default_query_args() {
		return array(
			'name'           => '',
			'parent'         => '',
			'parent_exclude' => '',
			'exclude'        => '',

			'limit'          => get_option( 'posts_per_page' ),
			'page'           => 1,
			'offset'         => '',
			'paginate'       => false,

			'order'          => 'DESC',
			'orderby'        => 'date',

			'return'         => 'objects',
		);
	}
}