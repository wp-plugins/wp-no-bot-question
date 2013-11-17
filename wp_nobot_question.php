<?php
/*
Plugin Name: WP No-Bot Question
Plugin URI: http://www.compdigitec.com/apps/wpnobot/
Description: Simple question that blocks most spambots (and paid robots) by making them answer a common sense question
Version: 0.1.4
Author: Compdigitec
Author URI: http://www.compdigitec.com/
License: 3-clause BSD
Text Domain: wp_nobot_question
*/
define('wp_nobot_question_version','0.1.4');
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

// Database version
define('wp_nobot_question_current_db_version',2);

register_activation_hook( __FILE__, 'wp_nobot_question_activate' );
register_deactivation_hook( __FILE__, 'wp_nobot_question_deactivate' );
register_uninstall_hook( __FILE__, 'wp_nobot_question_remove' );

add_action('init', 'wp_nobot_question_init');
add_action('admin_menu', 'wp_nobot_question_admin_init');

add_action('comment_form_after_fields', 'wp_nobot_question_comment_field');
add_action('comment_form_logged_in_after', 'wp_nobot_question_comment_field');
add_filter('preprocess_comment', 'wp_nobot_question_filter');

add_action('user_registration_email', 'wp_nobot_question_filter');
add_action('register_form', 'wp_nobot_question_registration_field');

function wp_nobot_question_activate() {
	if(get_option('wp_nobot_question_db_version') === false) add_option('wp_nobot_question_db_version', wp_nobot_question_current_db_version);
	if(get_option('wp_nobot_question_enable') === false) add_option('wp_nobot_question_enable',true);
	if(get_option('wp_nobot_question_questions') === false) add_option('wp_nobot_question_questions',Array('What is the sum of 2 and 7?'));
	if(get_option('wp_nobot_question_answers') === false) add_option('wp_nobot_question_answers',Array(Array('nine','Nine','9')));
	if(get_option('wp_nobot_question_registration') === false) add_option('wp_nobot_question_registration',false);
}

function wp_nobot_question_deactivate() {
	/* Stub */
}

function wp_nobot_question_remove() {
	delete_option('wp_nobot_question_db_version');
	delete_option('wp_nobot_question_enable');
	delete_option('wp_nobot_question_questions');
	delete_option('wp_nobot_question_answers');
	delete_option('wp_nobot_question_registration');
}

function wp_nobot_question_init() {
	load_plugin_textdomain( 'wp_nobot_question', false, dirname( plugin_basename( __FILE__ ) ) );
	wp_enqueue_script('jquery');
}

function wp_nobot_question_admin_init() {
	add_submenu_page( 'options-general.php', 'WP No-bot Question &rarr; Edit Question', 'WP No-bot Question', 'moderate_comments', 'wp_nobot_question_page', 'wp_nobot_question_admin' );
	if(get_option('wp_nobot_question_question') !== false) { /* database version 1 */
		// we now support multiple questions
		add_option('wp_nobot_question_questions',Array(strval(get_option('wp_nobot_question_question'))));
		delete_option('wp_nobot_question_question');
		update_option('wp_nobot_question_answers',Array(get_option('wp_nobot_question_answers')));
		add_option('wp_nobot_question_db_version', wp_nobot_question_current_db_version);
	}
}

function wp_nobot_question_comment_field() {
	wp_nobot_question_field('comment');
}

function wp_nobot_question_registration_field() {
	wp_nobot_question_field('registration');
}

function wp_nobot_question_field($context = 'comment') {
	if( current_user_can('editor') || current_user_can('administrator') ||
	    !wp_nobot_question_get_option('enable') ||
	    ( $context == 'registration' && !wp_nobot_question_get_option('registration') )
	     ) return;
?>
<p class="comment-form-wp_nobot_question">
	<?php
	$questions = wp_nobot_question_get_option('questions');
	$answers = wp_nobot_question_get_option('answers');
	$selected_id = rand(0,count($questions)-1);
	?>
	<label for="wp_nobot_answer"><?php echo htmlspecialchars($questions[$selected_id]); ?> (<?php _e('Required','wp_nobot_question'); ?>)</label>
	<input
		id="wp_nobot_answer"
		name="wp_nobot_answer"
		type="text"
		value=""
		size="30"
		<?php if($context == 'registration') { ?> tabindex="25" <?php }; ?>
	/>
	<input type="hidden" name="wp_nobot_answer_question" value="<?php echo $selected_id; ?>" />
	<input type="hidden" name="wp_nobot_answer_question_hash" value="<?php echo wp_nobot_question_security_hash($selected_id, $questions[$selected_id], $answers[$selected_id]); ?>" />
	</p>
<?php
}

