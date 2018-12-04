<?php

/**
 * Class LP_REST_User_V1_Controller
 *
 * @since 3.x.x
 */
class LP_REST_User_V1_Controller extends LP_REST_Base_V1_Controller {

	/**
	 * @since 3.x.x
	 *
	 * @var LP_User
	 */
	protected $user = null;

	/**
	 * LP_REST_User_V1_Controller constructor.
	 */
	public function __construct() {

//		if ( ! is_user_logged_in() ) {
//			if ( isset( $_REQUEST['_wpnonce'] ) ) {
//				unset( $_REQUEST['_wpnonce'] );
//			} elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
//				unset( $_SERVER['HTTP_X_WP_NONCE'] );
//			}
//		}

		$this->namespace = 'learnpress/v1';
		$this->rest_base = 'user';
	}

	/**
	 * Register routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/login',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'login' ),
					'permission_callback' => array( $this, 'guest_permission_check' ),
					'args'                => $this->get_collection_params(),
				)
			)
		);
	}

	/**
	 * REST api for login.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function login( $request ) {

		$logged = wp_signon( array(
			'user_login'    => $request->get_param( 'username' ),
			'user_password' => $request->get_param( 'password' ),
			'rememberme'    => $request->get_param( 'rememberme' )
		) );

		$response = array();

		if ( is_wp_error( $logged ) ) {
			return $logged;
		}

		$response['result']  = 'success';
		$response['message'] = __( 'Logged in successful!', 'learnpress' );

		/**
		 * Allows to modify results
		 */
		$response = apply_filters( 'learn-press/rest/login-results', $response );

		return rest_ensure_response( $response );
	}

	/**
	 * Check to ensure user is not logged in.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function guest_permission_check( $request ) {
		if ( ! LP_Request::verify_nonce( 'learn-press-login' ) ) {
			return new WP_Error( 'bad_request', __( 'Bad request.', 'learnpress' ) );
		}

		return ! is_user_logged_in();
	}
}