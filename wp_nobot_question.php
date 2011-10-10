<?php
/*
Plugin Name: WP No-Bot Question
Plugin URI: http://www.compdigitec.com/apps/wpnobot/
Description: Simple question that blocks most spambots (and paid robots) by making them answer a common sense question
Version: 0.1
Author: Compdigitec
Author URI: http://www.compdigitec.com/
License: 3-clause BSD
Text Domain: wp_nobot_question
*/
define('wp_nobot_question_version','0.1');
/*
 *      Redistribution and use in source and binary forms, with or without
 *      modification, are permitted provided that the following conditions are
 *      met:
 *      
 *      * Redistributions of source code must retain the above copyright
 *        notice, this list of conditions and the following disclaimer.
 *      * Redistributions in binary form must reproduce the above
 *        copyright notice, this list of conditions and the following disclaimer
 *        in the documentation and/or other materials provided with the
 *        distribution.
 *      * Neither the name of the Compdigitec nor the names of its
 *        contributors may be used to endorse or promote products derived from
 *        this software without specific prior written permission.
 *      
 *      THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *      "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 *      LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 *      A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 *      OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *      SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 *      LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 *      DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 *      THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *      (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *      OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

register_activation_hook( __FILE__, 'wp_nobot_question_activate' );
register_deactivation_hook( __FILE__, 'wp_nobot_question_deactivate' );
register_uninstall_hook( __FILE__, 'wp_nobot_question_remove' );

add_action('init', 'wp_nobot_question_init');
add_action('admin_menu', 'wp_nobot_question_admin_init');
add_action('comment_form_after_fields', 'wp_nobot_question_comment_field');
add_action('comment_form_logged_in_after', 'wp_nobot_question_comment_field');
add_filter('preprocess_comment', 'wp_nobot_question_filter');

function wp_nobot_question_activate() {
	if(get_option('wp_nobot_question_enable') === false) add_option('wp_nobot_question_enable',true);
	if(get_option('wp_nobot_question_question') === false) add_option('wp_nobot_question_question','What is the sum of 2 and 3?');
	if(get_option('wp_nobot_question_answers') === false) add_option('wp_nobot_question_answers',Array('five','Five','5'));
}

function wp_nobot_question_deactivate() {
	
}

function wp_nobot_question_remove() {
	delete_option('wp_nobot_question_enable');
	delete_option('wp_nobot_question_question');
	delete_option('wp_nobot_question_answers');
}

function wp_nobot_question_init() {
	load_plugin_textdomain( 'wp_nobot_question', false, dirname( plugin_basename( __FILE__ ) ) );
	wp_enqueue_script('jquery');
}

function wp_nobot_question_admin_init() {
	add_submenu_page( 'options-general.php', 'WP No-bot Question &rarr; Edit Question', 'WP No-bot Question', 'moderate_comments', 'wp_nobot_question_page', 'wp_nobot_question_admin' );
}

function wp_nobot_question_comment_field() {
	if(current_user_can('editor') || current_user_can('administrator')) return;
?>
<p class="comment-form-wp_nobot_question"><label for="wp_nobot_answer"><?php echo get_option('wp_nobot_question_question'); ?> (<?php _e('Required','wp_nobot_question'); ?>)</label><input id="wp_nobot_answer" name="wp_nobot_answer" type="text" value="" size="30" /></p>
<?php
}

function wp_nobot_question_filter($x) {
	if(current_user_can('editor') || current_user_can('administrator')) {
		return $x;
	}
	if(!array_key_exists('wp_nobot_answer',$_POST) || trim($_POST['wp_nobot_answer']) == '') {
		wp_die(__('Error: Please fill in the required question.','wp_nobot_question'));
	}
	$answers = get_option('wp_nobot_question_answers');
	foreach($answers as $answer) {
		if(trim($_POST['wp_nobot_answer']) == $answer) return $x; 
	}
	wp_die(__('Error: Please fill in the correct answer to the question.','wp_nobot_question'));
}

function wp_nobot_question_admin() {
	if(!current_user_can('moderate_comments')) return;
	if(isset($_POST['submit'])) {
		update_option('wp_nobot_question_enable',(bool)$_POST['wp_nobot_question_enabled']);
		update_option('wp_nobot_question_question',(string)$_POST['wp_nobot_question_question']);
		update_option('wp_nobot_question_answers',$_POST['wp_nobot_question_answers']);
		add_settings_error('wp_nobot_question', 'wp_nobot_question_updated', __('WP No-Bot Question settings updated.','wp_nobot_question'), 'updated');
	}
	$wp_nobot_question_enabled = (bool)get_option('wp_nobot_question_enable');
	$wp_nobot_question_question = strval(get_option('wp_nobot_question_question'));
	$wp_nobot_question_answers = get_option('wp_nobot_question_answers');
	?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Edit WP No-Bot Question</h2>
	<?php settings_errors(); ?>
	<form method="post" name="wp_nobot_question_form">
<?php settings_fields('discussion'); ?>
<table class="form-table">
	<tr valign="top">
	<th scope="row"><?php _e('Enable WP No-Bot Question','wp_nobot_question'); ?></th>
	<td>
		<fieldset>
			<input type="radio" name="wp_nobot_question_enabled" value="1" <?php if($wp_nobot_question_enabled) echo 'checked="checked"' ?> /> <?php _e('Yes'); ?>
			<input type="radio" name="wp_nobot_question_enabled" value="0" <?php if(!$wp_nobot_question_enabled) echo 'checked="checked"' ?> /> <?php _e('No'); ?>
		</fieldset>
	</td>
	</tr>
	<tr valign="top">
	<th scope="row"><?php _e('Question to present to bot','wp_nobot_question'); ?></th>
	<td>
		<input type="input" name="wp_nobot_question_question" size="70" value="<?php echo $wp_nobot_question_question; ?>" />
	</td>
	</tr>
	<tr valign="top">
	<th scope="row"><?php _e('Possible Answers','wp_nobot_question'); ?></th>
	<td>
<?php
$i = 0;
foreach($wp_nobot_question_answers as $value) {
	echo "<span id=\"wp_nobot_question_line_$i\"><input type=\"input\" id=\"wp_nobot_question_answer_$i\" name=\"wp_nobot_question_answers[]\" size=\"70\" value=\"$value\" /><a href=\"javascript:void(0)\" onclick=\"wp_nobot_question_delete(&quot;$i&quot;)\">" . __('Delete') . "</a><br /></span>";
	$i++;
}
echo "<script id=\"wp_nobot_question_placeholder\">ct = $i;</script>";
?>
<button onclick="return wp_nobot_question_add_newitem()"><?php _e('Add New','wp_nobot_question'); ?></button>		
	<script type="text/javascript">
	function wp_nobot_question_delete(x) {
		jQuery("#wp_nobot_question_line_" + x).remove();
	}
	
	function wp_nobot_question_add_newitem() {
		jQuery("#wp_nobot_question_placeholder").before("<span id=\"wp_nobot_question_line_" + ct + "\"><input type=\"input\" id=\"wp_nobot_question_answer_" + ct + "\" name=\"wp_nobot_question_answers[]\" size=\"70\" value=\"\" placeholder=\"Enter a new answer here\" /><a href=\"javascript:void(0)\" onclick=\"wp_nobot_question_delete(&quot;" + ct + "&quot;)\">Delete</a><br /></span>");
		ct++;
		return false;
	}
	</script>
	</td>
	</tr>
</table>

<?php submit_button(); ?>
</form>
<p>WP No-Bot Question version <?php echo wp_nobot_question_version; ?> by <a href="http://www.compdigitec.com/">Compdigitec</a>. You can find support at <a href="http://www.compdigitec.com/apps/wpnobot/">the plugin's homepage</a>.</p>
</div>
<?php
}

?>