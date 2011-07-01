<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>
			<p>Welcome to zURL.ws! We provide a free URL shortener, to make your looooooong URLs short. Tired of sending around long, manageable URLs? Need to send a long URL by text message or post it on Twitter? By entering a URL in the box below, we'll create a short URL that will never expire.</p>


			<?php echo form::open('url/shorten', array('id' => 'shorten')); ?>			
				<p>					
					<input type="hidden" name="token" value="<?php echo csrf::token(); ?>" />
					<label for="url">Enter a long URL to make short:</label><br />
					<input type="text" name="url" id="url" value="<?php echo htmlspecialchars($values['url']); ?>" />
<?php if (empty($captcha)): ?>
					<input type="submit" id="shorten" value="Shorten!" />
<?php endif; ?>
				</p>
<?php if ($logged_in): ?>
				<p id="types">
					Type (<a href="about.htm#custom">what?</a>): 
					
	<?php if (!empty($domains)) : ?>
					<?php echo Form::radio('type', 'domain', $values['type'] == 'domain', array('id' => 'type_domain')); ?>
					<label for="type_domain" title="Domain URL: Like a standard URL, except using your custom domain">Domain (standard)</label>
					
					<?php echo Form::radio('type', 'domain_custom', $values['type'] == 'domain_custom', array('id' => 'type_domain_custom')); ?>
					<label for="type_domain_custom" title="Custom domain URL: Like a user URL, except using your custom domain">Domain (custom)</label>
	<?php endif; ?>
					
					<?php echo Form::radio('type', 'standard', $values['type'] == '' || $values['type'] == 'standard', array('id' => 'type_standard')); ?>
					<label for="type_standard" title="Standard URL: A normal zURL link containing random letters and numbers in the URL">Standard</label>
					
					<?php echo Form::radio('type', 'custom', $values['type'] == 'custom', array('id' => 'type_custom')); ?>
					<label for="type_custom" title="Custom URL: Specify your own URL">Custom</label>
					
					<?php echo Form::radio('type', 'user', $values['type'] == 'user', array('id' => 'type_user')); ?>
					<label for="type_user" title="User URL: Like a custom URL, except with your username at the start">User</label>
				</p>
				<p id="alias_p">
					Short URL: http://<strong id="prefix"></strong>.zurl.ws/<input type="text" name="alias" id="alias" value="<?php echo !empty($values['alias']) ? htmlspecialchars($values['alias']) : ''; ?>" />
				</p>
	<?php if (!empty($domains)) : ?>
				<p id="domain_p">
					Domain: <?php echo Form::select('domain', $domains, Arr::get($values, 'domain'), array('id' => 'domain')); ?>
				</p>
				<p id="domain_custom_p">
					Short URL: http://<?php echo Form::select('domain_custom', $domains, Arr::get($values, 'domain_custom'), array('id' => 'domain_custom')); ?>/
					<input type="text" name="domain_alias" id="domain_alias" value="<?php echo !empty($values['domain_alias']) ? htmlspecialchars($values['domain_alias']) : ''; ?>" />
				</p>
	<?php endif; ?>
<?php else: ?>
				<input type="hidden" name="type" value="standard" />
<?php endif; ?>
				
<?php if (!empty($captcha)): ?>
				<p>
					<label for="recaptcha_challenge_field">Security code:</label>
					<?php echo $captcha; ?>
					
					<input type="submit" id="shorten" value="Shorten!" />
				</p>
<?php endif; ?>
			</form>
