<?php
namespace loglink;

class plugin {
	public $file, $basedir;
	public $links;
	public $admin_cap = 'manage_options';

	public function __construct() {
		$this->file    = dirname(dirname(__FILE__)) . '/loglink.php';
		$this->basedir = dirname($this->file);

		add_action('plugins_loaded', array($this, 'init'));
		add_action('plugins_loaded', array($this, 'check_uri'));
		add_action('wp_ajax_' . __NAMESPACE__ . '_link_gen', array($this, 'gen_link'));
	}

	public function init() {
		require_once($this->basedir . '/classes/admin.php');
		require_once($this->basedir . '/classes/user-edit.php');
	}

	public function gen_link($uid = FALSE) {
		$_p = array_map('stripslashes', array_map('esc_html', $_POST));

		if(!$uid) {
			if(!isset($_p['uid']) || empty($_p['uid'])) return;

			if(!isset($_p[__NAMESPACE__ . '_gen_nonce']) || !wp_verify_nonce($_p[__NAMESPACE__ . '_gen_nonce'], __NAMESPACE__ . '_gen_' . $_p['uid']))
				return;

			$uid = $_p['uid'];
		}

		$key  = $this->opts('gen_hash');
		$time = time();
		$data = base64_encode($uid);

		$hash = hash_hmac('sha256', $uid, $key);

		echo site_url('?'.__NAMESPACE__ . '=' . $uid . '||' . $hash);
		exit();
	}

	public function check_uri() {
		if(!isset($_REQUEST[__NAMESPACE__]) || empty($_REQUEST[__NAMESPACE__])) return;

		$raw = urldecode($_REQUEST[__NAMESPACE__]);
		if(strpos($raw, '||') === -1) return; // Invalid link

		$arr = explode('||', $raw);
		if(count($arr) !== 2) return; // Invalid link

		$uid = $arr[0];
		$hash = $arr[1];
		$key = $this->opts('gen_hash');

		$hashcheck = hash_hmac('sha256', $uid, $key);
		/*
		echo 'arr[0]: ' . $arr[0];
		echo '  uid: ' . $uid;
		echo '  hash: ' . $hash;
		echo '  hashcheck:' . $hashcheck;
		*/

		if($hash === $hashcheck) {
			$this->do_login((int)$uid);

			$url = apply_filters(__NAMESPACE__ . '_redirect_url', site_url());

			wp_redirect($url);
			exit();
		}
	}

	public function opts($opt = FALSE) {
		$defaults = array(
			'gen_hash'        => '', // The hash key to use
			'link_duration'   => 24 * 60 * 60, // 1 day
			'override_logins' => 'yes' // Links log out logged-in Users?
		);

		$defaults = apply_filters(__NAMESPACE__ . '_default_options', $defaults);
		$opts = get_option(__NAMESPACE__ . '_options');

		if(!$opts || !is_array($opts)) $returnable = $defaults;
		else $returnable = array_merge($defaults, $opts);

		if(!$opt) return $returnable;

		if($opt && !array_key_exists($opt, $returnable)) return FALSE;
		elseif($opt) return $returnable[$opt];
	}

	private function do_login($uid) {
		$user = new \WP_User($uid);
		if(!$user->exists()) return;

		if(is_user_logged_in() && $this->opts('override_logins') === 'no') return;

		wp_set_current_user($uid, $user->user_login);
		wp_set_auth_cookie($uid);
		do_action('wp_login', $user->user_login);
	}
}

$GLOBALS[__NAMESPACE__] = new plugin;

function plugin() {
	return $GLOBALS[__NAMESPACE__];
}
