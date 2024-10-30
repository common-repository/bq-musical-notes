<?php
/*
Plugin Name: BQ Musical Notes
Plugin URI: http://bqplugins.com/bq-musical-notes
Description: Displays musical notes and intervals with flat and sharp symbols instead of "b" and "#". Provides shortcode for displaying chord diagrams.
Version: 2.2
Author: bquade
Author URI: http://bqplugins.com
License: GPL-3
*/

require_once 'notes.php';

add_action ('wp_head', array ('BQNotes', 'add_inline_styles'));
add_action ('admin_head', array ('BQNotes', 'add_inline_styles'));

add_filter ('the_title', array ('BQNotes', 'replace_note_text'));
add_filter ('the_content', array ('BQNotes', 'replace_note_text'));
add_filter ('the_excerpt', array ('BQNotes', 'replace_note_text'));
add_filter ('comment_text', array ('BQNotes', 'replace_note_text'));

if (has_filter ('the_content', 'wpautop'))
{
	if (!class_exists ('BQAutoBR'))
		require_once 'bqautobr.php';

	remove_filter ('the_content', 'wpautop');
	remove_filter ('the_excerpt', 'wpautop');
	add_filter ('the_content', array ('BQAutoBR', 'autobr'));
	add_filter ('the_excerpt', array ('BQAutoBR', 'autobr'));
}

add_shortcode ('bqchord', array ('BQNotes', 'bq_chord'));
?>
