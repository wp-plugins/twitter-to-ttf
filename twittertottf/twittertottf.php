<?php
/*
Plugin Name: Twitter to TTF
Plugin URI: http://www.kieranmasterton.com/twittertottf
Description: Takes your latest Tweet and creates a transparent png with the text rendered in your choice of TTF.
Author: Kieran Masterton
Version: 0.1
Author URI: http://www.kieranmasterton.com
*/


if ($_GET["twitterid"]){
	
# Load twitter class. 
require_once ('twitterclass.php');

# Declare some variables.
$id = $_REQUEST["twitterid"];
$textcolor = $_REQUEST["textcolor"];
$bgcolor = $_REQUEST["bgcolor"];
$trans = $_REQUEST["trans"];

# Retrieve Twitter Status
$t= new twitter(); 
$status = $t->userTimeline($id);
$something = $status->status->text;

if($status===false){ 
   $something = "ERROR!";
 }

	# Set the content-type
	header("Content-type: image/png");
	
	# Apply the wordwrap
	$something = wordwrap($something, 25, "\n");
	
	# Count how many times the text wraps and adjust image height accordingly.
	$wrap_count = substr_count($something, "\n");
	
	# In case the text doesn't wrap.
	if($wrap_count == 0){
		$wrap_count = 1;
	}
	
	# Multiply the number of times the texts wraps by 30px.
	$height = $wrap_count * 30;
	$height = $height + 20;
	
	# Create the image
	$im = imagecreatetruecolor(250, $height);

	# Lets convert those values.
	$text_array = (sscanf($textcolor, '%2x%2x%2x'));
	$bg_array = (sscanf($bgcolor, '%2x%2x%2x'));

	# Create some colors
	$bgcreatecolor = imagecolorallocate($im, $bg_array[0], $bg_array[1], $bg_array[2]);
	$textcreatecolor = imagecolorallocate($im, $text_array[0], $text_array[1], $text_array[2]);

	imagefilledrectangle($im, 0, 0, 250, $height, $bgcreatecolor);
	if ($trans){
		imagecolortransparent ($im, $bgcreatecolor);
	}
	
	# The text to draw
	$text = $something;
	# Replace path by your own font path
	$font = 'font.ttf';

	# Add the text
	imagettftext($im, 15, 0, 10, 20, $textcreatecolor, $font, $text);

	# Using imagepng() results in clearer text compared with imagejpeg()
	imagepng($im);
	imagedestroy($im);

}else{

# One init function for everything widgety.
function widget_twittertottf_init() {

    # Check for widget API functions.
    if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
        return; // ...and if not, exit gracefully from the script.

    # Function to output sidebar magic.
    function widget_twittertottf($args) {

        # Extract them Args.
        extract($args);

        # Collect widget options or set defaults.
        $options = get_option('widget_twittertottf');
        $twitterid = empty($options['twitterid']) ? '15874117' : $options['twitterid'];
        $textcolor = empty($options['textcolor']) ? '#000000' : $options['textcolor'];
        $bgcolor = empty($options['bgcolor']) ? '#ffffff' : $options['bgcolor'];
        $trans = empty($options['trans']) ? 'on' : $options['trans'];
        
        # Kill the leading hash.
        $textcolor = ereg_replace("#", "", $textcolor);
        $bgcolor = ereg_replace("#", "", $bgcolor);
   
   		# Lets output the image.
?>
<img src="<?php echo bloginfo('url'); ?>/wp-content/plugins/twittertottf/twitter.png" border="0" /><br />
<img src="<?php echo bloginfo('url'); ?>/wp-content/plugins/twittertottf/twittertottf.php?twitterid=<?php echo $twitterid; ?>&textcolor=<?php echo $textcolor; ?>&bgcolor=<?php echo $bgcolor; ?>&trans=<?php echo $trans; ?>" vspace="5" />
<br /><br />
<?php   
    }

	# Function to run the user option control.
    function widget_twittertottf_control() {

       # Grab user defined options.
        $options = get_option('widget_twittertottf');

        # Was it submitted?
        if ( $_POST['twittertottf-submit'] ) {
            # Clean up that mess!
            $newoptions['twitterid'] = strip_tags(stripslashes($_POST['twittertottf-twitterid']));
       		$newoptions['textcolor'] = strip_tags(stripslashes($_POST['twittertottf-textcolor']));
       		$newoptions['bgcolor'] = strip_tags(stripslashes($_POST['twittertottf-bgcolor']));
	       	$newoptions['trans'] = strip_tags(stripslashes($_POST['twittertottf-trans']));
	       	
        	# If user options don't match control, revert.
       	 	if ( $options != $newoptions ) {
            	$options = $newoptions;
            	update_option('widget_twittertottf', $options);
        	}
	    }
	     
        # Valid HTML please.
        $twitterid = htmlspecialchars($options['twitterid'], ENT_QUOTES);
        $textcolor = htmlspecialchars($options['textcolor'], ENT_QUOTES);
        $bgcolor = htmlspecialchars($options['bgcolor'], ENT_QUOTES);
        $trans = htmlspecialchars($options['trans'], ENT_QUOTES);
       
	    # Control form output.
?>
        <div>
        <label for="twittertottf-twitterid" style="line-height:35px;display:block;">Twitter ID: <input type="text" id="twittertottf-twitterid" name="twittertottf-twitterid" value="<?php echo $twitterid; ?>" /></label>
        <label for="twittertottf-textcolor" style="line-height:35px;display:block;">Text Color: <input type="text" id="twittertottf-textcolor" name="twittertottf-textcolor" value="<?php echo $textcolor; ?>" /></label>
        <label for="twittertottf-bgcolor" style="line-height:35px;display:block;">Background Color: <input type="text" id="twittertottf-bgcolor" name="twittertottf-bgcolor" value="<?php echo $bgcolor; ?>" /></label>
        <label for="twittertottf-trans" style="line-height:35px;display:block;">Transparent BG? <input type="checkbox" id="twittertottf-trans" name="twittertottf-trans" <?php if ($trans){ echo "CHECKED"; } ?> /></label>
        <input type="hidden" name="twittertottf-submit" id="twittertottf-submit" value="1" />
       </div>
    <?php
    # End control widget.
    }

    // Lets register this widget!
    register_sidebar_widget('Twitter To TTF', 'widget_twittertottf');

    // And the control.
    register_widget_control('Twitter To TTF', 'widget_twittertottf_control');
}

// Add widget to the dynamic sidebar.
add_action('plugins_loaded', 'widget_twittertottf_init');

}