<?php
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
?>