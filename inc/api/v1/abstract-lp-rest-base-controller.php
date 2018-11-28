<?php

/**
 * Class LP_REST_Base_V1_Controller
 *
 * @since 3.x.x
 */
abstract class LP_REST_Base_V1_Controller extends WP_REST_Controller {

	/**
	 * LP User object.
	 *
	 * @since 3.x.x
	 * @var LP_User null
	 */
	protected $user = null;

	/**
	 * LP_REST_Base_V1_Controller constructor.
	 */
	public function __construct() {
	}

	/**
	 * Wrap function rest_cookie_check_errors to ensure user is logged in.
	 *
	 * @since 3.x.x
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function rest_permission_check() {
		$result = rest_cookie_check_errors( null );

		$this->user = learn_press_get_current_user();

		return $result;
	}
}