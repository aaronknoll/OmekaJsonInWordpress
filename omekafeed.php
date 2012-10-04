<?php
/*
Plugin Name: Omeka Fees Pull from Json
Plugin URI: http://picturinghistory.gc.cuny.edu
Description: Pulls a feed from omeka into a page in Wordpress
Version: 0.1
Author: Aaron Knoll
Author URI: http://aaronknoll.com
License: GPL
*/

//INCLUDED FILES FOR EASIER REFERENCE
include "create_admin_page.php"; //code to make the page in the admin panel where options are set

//ACTIONS, HOOKS AND FILTERS. 

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'omekafeedpull_install'); 
/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'omekafeedpull_remove' );
add_action( 'add_meta_boxes', 'omekafeedpull_add_custom_box' );
/* Do something with the data entered */
add_action( 'save_post', 'omekafeedpull_save_postdata' );
//actually put the stuff on the page, our priority is high!
add_filter( 'the_content', 'omekapull_content_filter', 5 );
//add some widgets for each

//FUNCTIONS, OBJECTS AND OTHER EPHEMERA
function omekafeedpull_install() {
/* Creates new database field */
add_option('omekafeedpull_omekaroot', '/omeka', '', 'yes');
}

function omekafeedpull_remove() {
/* Deletes the database field */
delete_option('omekafeedpull_omekaroot');
}

// from here, we're going to add the custom fields to the "edit page" pages
/* Adds a box to the main column on the Post and Page edit screens */
function omekafeedpull_add_custom_box() {
    add_meta_box(
        'omekafeedpull_sectionid',
        __( 'Omeka item ID #', 'omekafeedpull_textdomain' ), 
        'omekafeedpull_inner_custom_box',
        'page'
    );
}

/* Prints the box content */
function omekafeedpull_inner_custom_box( $post ) {
  
  $post_id	=	get_the_ID(); 
		
  $existingvalue = get_post_meta($post_id, 'omeka_id');
  if(!$existingvalue)
  	{
  		$existingvalue = "Enter Number";
	}
  // verify things. 
  wp_nonce_field( plugin_basename( __FILE__ ), 'omekafeedpull_noncename' );

  //the form
  echo '<label for="omekafeedpull_new_field">';
       _e("Description for this field", 'omekafeedpull_textdomain' );
  echo '</label> ';
  echo '<input type="text" id="omekafeedpull_new_field" name="omekafeedpull_new_field" 
  		value="'. $existingvalue[0] .'" size="25" />';
  //add style later
  echo '<p class="important">Please note! Adding a number here
  		will cause the Json Feed of the appropriate Omeka archive
  		page to be pulled here. Other plugin settings may be over-ridden
  		here</p>';
}

/* When the post is saved, saves our custom data */
function omekafeedpull_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce( $_POST['omekafeedpull_noncename'], plugin_basename( __FILE__ ) ) )
      return;
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // and now that we're safe! Let's save that data. 
  $mydata = $_POST['omekafeedpull_new_field'];
  //either or, whatever is most appropriate. 
  add_post_meta($post_id, 'omeka_id', $mydata, true) or update_post_meta($post_id, 'omeka_id', $mydata);
}



function omekapull_content_filter( $content ) {
	//explanation:
	//if the box has a # in it, we're going to go grab that omeka page.
	//but if the field is blank [as it should be in most cases]
	//just display the post content ala usual. 
    if ( is_page() )
		{
		$post_id	=	get_the_ID(); 
		$existingvalue = get_post_meta($post_id, 'omeka_id');
		//echo $existingvalue; //debug
		
		$pulledpork	=	new XmlPuller;
		$pulledpork->puller();
		$pulledpork->parseXml();
		//$pulledpork->displayallmeta();
		$pulledpork->displayameta('Subject', 'Objects Beware');
		$pulledpork->displayameta('Language', 'Idioma?');
		$pulledpork->displayameta('fulltext', 'Objects Beware');
		}
}

class XmlPuller {

		public function __construct()
			{
				//find the id of the post we're working with
				$this->pid	=	get_the_ID(); 	
  				$builder = get_post_meta($this->pid, 'omeka_id');
				$this->evue = $builder[0];
				$this->direc = get_option('omekafeedpull_omekaroot'); 
				//echo "instantiatedm/// $this->pid  $this->evue";//debug
			}
			