function wp_nobot_question_filter($x) {
	if( current_user_can('editor') || current_user_can('administrator') ||
	    ( /* Is registration? */!is_array($x) && !wp_nobot_question_get_option('registration') )||
	    $x['comment_type'] == 'pingback' || $x['comment_type'] == 'trackback' ||
	    !wp_nobot_question_get_option('enable') ) {
		return $x;
	}
	if(!array_key_exists('wp_nobot_answer',$_POST) || !array_key_exists('wp_nobot_answer_question',$_POST) || trim($_POST['wp_nobot_answer']) == '') {
		wp_die(__('Error: Please fill in the required question.','wp_nobot_question'));
	}
	$question_id = intval($_POST['wp_nobot_answer_question']);
	$questions_all = wp_nobot_question_get_option('questions');
	$answers_all = wp_nobot_question_get_option('answers');
	// Hash verification to make sure the bot isn't picking on one answer.
	// This does not mean that they got the question right.
	if(trim($_POST['wp_nobot_answer_question_hash']) != wp_nobot_question_security_hash($question_id,$questions_all[$question_id],$answers_all[$question_id])) {
		wp_die(__('Error: Please fill in the correct answer to the question.','wp_nobot_question'));
	}
	// Verify the answer.
	if($question_id < count($answers_all)) {
		$answers = $answers_all[$question_id];
		foreach($answers as $answer) {
			if(trim($_POST['wp_nobot_answer']) == $answer) return $x;
		}
	}
	wp_die(__('Error: Please fill in the correct answer to the question.','wp_nobot_question'));
}

function wp_nobot_question_get_option($o) {
	switch($o) {
		case 'enable':
			return (bool)get_option('wp_nobot_question_enable');
		break;
		case 'questions':
			$tmp = get_option('wp_nobot_question_questions');
			if( $tmp === false ) return Array();
			else return $tmp;
		break;
		case 'answers':
			$tmp = get_option('wp_nobot_question_answers');
			if( $tmp === false ) return Array();
			else return $tmp;
		break;
		case 'registration':
			return (bool)get_option('wp_nobot_question_registration');
		break;
		default:
			return null;
	}
}

function wp_nobot_question_security_hash($id,$question,$answer) {
	/*
	 * Hash format: SHA256( Question ID + Question Title + serialize( Question Answers ) )
	 */
	$hash_string = strval($id) . strval($question) . serialize($answer);
	return hash('sha256',$hash_string);
}

function wp_nobot_question_template($id_,$question,$answers) {
	$id = intval($id_);
?>
	<tr valign="top" class="wp_nobot_question_row_<?php echo $id; ?>">
	<th scope="row"><?php _e('Question to present to bot','wp_nobot_question'); ?></th>
	<td>
		<input type="input" name="wp_nobot_question_question_<?php echo $id; ?>" size="70" value="<?php echo htmlspecialchars($question); ?>" placeholder="<?php _e('Type here to add a new question','wp_nobot_question'); ?>" /><a href="javascript:void(0)" onclick="wp_nobot_question_delete_entire_question(&quot;<?php echo $id ?>&quot;)"><?php echo __('Delete Question'); ?></a>
	</td>
	</tr>
	<tr valign="top" class="wp_nobot_question_row_<?php echo $id; ?>">
	<th scope="row"><?php _e('Possible Answers','wp_nobot_question'); ?></th>
	<td>
<?php
$i = 0;
foreach($answers as $value) {
	echo "<span id=\"wp_nobot_question_line_{$id}_$i\">";
	printf('<input type="input" id="wp_nobot_question_answer_%1$d_%2$d" name="wp_nobot_question_answers_%1$d[]" size="70" value="%3$s" />', $id, $i, htmlspecialchars($value));
	echo "<a href=\"javascript:void(0)\" onclick=\"wp_nobot_question_delete(&quot;$id&quot;, &quot;$i&quot;)\">" . __('Delete') . "</a>";
	echo "<br /></span>\n";
	$i++;
}
echo "<script id=\"wp_nobot_question_placeholder_$id\">ct[$id] = $i;</script>";
?>
<button onclick="return wp_nobot_question_add_newitem(<?php echo $id; ?>)"><?php _e('Add New','wp_nobot_question'); ?></button>
	</td>
	</tr>
<?php
}

