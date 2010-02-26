<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>

			<?php echo form::open('account/login'); ?>

				<p>
					<label for="username">Username:</label>
					<input type="text" name="username" id="username" /><br />
				
					<label for="password">Password:</label>
					<input type="password" name="password" id="password" /><br />
				</p>
				
				<p>
					<input type="hidden" name="timezone" id="timezone" />
					<input type="submit" value="Log in" />
				</p>
			</form>
