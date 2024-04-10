<?php
/*
Plugin Name:  Post Advert Banner 
Description: A Plugin that easily allows you to add advert banner within the post content
Version: 1.0.0
Author: Aramide Adetunji
Author URI: https://aramideadetunji.com
Text Domain: post-advert-banner
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class PostAdvertBannerPlugin {
    function __construct() {
        add_action( 'admin_menu', array($this, 'adminMenu') );
        if (get_option('banner_upload') &&  get_option('banner_placement') ) add_filter( 'the_content', array($this, 'insertBanner') );
    }

    function adminMenu() {
        add_menu_page( 'Post Advert Banner', 'Advert Banner', 'manage_options', 'postadvertbanner', array($this, 'postBannerPage'), 'dashicons-upload', 90 );
    }

    // function to insert advert inside the post content
    function insertBanner($content) {
        if (is_single()) { // Check if it's a single post
            $banner = get_option('banner_upload');
            $banner_placement = get_option('banner_placement');
        
            if ($banner != '' && $banner_placement != '') {
                // Regular expression to match paragraphs
                $paragraphs_regex = '/<p(.*?)>(.*?)<\/p>/i';
        
                // Find all paragraph matches in the content
                preg_match_all($paragraphs_regex, $content, $matches, PREG_OFFSET_CAPTURE);
        
                if (!empty($matches[0])) {
                    // Insert the banner after the specified number of paragraphs
                    $paragraph_to_insert_after = min($banner_placement, count($matches[0]));
        
                    // Get the position where the banner should be inserted
                    $position = $matches[0][$paragraph_to_insert_after - 1][1] + strlen($matches[0][$paragraph_to_insert_after - 1][0]);
        
                    // Insert the banner HTML
                    $banner_html = '<img src="' . $banner . '" alt="Banner Image">';
        
                    // Insert the banner into the content
                    $content = substr_replace($content, $banner_html, $position, 0);
                }
            }
        }
    
        return $content;
    }
    

    function handleForm(){
        if (isset($_POST['ourNonce']) && wp_verify_nonce($_POST['ourNonce'], 'postadvertbanner') && current_user_can('manage_options')) {
            // Sanitize and handle file upload
            if(isset($_FILES['banner_upload']) && !empty($_FILES['banner_upload']['name'])) {
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($_FILES['banner_upload'], $upload_overrides);
    
                if ($movefile && !isset($movefile['error'])) {
                    // File uploaded successfully, save its path or URL to the database
                    update_option('banner_upload', $movefile['url']); // You might want to save the URL or file path depending on your needs
                } else {
                    // Error handling if file upload fails
                    echo "File upload failed: " . $movefile['error'];
                }
            }
    
            // Sanitize and handle numeric input
            $banner_placement = isset($_POST['banner_placement']) ? intval($_POST['banner_placement']) : 0;
            update_option('banner_placement', $banner_placement);
    
            ?>
            <div>
                <p>Congrats, banner has been uploaded.</p>
            </div>
        <?php } else { ?>
            <div>
                <p>Sorry, you do not have permission to upload banner.</p>
            </div>
        <?php }
        
    }

    function postBannerPage() {  ?>
        <!-- build an input form to take image and paragraphs number to insert it in -->
        <div>
            <h1>Upload and insert banner in post page</h1>
            <?php if (isset($_POST['justsubmitted']) && $_POST['justsubmitted'] == "true") $this->handleForm(); ?>
            <form method="POST" enctype="multipart/form-data"> 
                <input type="hidden" name="justsubmitted" value="true" />
                <?php wp_nonce_field('postadvertbanner', 'ourNonce') ?>
                <div>
                    <label for="banner_upload">Upload Image</label>
                    <input type="file" name="banner_upload" accept="image/png, image/jpg, image/jpeg" required />
                </div>
                <div>
                    <label for="banner_placement">Banner position paragraph should be placed</label>
                    <input type="number" name="banner_placement" required />
                </div>
                <input type="submit" name="submit" id="submit" value="Insert banner">
            </form>
        </div> 
        
        <!-- Display the current banner and ad position -->
        <?php 
        $banner = get_option('banner_upload');
        $banner_placement = get_option('banner_placement');
    
        if ($banner != '' && $banner_placement != '') { ?>
            <div>
                <h3>Uploaded banner and banner position</h3>
                <p>Banner Image</p>
                <img src="<?php echo get_option('banner_upload'); ?>" alt="Banner Image" />
                <p>Banner position: <?php echo get_option('banner_placement'); ?></p>
            </div>

       <?php } 
    }
}


$postAdvertBannerPlugin = new PostAdvertBannerPlugin();