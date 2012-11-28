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
$dcoreArray	=	array("Title", "Creator", "Subject", "Description", "Publisher", "Contributor", "Date", "Type", "Format", "Identifier", "Source", "Language", "Relation", "Coverage", "Rights");
foreach($dcoreArray as $d)
	{
		add_option('omekafeedpull_'. $d .'toggle', 'on', '', 'yes');
	}
}

function omekafeedpull_remove() {
/* Deletes the database field */
delete_option('omekafeedpull_omekaroot');
$dcoreArray	=	array("Title", "Creator", "Subject", "Description", "Publisher", "Contributor", "Date", "Type", "Format", "Identifier", "Source", "Language", "Relation", "Coverage", "Rights");
foreach($dcoreArray as $d)
	{
		delete_option('omekafeedpull_'. $d .'toggle');
	}
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
  include("views/configform.php");
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
		//echo $existingvalue[0]; //debug
		echo $content;
		if(is_numeric($existingvalue[0]))
			{
			$pulledpork	=	new XmlPuller;
			$pulledpork->parseXml();
			//$pulledpork->displayallmeta();
			$pulledpork->allDublinCore();
			
			//why are they seperate? so we can reshuffle them of course.
			//$pulledpork->displayameta('Subject', 'Objects Beware');
			//$pulledpork->displayameta('Language', 'Idioma?');
			$pulledpork->displayameta('fulltext', 'Objects Beware');
			$pulledpork->displayfiles('files');
			}
		}
}

class XmlPuller {

		private $dcoreArray	=	array("Title", "Creator", "Subject", "Description", "Publisher", "Contributor", "Date", "Type", "Format", "Identifier", "Source", "Language", "Relation", "Coverage", "Rights");

		public function __construct()
			{
				//find the id of the post we're working with
				$this->pid	=	get_the_ID(); 	
  				$builder = get_post_meta($this->pid, 'omeka_id');
				$this->evue = $builder[0];
				$this->direc = get_option('omekafeedpull_omekaroot'); 
				//echo "instantiatedm/// $this->pid  $this->evue";//debug
			}
			
		public function allDublinCore()
			{
				foreach($this->dcoreArray as $d)
					{
						if($this->whatisthere($d) == "on")	
							{$this->displayameta($d, $d);}
					}
			}
		
		
		public function parseXml()
			{
				$this->xmlset	=	$this->puller();
				$fulltext = $this->xmlset->fulltext;
				$this->fulltext = $this->turnintohtml($fulltext);
				//cache the breadcrumb
				wp_cache_set('xml-breadcrumb', $this->xmlset->breadcrumb);
			}
			

		private function turnintohtml($string)
			{
				//separate because I suspect down the road we might
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

		public function displayfiles($filesorlinks)
			{
				$alttag	= wp_cache_get('xml-title');
				foreach($this->xmlset->files->attachment as $attachment)
					{
					$mime			= 	$attachment->mime;
					$highreslink	= 	$attachment->download;
					$thumbnail 		=	$attachment->fullsize;
					//not used
					$literalthumbnail = $attachment->thumbnail;
						if(strstr($attachment->mime, 'image'))
							{//case there's an image...
							if($filesorlinks == "files")
								{include('views/imagebox.php');}
							else 
								{include('views/textimages.php');	}
							}
						else 
							{
							include('views/downloaddocument.php');
							}
					}
				
			}

		public function displayameta($whichmeta, $titular)
			{
				//this will display a single piece of meta data which you call
				//on a per 'name' basis
				
				//$titular
				//we also take a custom title so that it doesn't have to
				//be the official the dublin core name.
				//special cases of data that aren't made from the normal loop
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
						if(is_null($whichmeta))
							{	}
						else 
							{
								if($this->whatisthere($whichmeta) == "on")
								{
								$nameofvar	= str_replace(' ', '', $whichmeta);
								$unipar		=	$this->metaswitcher($whichmeta);
								$unititle	=	 $titular;
								if($unipar)
									{
										include("views/eachbox.php");
									}
								}
							}
					}
			}

	public function getbreadcrumb()
		{
			$yourbcumb	=	wp_cache_get('xml-breadcrumb');
			return $yourbcumb;
		}

	private function metaswitcher($whichmeta)
		{
			switch($whichmeta)
				{
				case "Title":
					$return = $this->xmlset->title;
					wp_cache_set('xml-title', $return);
					break;
				case "Link":
					$return = $this->xmlset->link;
					break;
				case "Description":
					$return = $this->xmlset->description;
					break;
				case "Contributor":
					$return = $this->xmlset->contributor;
					break;
				case "Coverage":
					$return = $this->xmlset->coverage;
					break;
				case "Creator":
					$return = $this->xmlset->creator;
					break;
				case "Date":
					$return = $this->xmlset->ofdate;
					break;
				case "Format":
					$return = $this->xmlset->format;
					break;
				case "Identifier":
					$return = $this->xmlset->identifier;
					break;
				case "Language":
					$return = $this->xmlset->language;
					break;
				case "Publisher":
					$return = $this->xmlset->publisher;
					break;
				case "Relation":
					$return = $this->xmlset->relation;
					break;
				case "Rights":
					$return = $this->xmlset->rights;
					break;
				case "Source":
					$return = $this->xmlset->source;
					break;
				case "Subject":
					$return = $this->xmlset->subject;
					break;
				case "Type":
					$return = $this->xmlset->type;
					break;
				case "Breadcrumb":
					$return = $this->xmlset->breadcrumb;
					break;
				case "Fulltext":
					$return = $this->xmlset->fulltext;
					break;
				case "Files":
					$return = $this->xmlset->files;
					break;
				}
			//since the xml has whitespace in NULL fields,
			//lets trim so we can determine which fields
			//are truly NULL.
			return trim($return);	
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
			
		public function displayallmeta()
			{
				//this displays every piece of metadata in one long running 
				// list. also only displays the official dublin core metadata
				// field name. this most closely resembles what one would see
				// on a default omeka page. 
				$xmlset	=	$this->puller();
				foreach($xmlset as $cork)
					{
						if($this->whatisthere($cork[name]) == "on")
							{
							echo $cork['name'];
							echo $cork['value'];
							}
						else {
						}
					}
			}
			
		public function puller()
			{ 
				$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
				$this->url = site_url(''. $this->direc .'/items/show/id/'.$this->evue.'?output=axml');
				//echo "$this->url ";//debug
				$xml = simplexml_load_file($this->url, NULL, LIBXML_NOCDATA);
				//print_r($xml);//debug
				return $xml;
				
			}
}
?>