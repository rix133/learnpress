<?php

/**
 * Class LP_REST_Quiz_V1_Controller
 *
 * @since 3.x.x
 */
class LP_REST_Quiz_V1_Controller extends LP_REST_Course_V1_Controller {
	/**
	 * Current user for REST calling.
	 *
	 * @since 3.x.x
	 * @var LP_User
	 */
	protected $user = null;

	/**
	 * The course contain the question.
	 *
	 * @since 3.x.x
	 * @var LP_Course
	 */
	protected $course = null;

	/**
	 * The quiz contain question.
	 *
	 * @since 3.x.x
	 * @var LP_Quiz
	 */
	protected $quiz = null;

	/**
	 * LP_REST_Quiz_V1_Controller constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->namespace = 'learnpress/v1';
		$this->rest_base = 'quiz';
	}

	/**
	 * Register routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/retake',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'retake' ),
					'permission_callback' => array( $this, 'quiz_retake_permission_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Retake quiz.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_REST_Response
	 */
	public function retake( $request ) {
		$course_id = $this->course->get_id();
		$quiz_id   = $this->quiz->get_id();

		/**
		 * Filter to control action before retaking quiz
		 *
		 * @since 3.x.x
		 */
		$is_error = apply_filters( 'learn-press/rest/pre-retake-quiz', null, $quiz_id, $course_id, $this->user->get_id() );
		if ( is_wp_error( $is_error ) ) {
			return $is_error;
		}

		$this->user->retake_quiz( $quiz_id, $course_id );

		/**
		 * Fires after user retaken quiz.
		 *
		 * @since 3.x.x
		 */
		do_action( 'learn-press/rest/retake-quiz', $quiz_id, $course_id, $this->user->get_id() );

		$courseData = $this->user->get_course_data( $course_id );
		$quizData   = $courseData->get_item( $quiz_id );
		$quizData->reset();

		/**
		 * Allows modify response data.
		 *
		 * @since 3.x.x
		 */
		$result = apply_filters( 'learn-press/rest/retake-quiz-data', learn_press_get_quiz_data_json( $quiz_id, $course_id ) );

		return rest_ensure_response( array( 'quizData' => $result ) );
	}

	/**
	 * Check if course contain quiz.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error True if user have permission, WP_Error otherwise.
	 */
	public function quiz_exists_permissions_check( $request ) {

		if ( true !== ( $permission = $this->course_exists_permission_check( $request ) ) ) {
			return $permission;
		}

		$quizId = $request->get_param( 'itemId' );


		$quiz = $this->course->get_item( $quizId );
		if ( ! $quiz ) {
			return new WP_Error( 'rest_quiz_not_exists', __( 'Course not exists.', 'learnpress' ) );
		}

		$this->quiz = $quiz;

		return true;
	}

	/**
	 * Check user has already started quiz.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function quiz_started_permission_check( $request ) {

		if ( true !== $this->rest_permission_check() ) {
			return new WP_Error( 'rest_forbidden_access', __( 'Access forbidden.', 'learnpress' ) );
		}

		if ( true !== ( $permission = $this->quiz_exists_permissions_check( $request ) ) ) {
			return $permission;
		}

		$started = $this->user->get_quiz_status( $this->quiz->get_id(), $this->course->get_id() ) === 'started';

		if ( ! $started ) {
			return new WP_Error( 'rest_require_quiz_started', __( 'You have to start quiz.', 'learnpress' ) );
		}

		return true;
	}

	/**
	 * Check if user can retake quiz.
	 *
	 * @since 3.x.x
	 * @var WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function quiz_retake_permission_check( $request ) {

		if ( true !== $this->rest_permission_check() ) {
			return new WP_Error( 'rest_forbidden_access', __( 'Access forbidden.', 'learnpress' ) );
		}

		if ( true !== ( $permission = $this->quiz_exists_permissions_check( $request ) ) ) {
			return $permission;
		}

		$status = $this->user->get_quiz_status( $this->quiz->get_id(), $this->course->get_id() );

		if ( ! in_array( $status, array( 'completed' ) ) ) {
			return new WP_Error( 'rest_quiz_not_completed', __( 'You have to complete quiz.', 'learnpress' ) );
		}

		return true;
	}
}
