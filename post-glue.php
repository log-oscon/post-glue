<?php
/**
 * Plugin Name: Post Glue
 * Plugin URI: https://github.com/log-oscon/post-glue/
 * Description: Sticky posts for WordPress, improved.
 * Version: 1.0.0
 * Author: log.OSCON, Lda.
 * Author URI: https://log.pt/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: post-glue
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/log-oscon/post-glue
 * GitHub Branch: master
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Post_Glue' ) ) {
	require 'lib/class-post-glue.php';
}

register_activation_hook( __FILE__, array( 'Post_Glue', 'activation' ) );

add_action( 'plugins_loaded', array( 'Post_Glue', 'plugins_loaded' ) );
