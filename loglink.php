<?php
/*
 * Plugin Name: Loglink
 * Author: Code by Jinx
 * Author URI: http://byjinx.com/
 * Description: Generate time-sensitive, signed, and secure auto-login links for your Users.
 * Version: 150614
 */

if(require(dirname(__FILE__).'/includes/wp-php53.php'))
	require(dirname(__FILE__).'/includes/loglink.inc.php');
else wp_php53_notice('Loglink');
