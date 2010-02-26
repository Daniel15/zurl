<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>

			<?php echo form::open('account/settings'); ?>

				<p>
					<label for="timezone">Timezone:</label>
					<?php echo Form::timezone('timezone'); ?><br />
				</p>
				
				<p><input type="submit" value="Save Changes" />
			</form>
