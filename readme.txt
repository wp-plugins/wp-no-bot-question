=== Plugin Name ===
Contributors: edwardw
Donate link: http://www.compdigitec.com/
Tags: anti-bot, anti-spam, comments, question, captcha, anti-bot question, anti-spam question, bots
Requires at least: 3.1
Tested up to: 3.3
Stable tag: trunk

Simple question that blocks most spambots (and paid robots) by making them answer a common sense question

== Description ==

This plugin is just a very effective yet simple plugin which adds a question to the comment form that blocks most automated spam-bots by making them answer a common sense question. For optimal effectiveness, you might want to consider changing the question periodically so that the spambots can't "memorize" the question and answer.

Multiple answers can be added to account for variances in answers. (e.g. "five", "Five", "5")

Plugin homepage: http://www.compdigitec.com/apps/wpnobot

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

= 0.1 =
* Initial version

