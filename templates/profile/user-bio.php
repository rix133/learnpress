<?php
/**
 * Template for displaying user's BIO in profile.
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 3.x.x
 */

defined( 'ABSPATH' ) or die();

$user = LP_Profile::instance()->get_user();
?>
<div class="user-bio">
	<?php echo learn_press_maybe_auto_p( $user->get_description() ); ?>
</div>
