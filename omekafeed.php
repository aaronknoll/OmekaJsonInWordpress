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
		
		$pulledpork	=	new JsonPuller;
		$pulledpork->puller();
		
		$jj = 0;
		//decode the JSON Feed of featured OMEKA items
		//$json = file_get_contents("http://lw4.gc.cuny.edu/_dev_push/archive/items/show/id/1?output=omeka-xml");
	 	//$xml = simplexml_load_file('http://lw4.gc.cuny.edu/_dev_push/archive/items/show/id/1?output=omeka-xml');
	



		$url = 'archive/items/show/id/1?output=omeka-xml';
		$str = file_get_contents($url);
		echo $str;
		}
}

class JsonPuller {

		public function __construct()
			{
				//find the id of the post we're working with
				$this->pid	=	get_the_ID(); 	
  				$builder = get_post_meta($this->pid, 'omeka_id');
				$this->evue = $builder[0];
				$this->direc = get_option('omekafeedpull_omekaroot'); 
				//echo "instantiatedm/// $this->pid  $this->evue";//debug
			}
			
		public function parseJson($url)
			{

			}
			
		public function puller()
			{ 
				//$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
				//$this->url = site_url(''. $this->direc .'/admin/items/show/id/'.$this->evue.'?output=dcmes-xml');
				//echo "$this->url ";//debug
				//fopen($this->url);
				//$file = file_get_contents($this->url, true);
				//var_dump($file);
				//$this->parseJson($this->url);
				//echo $url;
//				ncoded 	= urlencode($this->url);
				//echo $encoded;
				//$xml = simplexml_load_file($this->url, 'SimpleXMLElement', LIBXML_NOCDATA);
				

				 //foreach (libxml_get_errors() as $error) 
				 // {
				 //   echo "\t", $error->message;
				 // }   
//
				//$xml = file_get_contents($this->url, true, $context) or die("ds");
				//print_r($xml);
				//echo $xml;
			}
	
}
?>