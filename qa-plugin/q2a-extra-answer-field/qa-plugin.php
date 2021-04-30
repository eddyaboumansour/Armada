<?php
/*
	Plugin Name: Extra Answer Field
	Plugin URI: https://github.com/JacksiroKe/q2a-extra-question
	Plugin Description: Add extra field(s) on the question form
	Plugin Version: 2.1
	Plugin Date: 2015-02-04
	Plugin Author: JacksiroKe
	Plugin Author URI: https://github.com/JacksiroKe
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI:
*/
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

	$plugin_dir = dirname( __FILE__ ) . '/';
	$plugin_url = qa_path_to_root().'qa-plugin/q2a-extra-answer-field';
	qa_register_layer('qa-eaf-admin.php', 'Extra Answer Field Admin', $plugin_dir, $plugin_url );
	
	qa_register_plugin_phrases('langs/qa-eaf-lang-*.php', 'eaf_lang');
	qa_register_plugin_module('module', 'qa-eaf.php', 'qa_eaf', 'Extra Answer Field');
	qa_register_plugin_module('event', 'qa-eaf-event.php', 'qa_eaf_event', 'Extra Answer Field');
	qa_register_plugin_layer('qa-eaf-layer.php', 'Extra Answer Field');
	qa_register_plugin_module('filter', 'qa-eaf-filter.php', 'qa_eaf_filter', 'Extra Answer Field');
/*
	Omit PHP closing tag to help avoid accidental output
*/