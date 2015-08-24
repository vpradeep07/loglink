<?php
namespace loglink;

class user_edit {
	public $plugin;

	public function __construct() {
		$this->plugin = plugin();

		add_action('edit_user_profile', array($this, 'display'));
		add_action('show_user_profile', array($this, 'display'));
	}

	public function display($user) {
		if(!current_user_can($this->plugin->admin_cap)) return;

		add_thickbox();

		$nonce = wp_create_nonce(__NAMESPACE__.'_gen_'.$user->ID);
		?>
		<table class="form-table">
			<tbody>

			<tr>
				<th>
					Generate Login Link
				</th>
				<td>
					<a href="#TB_inline?width=650&height=150&inlineId=loglink-lightbox" class="thickbox" id="loglink_generate_link">Click here</a> to generate a signed login link for this User.
				</td>
			</tr>

			</tbody>
		</table>

		<div id="loglink-lightbox" style="display:none;">
			<p>
				<label for="loglink-signed-link" style="font-size: 125%; font-weight: bold;">Here's this User's special secure login link!</label>
			</p>

			<p>
				<input type="text" id="loglink-signed-link" style="width: 100%;" />
			</p>

			<p>
				Be careful who you send this to! It automatically gives access to this User's account to anyone who is granted access to the link.
			</p>
		</div>

		<script type="text/javascript">
			(function($) {
				var url = '';

				$('#loglink_generate_link').click(
					function()
					{
						var data = {
							action           : 'loglink_link_gen',
							loglink_gen_nonce: '<?php echo $nonce; ?>',
							uid              : <?php echo $user->ID; ?>
						};

						$.post(
							ajaxurl, data, function(response) {
								$('#loglink-signed-link').val(response);
							});
					});

				$('#loglink-signed-link').click(function() {
						$(this).select();
					});

				$('#loglink-signed-link').onchange(function() {
					$(this).val(url);
				});
			})(jQuery);
		</script>
	<?php
	}
}

return new user_edit;