function wp_nobot_question_admin() {
	if(!current_user_can('moderate_comments')) return;
	if(isset($_POST['submit'])) {
		$questions = Array();
		$answers = Array();
		foreach($_POST as $key => $value) {
			if(strpos($key, 'wp_nobot_question_question_') === 0) {
				// value starts with wp_nobot_question_question_
				$q_id = str_replace('wp_nobot_question_question_','',$key);
				if(trim(strval($value)) != '') { // if not empty
					$question_slashed = trim(strval($value));
					// WordPress seems to add quotes by default, see:
					// http://stackoverflow.com/questions/1746078/wordpress-2-8-6-foobars-my-theme-options-with-escape-slashes#answers-header
					// http://core.trac.wordpress.org/ticket/18322
					$questions[] = stripslashes($question_slashed);
					$answers_slashed = array_filter($_POST['wp_nobot_question_answers_' . $q_id]);
					foreach($answers_slashed as $key => $value) {
						$answers_slashed[$key] = stripslashes($value);
					}
					$answers[] = $answers_slashed;
				}
			}
		}
		update_option('wp_nobot_question_enable',(bool)$_POST['wp_nobot_question_enabled']);
		update_option('wp_nobot_question_questions', $questions);
		update_option('wp_nobot_question_answers', $answers);
		if(array_key_exists( 'wp_nobot_question_registration', $_POST ))
			update_option('wp_nobot_question_registration', true);
		else
			update_option('wp_nobot_question_registration', false);
		add_settings_error('wp_nobot_question', 'wp_nobot_question_updated', __('WP No-Bot Question settings updated.','wp_nobot_question'), 'updated');
	}

	$wp_nobot_question_enabled = wp_nobot_question_get_option('enable');
	$wp_nobot_question_questions = wp_nobot_question_get_option('questions');
	$wp_nobot_question_answers = wp_nobot_question_get_option('answers');
	$wp_nobot_question_registration = wp_nobot_question_get_option('registration');
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
	<th scope="row"><?php _e('Protect the registration page too?','wp_nobot_question'); ?></th>
	<td>
		<fieldset>
			<input type="checkbox" name="wp_nobot_question_registration" value="1" <?php if($wp_nobot_question_registration) echo 'checked="checked"' ?> />
		</fieldset>
	</td>
	</tr>
	<tr colspan="2">
	<td>
		<b><?php _e('Questions to present to bot','wp_nobot_question'); ?></b>
	</td>
	</tr>
	<script type="text/javascript">
	var ct = Array();
	function wp_nobot_question_delete(id, x) {
		jQuery("#wp_nobot_question_line_" + id + "_" + x).remove();
	}

	function wp_nobot_question_delete_entire_question(id) {
		jQuery("tr.wp_nobot_question_row_" + id).remove();
	}

	function wp_nobot_question_add_newitem(id) {
		jQuery("#wp_nobot_question_placeholder_" + id).before("<span id=\"wp_nobot_question_line_" + id + "_" + ct[id] + "\"><input type=\"input\" id=\"wp_nobot_question_answer_" + id + "_" + ct + "\" name=\"wp_nobot_question_answers_" + id + "[]\" size=\"70\" value=\"\" placeholder=\"<?php _e('Enter a new answer here','wp_nobot_question'); ?>\" /><a href=\"javascript:void(0)\" onclick=\"wp_nobot_question_delete(&quot;" + id + "&quot;, &quot;" + ct[id] + "&quot;)\"><?php echo __('Delete'); ?></a><br /></span>");
		ct[id]++;
		return false;
	}
	</script>
	<?php
	$i = 0;
	foreach($wp_nobot_question_questions as $question) {
		wp_nobot_question_template($i, $question, $wp_nobot_question_answers[$i]);
		$i++;
	}
	wp_nobot_question_template($i, '', Array());
	?>
</table>

<?php submit_button(); ?>
</form>
<p>WP No-Bot Question version <?php echo wp_nobot_question_version; ?> by <a href="http://www.compdigitec.com/">Compdigitec</a>. You can find support at <a href="http://www.compdigitec.com/apps/wpnobot/">the plugin's homepage</a> - bugs and suggestions welcome. Please leave a comment to indicate a potential feature, bug, or just for appreciation.</p>
</div>
<?php
}

?>
