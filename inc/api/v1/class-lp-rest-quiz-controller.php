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
			'/' . $this->rest_base . '/start',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'start' ),
					'permission_callback' => array( $this, 'quiz_start_permission_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/complete',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'complete' ),
					'permission_callback' => array( $this, 'quiz_started_permission_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

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
	 * Start quiz.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_REST_Response
	 */
	public function start( $request ) {
		$course_id = $this->course->get_id();
		$quiz_id   = $this->quiz->get_id();

		/**
		 * Filter to control action before retaking quiz
		 *
		 * @since 3.x.x
		 */
		$is_error = apply_filters( 'learn-press/rest/pre-start-quiz', null, $quiz_id, $course_id, $this->user->get_id() );
		if ( is_wp_error( $is_error ) ) {
			return $is_error;
		}

		$this->user->start_quiz( $quiz_id, $course_id );

		/**
		 * Fires after user retaken quiz.
		 *
		 * @since 3.x.x
		 */
		do_action( 'learn-press/rest/start-quiz', $quiz_id, $course_id, $this->user->get_id() );

		$courseData = $this->user->get_course_data( $course_id );
		$quizData   = $courseData->get_item( $quiz_id );
		$quizData->reset();

		/**
		 * Allows modify response data.
		 *
		 * @since 3.x.x
		 */
		$result = apply_filters( 'learn-press/rest/start-quiz-data', learn_press_get_quiz_data_json( $quiz_id, $course_id ) );

		return rest_ensure_response( array( 'quizData' => $result ) );
	}

	/**
	 * Complete quiz.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return  array|WP_REST_Response
	 */
	public function complete( $request ) {

		$user   = $this->user;
		$course = $this->course;
		$quiz   = $this->quiz;

		$quiz->set_course( $course->get_id() );
		$course_data = $user->get_course_data( $course->get_id() );
		$questions   = $request->get_param( 'answers' );
		$response    = array();

		if ( $quiz_data = $course_data->get_item_quiz( $quiz->get_id() ) ) {

			$quiz_data->update_meta( '_time_spend', LP_Request::get( 'timeSpend' ) );
			$quiz_data->add_question_answer( $questions );
			$quiz_data->update();
			$quiz_data->complete();

			// Prevent caching...
			$user_item_curd = new LP_User_Item_CURD();
			$user_item_curd->parse_items_classes( $course->get_id(), $user->get_id() );
		}

		$quizDataResults = array(
			'completed' => $quiz_data->is_completed(),
			'status'    => $quiz_data->get_status(),
			'results'   => $quiz_data->calculate_results(),
			'classes'   => array_values( $quiz->get_class( '', $course->get_id(), $user->get_id() ) )
		);

		$response['quiz']   = $quizDataResults;
		$response['course'] = array( 'results' => $course_data->get_percent_result() );

		$response = apply_filters( 'learn-press/rest/complete-quiz-data', $response, $quiz->get_id(), $course->get_id(), $user->get_id() );

		//LP_Notifications::instance()->add( 'You have completed quiz' );

		return rest_ensure_response( $response );
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

		// Prevent caching...
		$user_item_curd = new LP_User_Item_CURD();
		$user_item_curd->parse_items_classes( $course_id, $this->user->get_id() );

		/**
		 * Allows modify response data.
		 *
		 * @since 3.x.x
		 */
		$result = apply_filters( 'learn-press/rest/retake-quiz-data', learn_press_get_quiz_data_json( $quiz_id, $course_id ) );

		return rest_ensure_response( array( 'quiz' => $result, 'results' => $courseData->calculate_results() ) );
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

		if ( true !== $this->course_enrolled_permission_check( $request ) ) {
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
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function quiz_retake_permission_check( $request ) {

		if ( true !== $this->course_enrolled_permission_check( $request ) ) {
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

	/**
	 * Checks to ensure user did not started/completed quiz.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function quiz_start_permission_check( $request ) {


		if ( true !== $this->course_enrolled_permission_check( $request ) ) {
			return new WP_Error( 'rest_forbidden_access', __( 'Access forbidden.', 'learnpress' ) );
		}

		if ( true !== ( $permission = $this->quiz_exists_permissions_check( $request ) ) ) {
			return $permission;
		}

		$status = $this->user->get_quiz_status( $this->quiz->get_id(), $this->course->get_id() );

		if ( in_array( $status, array( 'started', 'completed' ) ) ) {
			return new WP_Error( 'rest_quiz_started_or_completed', __( 'You\'ve started or completed quiz.', 'learnpress' ) );
		}

		return true;
	}
}
