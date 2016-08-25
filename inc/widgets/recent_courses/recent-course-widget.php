<?php
/**
 * Widget to display recent courses
 */

defined( 'ABSPATH' ) || exit();

include LP_PLUGIN_PATH . 'inc/widgets/lp-widget-util.php';

/**
 * @class LP_Recent_Course_Widget
 * Widget to display recent courses
 */
class LP_Recent_Course_Widget extends WP_Widget {
    /**
     * @var null|string widget location
     */
    private $widget_path = null;

    /**
     * @var string $template_dir template folder location
     */
    private $template_dir = null;

    /**
     * @var tring $template_file_name template filename
     */
    private $template_file_name = null;

    /**
     * @var [array] $templates null
     * template list
     */
    private $templates = null;

    /**
     * @var array|null default instance
     */
    private $default_attributes = null;

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'recent_course_widget',
            'description' => 'Display most recent courses',
        );

        $this->widget_path = LP_PLUGIN_PATH . '/inc/widgets/recent_courses';

        $this->template_dir = $this->widget_path . '/templates/';

        $this->template_file_name = 'default.php';

        $this->templates = scandir ( $this->template_dir );
        $this->templates = array_filter($this->templates, 'lp_php_file_filter');
        array_walk($this->templates, 'lp_trim_file_extension');

        $this->default_attributes = array(
            'show_teacher' => 'true',
            'show_lesson' => 'true',
            'show_thumbnail' => 'true',
            'limit' => '5',
            'title' => 'Recent courses',
            'show_desc' => '',
            'desc_length' => '10',
            'show_enrolled_students' => 'true',
            'show_price' => '',
            'template' => 'default',
            'css_class' => '',
            'bottom_link' => '',
            'bottom_link_text' => ''
        );

        parent::__construct( 'recent_course_widget', __('Recent Courses', 'leanpress'), $widget_ops );
    }

    /**
     * get template file name from options
     * @param WP_Widget $instance widget instance
     * @return string Template filename
     */
    private function get_template_file(&$instance){
        $template_file_name = $instance['template'] . '.php';
        if( ! file_exists( $this->template_dir . $template_file_name ) ){
            $template_file_name = 'default.php';
        }
        $this->template_file_name = $template_file_name;

        return $template_file_name;
    }

    /**
     * render widget template
     * @param array $courses array of course
     * @param array widget args
     * @param WP_Widget $instance  widget instance
     */
    private function render($courses, $args, $a){
        //include template file
        include $this->template_dir . $this->template_file_name;
    }

    /**
     * get learn press course from wordpress post object
     * @param object-reference $post wordpress post object
     * @return LP_Course course
     */
    public function get_lp_course($post){
        $id = $post->ID;
        $course = null;
        if( !empty($id) ){
            $course = new LP_Course($id);
        }

        return $course;
    }

    /**
     * get courses
     * @return array|null array of course
     */
    private function get_courses($instance){
        global $wpdb;
        $posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT ID FROM $wpdb->posts AS p
        			WHERE p.post_type = %s
		        	AND p.post_status = %s
			        ORDER BY p.post_date DESC
			        LIMIT %d",
                LP()->course_post_type,
                'publish',
                (int)$instance['limit']
            )
        );
        $courses = array_map(array($this, 'get_lp_course'), $posts );
        return $courses;
    }


    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        $instance = wp_parse_args($instance, $this->default_attributes);
        $this->get_template_file($instance);
        $courses = $this->get_courses($instance);
        
        echo $args['before_widget'];
        $this->render($courses, $args ,$instance);
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     * @return void
     */
    public function form( $instance ) {
        $instance = wp_parse_args( ( array ) $instance, $this->default_attributes);
        require $this->widget_path . '/form.php';
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     * @return array  widget instance to save to db
     */
    public function update( $new_instance, $old_instance ) {
        $instance = wp_parse_args($new_instance, $this->default_attributes);
        
        return $instance;
    }
}

// register widget
function register_recent_course_widget() {
    register_widget( 'LP_Recent_Course_Widget' );
}
add_action( 'lp_widgets_init', 'register_recent_course_widget' );