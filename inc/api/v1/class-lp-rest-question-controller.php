<?php

/**
 * Class LP_REST_Question_V1_Controller
 *
 * @since 3.x.x
 */
class LP_REST_Question_V1_Controller extends LP_REST_Quiz_V1_Controller {

	/**
	 * LP Question object.
	 *
	 * @since 3.x.x
	 * @var null
	 */
	protected $question = null;

	/**
	 * LP_REST_Question_V1_Controller constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->namespace = 'learnpress/v1';
		$this->rest_base = 'question';
	}

	/**
	 * Register routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/check',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'check' ),
					'permission_callback' => array( $this, 'quiz_started_permission_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Check the question is correct/wrong.
	 *
	 * @since 3.x.x
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function check( $request ) {

		$user       = learn_press_get_current_user();
		$course     = $this->course;
		$quiz       = $course->get_item( $request->get_param( 'itemId' ) );
		$questionId = $request->get_param( 'questionId' );
		$question   = learn_press_get_question( $questionId );

		// Check if quiz enable check-answer
		if ( $quiz->get_show_check_answer() == 0 ) {
			return new WP_Error( 'rest_quiz_check_answer_disabled', __( 'Check answer is disabled.', 'learnpress' ), array( 'status' => 400 ) );
		}

		// If user is not enrolled course or has completed
		$course_data = $user->get_course_data( $course->get_id() );
		if ( ! $course_data || 'enrolled' !== $course_data->get_status() ) {
			return new WP_Error( 'rest_check_answer_course_not_enrolled', __( 'Course is not enrolled.', 'learnpress' ), array( 'status' => 400 ) );
		}

		// If user is not started quiz or has completed
		$quiz_data = $course_data->get_item( $quiz->get_id() );
		if ( ! $quiz_data || 'started' !== $quiz_data->get_status() ) {
			return new WP_Error( 'rest_check_answer_quiz_not_started', __( 'Quiz is not started.', 'learnpress' ), array( 'status' => 400 ) );
		}

		// The user has already checked question
		if ( $user->has_checked_question( $questionId, $quiz->get_id(), $course->get_id() ) ) {
			return new WP_Error( 'rest_answer_checked', __( 'User checked this question.', 'learnpress' ), array( 'status' => 400 ) );
		}

		/**
		 * There is no more checks
		 */
		if ( ! $user->can_check_answer( $quiz->get_id(), $course->get_id() ) ) {
			return new WP_Error( 'rest_check_answer_limit', __( 'Check answer are limited.', 'learnpress' ), array( 'status' => 400 ) );
		}

		// Update answers posted
		$quiz_data->add_question_answer( array( $questionId => LP_Request::get( 'answers' ) ) );
		$quiz_data->update();

		// Setup data
		$question->setup_data( $quiz->get_id(), $course->get_id(), $user->get_id() );
		$user->check_question( $questionId, $quiz->get_id(), $course->get_id() );
		$checked = $user->has_checked_question( $questionId, $quiz->get_id(), $course->get_id() );

		// Prepare response data
		$response = array(
			'checkCount' => $quiz_data->can_check_answer(),
			'hintCount'  => $quiz_data->can_hint_answer(),
			'questions'  => array(
				$questionId => array(
					'checked' => $checked,
					'answers' => $quiz_data->get_question_answer( $questionId )
				)
			)
		);

		if ( $checked ) {

			$response['questions'][ $questionId ] = array_merge( $response['questions'][ $questionId ], array(
				'explanation'   => $question->get_explanation(),
				'hint'          => $question->get_hint(),
				'optionAnswers' => array_values( $quiz_data->get_answer_options( $questionId ) )
			) );

		}

		/**
		 * Allow modify response data.
		 *
		 * @since 3.x.x
		 */
		$response = apply_filters( 'learn-press/rest/check-answer-response', $response, $questionId, $quiz->get_id(), $course->get_id(), $user->get_id(), $response );

		return rest_ensure_response( $response );
	}

//	/**
//	 * Check if course contain quiz.
//	 *
//	 * @since 3.x.x
//	 *
//	 * @param WP_REST_Request $request
//	 *
//	 * @return bool|WP_Error True if user have permission, WP_Error otherwise.
//	 */
//	public function general_permissions_check( $request ) {
//		$courseId = $request->get_param( 'courseId' );
//		$quizId   = $request->get_param( 'itemId' );
//
//		$result = array();
//
//		rest_cookie_check_errors( $result );
//
//		$user = learn_press_get_current_user();
//		if ( $user->is_guest() ) {
//			return new WP_Error( 'rest_guest_disabled', __( 'Guest is now allowed.', 'learnpress' ) );
//		}
//
//		$course = learn_press_get_course( $courseId );
//		if ( ! $course || ! $course->is_publish() || ! $course->is_exists() ) {
//			return new WP_Error( 'rest_course_not_exists', __( 'Course not exists.', 'learnpress' ) );
//		}
//
//		$quiz = $course->get_item( $quizId );
//		if ( ! $quiz ) {
//			return new WP_Error( 'rest_quiz_not_exists', __( 'Course not exists.', 'learnpress' ) );
//		}
//
//		$this->user   = $user;
//		$this->course = $course;
//		$this->quiz   = $quiz;
//
//		return true;
//	}
//
//	/**
//	 * Check user has already started quiz.
//	 *
//	 * @since 3.x.x
//	 *
//	 * @param WP_REST_Request $request
//	 *
//	 * @return bool|WP_Error
//	 */
//	public function quiz_started_permission_check( $request ) {
//
//		if ( ! $this->general_permissions_check( $request ) ) {
//			return new WP_Error( 'rest_forbidden_access', __( 'Access forbidden.', 'learnpress' ) );
//		}
//
//		$stated = $this->user->get_quiz_status( $this->quiz->get_id(), $this->course->get_id() ) === 'started';
//
//		if ( ! $stated ) {
//			return new WP_Error( 'rest_require_quiz_started', __( 'You have to start quiz.', 'learnpress' ) );
//		}
//
//		return true;
//	}
}