		public function parseXml()
			{
				//at point where this function is called, the puller should exist.
				//ideally to save on queries to the page, we want to call
				//puller() at the beginning of the script, just after the header
				//and rely on the cache from wordpress the rest of the way. 
				
				$this->xmlarray	=	wp_cache_get('pulledXml');
				if(!$this->xmlarray)
					{
						//if you skip to this step directly, load the xml
						//and cache it. 
						//echo "there is nothing in the cache.";//debug
						$this->xmlarray =	$this->puller();
						//echo "now we have it?";//debug
					}
				else {
					//this is where we start parseing it, right?
					//echo "we have an array right here $this->xmlarray";//debug
				}
				
				//at this point with certainty we have an $xmlarray. 
				//**debug this is an early version. let's populate a series of
				//variables with our dublin core fields.
				
				$dublincorearray	=	$this->xmlarray->elementSetContainer->elementSet->elementContainer;
				$imagesarray		=	$this->xmlarray->fileContainer;
				
				//we're dealing with a pretty large xml file. so what we're doing is iterating
				//though the array of dublin core values and assigning a variable name and value
				//for each populated field.
				//print_r($dublincorearray);//debug
				//$numberofarrays	=	count($dublincorearray);//debug --do we have more than 1 array here?
				//echo $numberofarrays;//debug
				
				$irelandarray = array();
				$xx = 0; //iterator
				foreach($dublincorearray->element as $dubliner)
					{
						$irelandarray[$xx]['name']	=	$dubliner->name;
						$irelandarray[$xx]['value']	=	$dubliner->elementTextContainer->elementText->text;
						//echo $irelandarray[$xx]['name'] ."=>>>>". $irelandarray[$xx]['value'];
						//echo "<br />";
					
						//let's cache the value and make it available later.
						//wp_cache_set($irelandarray['name'], $irelandarray['value']);
					
						//and if we're already in the object, simplest. lt's assign a this variable
						$this->$irelandarray[$xx]['name'] = $irelandarray[$xx]['value'];
						
						$xx++;//iterator
					}
				//echo $this->Subject;//debug THIS MEANS ITS WORKING.
				$this->ireland	=	$irelandarray;
				
				if($imagesarray->file)
					{
						echo "we're im jere a...<br />";	//debug
						//because you could, but not normally, might have more than one. 
						$imagesinarrayform = array();
						$jj=0;
						foreach($imagesarray->file as $jacksonpollack)
							{
								//echo "<font color='red'>". $this->urlsplitter($jacksonpollack->src) ."</font>";
								$imagesinarrayform[$jj]	=	$jacksonpollack->src;
								$jj++;
							}
						if($imagesinarrayform)
							{ 	//echo $imagesinarrayform[0];
								$this->images = $imagesinarrayform;
							}
					}
				
			
				//and don't forget about fulltext.
				$fulltext = $this->xmlarray->itemType->elementContainer->element->elementTextContainer->elementText->text;
				
				//echo "<font color='green'>".  ."</font>";//debug
				$this->fulltext = $this->turnintohtml($fulltext);
			}

		private function turnintohtml($string)
			{
				//seperate because I suspect down the road we might
				//have other operations to run here.
				$replacer = nl2br($string);
				return $replacer;
			}
			
		private function urlsplitter($url)
			{
				//takes the url of the full size image and finds the right thumbnail
				//to display instead. 
				$replacer = str_replace('fullsize', 'fullsize', $url);
				return $replacer;
			}

		public function displayameta($whichmeta, $titular)
			{
				//$whichmeta
				//this will display a single piece of meta data which you call
				//on a per 'name' basis
				
				//$titular
				//we also take a custom title so that it doesn't have to
				//be the official the dublin core name.
				
				//special cases of data that aren't made from the normal loop
				//echo "<strong>";//debug
				if($whichmeta == "image")
					{
						//but if and only if there's images
						if($this->images)
							{
								//echo $this->images[0];
								foreach ($this->images as $snowflake)
									{
										echo "<img src='$snowflake'>";
									}
							}

					}
				elseif($whichmeta == "fulltext")
					{
						$unititle	=	 "Fulltext";
						$unipar		=	 $this->fulltext;
						include("views/eachbox.php");
					}
				else 
					{
						$unititle	=	 $titular;
						$unipar		=	 $this->$whichmeta;
						include("views/eachbox.php");
					
					}
				//echo "</strong>";//debug
			}
			
		public function displayallmeta()
			{
				//this displays every piece of metadata in one long running 
				// list. also only displays the official dublin core metadata
				// field name. this most closely resembles what one would see
				// on a default omeka page. 
				
				foreach($this->ireland as $cork)
					{
						echo $cork['name'];
						echo $cork['value'];
					}
				//echo $this->images;//display image
			}
			
		public function puller()
			{ 
				$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
				$this->url = site_url(''. $this->direc .'/items/show/id/'.$this->evue.'?output=omeka-xml');
				//echo "$this->url ";//debug
				$xml = simplexml_load_file($this->url);
				//print_r($xml);//debug
				
				//set the xml into the wp cache, to reduce the loading time.
				//we're going to break this apart for efficacy.
				wp_cache_set('pulledXml', $xml);
			}
}
?>