=== Plugin Name ===
Contributors: edwardw
Donate link: http://www.compdigitec.com/
Tags: anti-bot, anti-spam, comments, question, captcha, anti-bot question, anti-spam question, bots
Requires at least: 3.1
Tested up to: 4.3
Stable tag: trunk

Simple question that blocks most spambots (and paid robots) by making them answer a common sense question

== Description ==

This plugin is just a very effective yet simple plugin which adds a question to the comment form that blocks most automated spam-bots by making them answer a common sense question. For optimal effectiveness, you should add multiple questions so that the spambots can't "memorize" the question and answer. The plugin will display questions at random to prevent the aformentioned memorization. Multiple answers can be added per question to account for variances in answers. (e.g. "five", "Five", "5")

Plugin homepage: http://www.compdigitec.com/apps/wpnobot

Please drop a [comment](http://www.compdigitec.com/labs/2011/10/09/wp-no-bot-question-plugin-for-wordpress/ "WP No-bot Question plugin for WordPress") if it works for you, if you found any bugs or if you have suggestions/ideas for this plugin - complaints welcome!

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `wp_nobot_question` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enable and edit the question&answer under "Settings -> Edit WP No-Bot Question"

== Frequently Asked Questions ==

= How do I change or disable this plugin's settings? =
Disable and edit the question and answer under "Settings -> Edit WP No-Bot Question"

= Why is the CAPTCHA / Question-box not showing up? =
Make sure you are using a WordPress 3.0 theme - upgrade your theme to use `comment_form()` or make sure it calls `do_action('comment_form_after_fields');` somewhere in the comment box.

== Screenshots ==



== Changelog ==
= 0.1.6 =
* 6768a8f Tested with 4.3
* fd0d6e2 l10n: update
* 4662faf Add direct access to settings

= 0.1.5 =
* 51f4772 Tested with 3.9

= 0.1.4 =
* 4357959 Tested with 3.7.1
* 17efd18 l10n: Greek translation by ifaist0s

= 0.1.3 =
* 883ed15 Add a basic hashing mechanism to the question
* 9214d36 Support questions/answers with quotation marks
* 3b35ca3 Escape HTML special characters in admin
* 787b294 Validate the existance of the wp_nobot_answer_question field

= 0.1.2 =
* c911cc4 Update fr-FR translation
* e84f760 Bump the version to 0.1.2
* ce4d073 Implement addition/deletion of multiple questions
* 2823060 Support multiple questions in the database
* 01bcabd Factorize question template
* b07ce93 Don't store empty answers

= 0.1.1 =
* a0e0933 Translate the delete link when dynamic
* 765172c fr-FR translation update
* 2123900 Update pot file for translators
* 9ab727e Appreciation note
* 74c1e17 Implement an option to allow protection of the registration page separat
* 635e4a9 Enable protecting the registration page as well
* bce22e1 Kill typecast to array warning
* a84ddb1 Fix whitespace errors

= 0.1 =
* Initial version

