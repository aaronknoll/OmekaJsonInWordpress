<?php

/* creates the admin section page */
add_action('admin_menu', 'omekafeedpull_menu');

function omekafeedpull_menu() {
	//create new top-level menu
	add_menu_page('omekafeedpull', 'omekafeedpull', 'administrator', 'omekafeedpullhtml', 'omekafeedpull_htmlpage',plugins_url('/images/engine2.png', __FILE__));
	//call register settings function
	add_action( 'admin_init', 'omekafeedpull_mysettings' );
}

class dCoreDropDown {
	private $dcoreArray	=	array("Title", "Creator", "Subject", "Description", "Publisher", "Contributor", "Date", "Type", "Format", "Identifier", "Source", "Language", "Relation", "Coverage", "Rights");
	
	private function isArray($string)
		{
			//echo "eval point a";
			if(strstr($string, ','))
				{
				return TRUE;
				//echo "eval point d";
				}
			else {
				return FALSE;
				//echo "eval point e";
			}
		}
		
	private function splitCore($nonArray)
		{
			$temp	=	explode(',', $nonArray);
			//echo "splitting the core";
			return $temp;
		}
	
	//makeMenu is available because it leaves a potential
	//hook for future expansion of this plugin. Right now
	//it will display a list of every dublin core field
	//to turn on/off. But perhaps in the future an admin
	//should be able to control on a level with more
	//authority which fields may/may not be turned off. 
	
	//SPECIAL CASE. if $whichcore = "all", call the dcore variable
	public function makeMenu($whichCore)
		{
						
		if($whichCore == "all"){$whichCore = $this->dcoreArray;}
			//whichCore will take either a single value of a dublin core
			//field, or a string of values separated by commas. 
			if(is_array($whichCore))
				{
					foreach($whichCore as $element)
						{
						$this->singleMenuIteration($element);
						}
				}
			elseif($this->isArray($whichCore) == TRUE)
				{
					//display a menu for each
					$whichCore	= $this->splitCore($whichCore);
					foreach($whichCore as $element)
						{
						$this->singleMenuIteration($element);
						}
				}
			else {
				
				//display a single iteration
				$this->singleMenuIteration($whichCore);
			}

		}
	
	private function whatisthere($element)
		{
		$current	=	get_site_option('omekafeedpull_'. $element .'toggle');
			{
				if($current)
					{return $current;}
				else
					{return FALSE;}
			}	
		}
		
	private function singleMenuIteration($element)
		{
			$onoroff	=	$this->whatisthere($element);
			?>
			<fieldset>
				<label for="omekafeedpull_<?php echo $element; ?>toggle"><?php echo $element; ?></label>
				<select id="omekafeedpull_<?php echo $element; ?>toggle" name="omekafeedpull_<?php echo $element; ?>toggle">
					<option <?php if($onoroff == "on"){echo "SELECTED";}?> value="on">ON</option>
					<option <?php if($onoroff == "off"){echo "SELECTED";}?> value="off">OFF</option>					
				</select>
			</fieldset>
			<?php
		}
}

function omekafeedpull_mysettings() {
	//register our settings
	register_setting( 'omekafeedpull-group', 'omekafeedpull_omekaroot' );
	$dcoreArray	=	array("Title", "Creator", "Subject", "Description", "Publisher", "Contributor", "Date", "Type", "Format", "Identifier", "Source", "Language", "Relation", "Coverage", "Rights");
	foreach($dcoreArray as $d)
		{
		register_setting( 'omekafeedpull-group', 'omekafeedpull_'. $d .'toggle');
		}
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
	   
	<h4>Toggle Dublin Core fields on/off</h4>
	    <?php
	    $dd	=	new dCoreDropDown;
		$dd->makeMenu("all");
	    ?>
	    <p class="submit">
	    	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	    </p>
	</form>
	</div>
<?php } ?>