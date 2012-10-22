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
					{
						return $current;
					}
				else
					{
						return FALSE;
					}
			}
			
		}
	private function singleMenuIteration($element)
		{
			$onoroff	=	$this->whatisthere($element);
			?>
			<fieldset>
				<legend><?php echo $element; ?></legend>
				<select id="omekafeedpull_<?php echo $element; ?>toggle">
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
	    <?php
	    $dd	=	new dCoreDropDown;
		$dd->makeMenu("all");
		//$dd->makeMenu("Subject");
	    ?>
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