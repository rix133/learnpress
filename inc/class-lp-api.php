<?php

/**
 * LearnPress API
 *
 * @since 3.x.x
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class LP_API
 */
class LP_API {

	protected $endpoint = 'lp-api';

	protected $request_version = '';

	/**
	 * LP_API constructor.
	 */
	public function __construct() {
		// Add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Add endpoint
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );

		// Parse request
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );

		add_filter( 'rest_pre_echo_response', array( $this, 'pre_echo_response' ), 10, 3 );

		$this->init();
	}

	/**
	 * Print signature before response json.
	 *
	 * @param array           $result
	 * @param WP_REST_Server  $server
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function pre_echo_response( $result, $server, $request ) {
		if ( 1 == $request->get_header( 'x_learnpress' ) ) {
			echo '<!-- LP_REST_API_RESPONSE -->';
		}

		return $result;
	}

	/**
	 * @since 3.x.x
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = $this->endpoint;

		return $vars;
	}

	/**
	 * Register endpoint for Rest API.
	 *
	 * @since 3.x.x
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( $this->endpoint, EP_ALL );
	}

	/**
	 *
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET[ $this->endpoint ] ) ) {
			$wp->query_vars[ $this->endpoint ] = sanitize_key( wp_unslash( $_GET[ $this->endpoint ] ) );
		}

		if ( ! empty( $wp->query_vars[ $this->endpoint ] ) ) {

			ob_start();

			nocache_headers();

			$api_request = strtolower( sanitize_text_field( $wp->query_vars[ $this->endpoint ] ) );

			do_action( 'learn-press/api-request', $api_request );

			status_header( has_action( 'learn-press/api-' . $api_request ) ? 200 : 400 );

			do_action( 'learn-press/api-' . $api_request );

			ob_end_clean();
			die( '-1' );
		}
	}

	/**
	 * Register routes for rest.
	 *
	 * @since 3.x.x
	 */
	public function register_rest_routes() {
		global $wp;

		if ( ! $this->request_version ) {
			if ( ! empty( $wp->query_vars['rest_route'] ) ) {
				if ( ! preg_match( '!/learnpress/(v[1-3]{1})(/?.*)!', $wp->query_vars['rest_route'], $m ) ) {
					return;
				}

				$this->request_version = $m[1];
			}
		}

		if ( ! $this->request_version ) {
			return;
		}

		$this->include_version( $this->request_version );
	}

	/**
	 * Include classes for rest.
	 *
	 * @since 3.x.x
	 */
	protected function includes() {
		global $wp;

		if ( preg_match( '!/' . $this->endpoint . '/(v[1-3]{1})(/?.*)!', $_SERVER['REQUEST_URI'], $m ) ) {

			$this->request_version = $m[1];

			$this->include_version( $m[1] );
		}
	}

	protected function get_rest_controllers( $version ) {
		$root = dirname( __FILE__ );

		return array(
			$root . "/api/{$version}/abstract-lp-rest-base-controller.php",
			$root . "/api/{$version}/class-lp-rest-course-controller.php",
			$root . "/api/{$version}/class-lp-rest-quiz-controller.php",
			$root . "/api/{$version}/class-lp-rest-question-controller.php",
		);
	}

	protected function include_version( $version ) {

		if ( ! $rest_controllers = $this->get_rest_controllers( $version ) ) {
			return;
		}

		foreach ( $rest_controllers as $controller ) {
			include_once $controller;
		}

		$v = strtoupper( $this->request_version );

		$controllers = array(
			"LP_REST_Course_{$v}_Controller",
			"LP_REST_Quiz_{$v}_Controller",
			"LP_REST_Question_{$v}_Controller",
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller;
			$this->$controller->register_routes();
		}

		var_dump( $this );
	}

	/**
	 * Init
	 *
	 * @since 3.x.x
	 */
	protected function init() {
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->includes();
		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}
}