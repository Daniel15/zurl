<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>

			<?php echo form::open('account/register', array('id' => 'register')); ?>

				<p>
					<label for="username">Username:</label>
					<input type="text" name="username" id="username" value="<?php echo htmlspecialchars($values['username']); ?>" /> 
					<strong id="username_check"></strong>
					<br />
					<small>Your username is what you use to log in to the site. It is case insensitive (uppercase and lowercase are the same thing), and can only contain <strong>alphanumeric characters</strong> (A-Z and 0-9), <strong>dashes</strong> (-) and <strong>underscores</strong> (_).</small><br /><br />
					
					<label for="email">Email Address:</label>
					<input type="text" name="email" id="email" value="<?php echo htmlspecialchars($values['email']); ?>"/><br />
				
					<label for="password">Password:</label>
					<input type="password" name="password" id="password" /><br />
					
					<label for="password_confirm">Confirm Password:</label>
					<input type="password" name="password_confirm" id="password_confirm" /><br />
				</p>
				
				<p>
					<label for="recaptcha_challenge_field">Security code:</label><br />
					<?php echo $captcha; ?>
				</p>
				
				<p>By registering, you agree to zURL's Terms of Use. In short, do not use the service to spam. We won't give your personal information to anyone.</p>
				
				<p>
					<input type="hidden" name="token" value="<?php echo csrf::token(); ?>" />
					<input type="hidden" name="timezone" id="timezone" />
					<input type="submit" value="Register" />
				</p>
			</form>
