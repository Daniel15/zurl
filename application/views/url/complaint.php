<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>

			<p>zURL has a strong stance against spam, and will delete any URLs that are being used for spamming. If you wish to report a spam URL you've encountered on zURL, please use the below form. Spam submissions will be dealt with as soon as possible. Note that zURL does NOT host any content, we just provide a URL shortening service. If you want to report a spam website, please report it to the web host of said site. Thanks!</p>
			<?php echo form::open('url/complaint'); ?>

				<p>
					<label for="url">URL:</label>
					<input type="text" name="url" id="url" size="50" value="<?php echo htmlspecialchars($values['url']); ?>" /><br />
					
					<label for="email">Your Email Address:</label>
					<input type="text" name="email" id="email" size="50" value="<?php echo htmlspecialchars($values['email']); ?>" /><br />
					<small>Your email address is optional and is only needed if you want us to contact you about this URL if we have any questions</small>
				</p>
				
				<p>
					<label for="reason">Reason for reporting:</label><br />
					<textarea name="reason" id="reason" rows="6" cols="60"><?php echo htmlspecialchars($values['reason']); ?></textarea>
				</p>
				
				<p>
					<label for="recaptcha_challenge_field">Security code:</label><br />
					<?php echo $captcha; ?>
				</p>
				
				<p><input type="submit" value="Send Report" />
			</form>
