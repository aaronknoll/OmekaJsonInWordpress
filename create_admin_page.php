<?php

/* creates the admin section page */
add_action('admin_menu', 'omekafeedpull_menu');

function omekafeedpull_menu() {
	//create new top-level menu
	add_menu_page('omekafeedpull', 'omekafeedpull', 'administrator', 'omekafeedpullhtml', 'omekafeedpull_htmlpage',plugins_url('/images/engine2.png', __FILE__));
	//call register settings function
	add_action( 'admin_init', 'omekafeedpull_mysettings' );
}


function omekafeedpull_mysettings() {
	//register our settings
	register_setting( 'omekafeedpull-group', 'omekafeedpull_omekaroot' );
}



function omekafeedpull_htmlpage() {
?>
	<div class="wrap">
	<h2>Settings for including Omeka pages</h2>
	
	<form method="post" action="options.php">
	
	    <?php settings_fields( 'omekafeedpull-group' ); ?>
	
	<h4>Where is your content coming from?</h4>
	    <table class="form-table">
	    	<tr valign="top">
	        <th scope="row">What directory from your base
	        	installation of Wordpress is your Omeka site?</th>
	        <td><input type="text" name="omekafeedpull_omekaroot" value="<?php echo get_option('omekafeedpull_omekaroot'); ?>" /></td>
	        </tr>
	    </table>
	    <!--<p>Firstly note, <strong>if the field is blank it will not display</strong>.
	    	but since this plugin assumes that your Omeka backend is primarily
	    	inteneded for cataloging, I think it's possible and likely that you
	    	have some fields which you want filled out for archival purposes,
	    	but you may not want to display on the front end. By filling this out
	    	you can use the "Display all Fields" option and have it automatically
	    	only display the fields you want, regardless of whether or not
	    	they are empty on your Omeka Backend.</p>
	   	<table class="form-table">
	    	<tr valign="top">
	        <th scope="row">Would You like to suppress any Dublin Core Fields?</th>
	        <td><input type="text" name="omekafeedpull_omekaroot" value="<?php echo get_option('omekafeedpull_omekaroot'); ?>" /></td>
		        <td>
		        	<fieldset>
		        		<legend>
		        			Creator
		        		</legend>
		        			<select>
				 				<option name="creatoron" id="creatoron" value="mercedes">Yes</option>
				  				<option value="audi">No</option>
							</select>	
		        	</fieldset>
		        	
				</td>
	        </tr>
	    </table>-->
	    <p class="submit">
	    	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	    </p>
	</form>
	</div>
<?php } ?>