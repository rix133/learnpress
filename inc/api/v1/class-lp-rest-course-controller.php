<?php

/**
 * Class LP_REST_Course_V1_Controller
 *
 * @since 3.x.x
 */
class LP_REST_Course_V1_Controller extends LP_REST_Base_V1_Controller {

	/**
	 * LP Course object.
	 *
	 * @since 3.x.x
	 *
	 * @var LP_Course
	 */
	protected $course = null;

	/**
	 * LP_REST_Course_V1_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'learnpress/v1';
		$this->rest_base = 'course';
	}

	/**
	 * Check course is exists and available.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function course_exists_permission_check( $request ) {
		$courseId = $request->get_param( 'courseId' );

		$course = learn_press_get_course( $courseId );
		if ( ! $course || ! $course->is_publish() || ! $course->is_exists() ) {
			return new WP_Error( 'rest_course_not_exists', __( 'Course not exists.', 'learnpress' ) );
		}

		$this->course = $course;

		return true;
	}

	/**
	 * General permission check to ensure user is logged in and
	 * course is available.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function general_permission_check( $request ) {
		return $this->rest_permission_check() === true && $this->course_exists_permission_check( $request ) === true;
	}

	/**
	 * Checks if user is enrolled course.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function course_enrolled_permission_check( $request ) {
		if ( true !== ( $permission = $this->general_permission_check( $request ) ) ) {
			return $permission;
		}

		return $this->user->get_course_status( $this->course->get_id() ) === 'enrolled';
	}
}