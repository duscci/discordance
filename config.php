<div class="wrap" id="discordance">
	<h1 class="wp-heading-inline"><?= get_admin_page_title() ?></h1>
	<?php
    if (isset($_POST['webhooks']) && isset($_POST['format'])) {
        $discordance_opts['webhooks'] = sanitize_textarea_field($_POST['webhooks']);
        $discordance_opts['format'] = sanitize_textarea_field($_POST['format']);
        update_option('discordance', $discordance_opts);
        echo <<<HTML
    <div class="updated notice is-dismissible" id="discordance-update-prompt">
		<p>
			<strong>Settings updated.</strong>
		</p>
    </div>
HTML;
    } ?>
	<hr class="wp-header-end">
	<form method="post" novalidate="novalidate">
		<table class="form-table">
			<tr>
				<th>
					<label for="webhooks">Discord Webhooks</label>
				</th>
				<td>
					<textarea name="webhooks" id="webhooks" rows="4" cols="100" placeholder="https://discord.com/api/webhooks/id/token"><?= $discordance_opts['webhooks']; ?></textarea>
					<p class="description">one Discord Webhook URL per line</p>
				</td>
			</tr>
				<th>
					<label for="format">Embed format</label><br />
					<button id="pretty" class="button button-primary">Pretty JSON</button>
				</th>
				<td style="position: relative;">
					<textarea name="format" id="format" rows="12" cols="100"><?= stripslashes($discordance_opts['format']); ?></textarea>
					<div id="format-warn" style="color: red; font-size: 0.6rem; position: absolute; top: 0"></div>
					<p class="description">
						<strong>Variables:</strong> %title% (post title), %excerpt% (post excerpt), %thumbnail% (post thumbnail), %link% (post permalink), %author% (author display name), %author_url% (author url), %gravatar% (author avatar provided by <a href="https://en.gravatar.com/connect/" target="_blank">Gravatar</a>)<br />
						<strong>Embed Visualizer:</strong> <a href="https://leovoel.github.io/embed-visualizer/" target="_blank">https://leovoel.github.io/embed-visualizer/</a> (don't forget to activate webhook mode)
					</p>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Update settings"></p>
	</form>
</div>