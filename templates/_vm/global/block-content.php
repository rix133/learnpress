<?php
/**
 * Template for display login/register form or enroll button.
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 3.x.x
 */

defined( 'ABSPATH' ) or die();
?>

<?php if ( ! is_user_logged_in() ) { ?>
    <course-item-login-register-form inline-template>
        <div id="course-item-login-register-form">
            <div id="content-item-login-form">
				<?php learn_press_get_template( 'global/form-login' ); ?>
            </div>

<!--            <div id="content-item-register-form">-->
<!--				--><?php //learn_press_get_template( 'global/form-register' ); ?>
<!--            </div>-->
        </div>
    </course-item-login-register-form>

<?php } else {

} ?>