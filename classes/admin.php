<?php
namespace loglink;

class admin {
	public $plugin;

	public $saved;

	public function __construct() {
		$this->plugin = plugin();

		add_action('admin_menu', array($this, 'enqueue'));
	}

	public function enqueue() {
		add_management_page('Loglink', 'Loglink', 'manage_options', 'loglink-options', array($this, 'page'));
	}

	public function page() {
		if(!current_user_can($this->plugin->admin_cap))
			wp_die('You don\'t have access to this.');

		$this->save_options();
		?>
		<div class="wrap">
			<h2>Loglink - Settings</h2>

			<?php if($this->saved): ?>
				<div style="margin-top: 10px; padding: 10px; color: green; font-size: 150%; background-color: #9EFFA1; border: 1px solid #CCC; border-radius: 5px;">Successfully Saved Options. :-)</div>
			<?php endif; ?>

			<form method="post" action="">
				<table class="form-table">
					<tbody>
					<tr>
						<th>
							<label for="gen_hash">Link Hash Key (required)</label>
						</th>
						<td>
							<input type="text" name="gen_hash" id="gen_hash" class="regular-text"
										value="<?php echo $this->plugin->opts('gen_hash'); ?>" />
							<p class="description">
								This alphanumeric key is used to secure your login links. You shouldn't change this after it's set, as all previously generated links will be invalid.
							</p>
							<p class="description">
								<a href="#" id="loglink_autogen">Click here</a> to automatically generate a key.
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label for="link_duration">Link Duration (in seconds)</label>
						</th>
						<td>
							<input type="text" name="link_duration" id="link_duration" class="regular-text"
							       value="<?php echo $this->plugin->opts('link_duration'); ?>" />
							<p class="description">
								Set the amount of time a link is good for (in seconds). Default is 1 day (<code>86400</code> seconds).
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label for="override_logins">Override Current Login?</label>
						</th>
						<td>
							<select id="override_logins" name="override_logins">
								<option value="yes"
									<?php if($this->plugin->opts('override_logins') === 'yes') echo 'selected="selected"'; ?>>
									Yes -- Log Users out when links are clicked
								</option>
								<option value="no"
									<?php if($this->plugin->opts('override_logins') === 'no') echo 'selected="selected"'; ?>>
									No -- If a User is logged in, leave them logged in.
								</option>
							</select>
							<p class="description">
								The behavior of the plugin if a User clicks a login link while they're already logged in.
							</p>
						</td>
					</tr>
					</tbody>
				</table>
				<?php submit_button('Save All Changes'); ?>
				<?php wp_nonce_field(__NAMESPACE__ . '_opts', __NAMESPACE__ . '_opts_nonce'); ?>
			</form>
		</div>
		<script type="text/javascript">
			(function($) {
				var randomString = function(len, charSet) {
					charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
					var str = '';

					for(var i = 0; i < len; i++) {
						var randomPoz = Math.floor(Math.random() * charSet.length);
						str += charSet.substring(randomPoz, randomPoz + 1);
					}
					return str;
				};

				$('#loglink_autogen').click(function() {
						$('#gen_hash').val(randomString(40));
				});
			})(jQuery);
		</script>
	<?php
	}

	private function save_options() {
		if(empty($_POST) || !isset($_POST[__NAMESPACE__.'_opts_nonce'])) return;

		$_p = array_map('stripslashes', array_map('esc_html', $_POST));

		if(!wp_verify_nonce($_p[__NAMESPACE__.'_opts_nonce'], __NAMESPACE__.'_opts')) return;

		$this->saved = TRUE;

		$opts = array(
			'gen_hash'        => $_p['gen_hash'],
			'link_duration'   => $_p['link_duration'],
			'override_logins' => $_p['override_logins']
		);

		update_option(__NAMESPACE__ . '_options', $opts);
	}
}

return new admin;
