<?php
/*********************
Start all the functions
at once for Reverie.
*********************/

// start all the functions
add_action('after_setup_theme','reverie_startup');

function reverie_startup() {

    // launching operation cleanup
    add_action('init', 'reverie_head_cleanup');
    
    // remove WP version from RSS
    add_filter('the_generator', 'reverie_rss_version');
    // clean up gallery output in wp
    add_filter('gallery_style', 'reverie_gallery_style');

    // enqueue base scripts and styles
    add_action('wp_enqueue_scripts', 'reverie_scripts_and_styles', 999);
    // ie conditional wrapper
    add_filter( 'style_loader_tag', 'reverie_ie_conditional', 10, 2 );
    
    // additional post related cleaning
    add_filter( 'img_caption_shortcode', 'reverie_cleaner_caption', 10, 3 );
    add_filter('get_image_tag_class', 'reverie_image_tag_class', 0, 4);
    add_filter('get_image_tag', 'reverie_image_editor', 0, 4);
    add_filter( 'the_content', 'reverie_img_unautop', 30 );

} /* end reverie_startup */


function reverie_head_cleanup() {
	// category feeds
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	// post and comment feeds
	remove_action( 'wp_head', 'feed_links', 2 );
	// EditURI link
	remove_action( 'wp_head', 'rsd_link' );
	// windows live writer
	remove_action( 'wp_head', 'wlwmanifest_link' );
	// index link
	remove_action( 'wp_head', 'index_rel_link' );
	// previous link
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	// start link
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	// links for adjacent posts
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	// WP version
	remove_action( 'wp_head', 'wp_generator' );
} /* end head cleanup */

// remove WP version from RSS
function reverie_rss_version() { return ''; }


// remove injected CSS from gallery
function reverie_gallery_style($css) {
  return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
}

/**********************
Enqueue CSS and Scripts
**********************/


// loading modernizr and jquery, and reply script
function reverie_scripts_and_styles() {
  if (!is_admin()) {
    // Register the main style
    wp_register_style( 'main-stylesheet', get_template_directory_uri() . '/css/style.css', array(), '201308151507', 'all' );
    	
    // modernizr (without media query polyfill)
    wp_register_script( 'modernizr', get_template_directory_uri() . '/js/vendor/custom.modernizr.js', array(), '2.6.2', false );
    
    // adding Foundation scripts file in the footer
    wp_register_script( 'foundation', get_template_directory_uri() . '/js/foundation-ck.js', array( 'jquery' ), '201311051313', true );
    
    if ($is_IE) {
       wp_register_script ( 'html5shiv', "http://html5shiv.googlecode.com/svn/trunk/html5.js" , false, true);
    }
    
    // enqueue styles and scripts
    wp_enqueue_style( 'main-stylesheet' );
    wp_enqueue_style( 'modernizr' );
    wp_enqueue_style( 'jquery' );
    wp_enqueue_script( 'foundation' );
    wp_enqueue_script( 'html5shiv' );

  }
}

// adding the conditional wrapper around ie stylesheet
// source: http://code.garyjones.co.uk/ie-conditional-style-sheets-wordpress/
function reverie_ie_conditional( $tag, $handle ) {
	if ( 'reverie-ie-only' == $handle )
		$tag = '<!--[if lt IE 9]>' . "\n" . $tag . '<![endif]-->' . "\n";
	return $tag;
}

/*********************
Post related cleaning
*********************/
/* Customized the output of caption, you can remove the filter to restore back to the WP default output. Courtesy of DevPress. http://devpress.com/blog/captions-in-wordpress/ */
function reverie_cleaner_caption( $output, $attr, $content ) {

	/* We're not worried abut captions in feeds, so just return the output here. */
	if ( is_feed() )
		return $output;

	/* Set up the default arguments. */
	$defaults = array(
		'id' => '',
		'align' => 'alignnone',
		'width' => '',
		'caption' => ''
	);

	/* Merge the defaults with user input. */
	$attr = shortcode_atts( $defaults, $attr );

	/* If the width is less than 1 or there is no caption, return the content wrapped between the [caption]< tags. */
	if ( 1 > $attr['width'] || empty( $attr['caption'] ) )
		return $content;

	/* Set up the attributes for the caption <div>. */
	$attributes = ' class="figure ' . esc_attr( $attr['align'] ) . '"';

	/* Open the caption <div>. */
	$output = '<figure' . $attributes .'>';

	/* Allow shortcodes for the content the caption was created for. */
	$output .= do_shortcode( $content );

	/* Append the caption text. */
	$output .= '<figcaption>' . $attr['caption'] . '</figcaption>';

	/* Close the caption </div>. */
	$output .= '</figure>';

	/* Return the formatted, clean caption. */
	return $output;
	
} /* end reverie_cleaner_caption */

// Clean the output of attributes of images in editor. Courtesy of SitePoint. http://www.sitepoint.com/wordpress-change-img-tag-html/
function reverie_image_tag_class($class, $id, $align, $size) {
	$align = 'align' . esc_attr($align);
	return $align;
} /* end reverie_image_tag_class */

// Remove width and height in editor, for a better responsive world.
function reverie_image_editor($html, $id, $alt, $title) {
	return preg_replace(array(
			'/\s+width="\d+"/i',
			'/\s+height="\d+"/i',
			'/alt=""/i'
		),
		array(
			'',
			'',
			'',
			'alt="' . $title . '"'
		),
		$html);
} /* end reverie_image_editor */

// Wrap images with figure tag. Courtesy of Interconnectit http://interconnectit.com/2175/how-to-remove-p-tags-from-images-in-wordpress/
function reverie_img_unautop($pee) {
    $pee = preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<figure>$1</figure>', $pee);
    return $pee;
} /* end reverie_img_unautop */
?>