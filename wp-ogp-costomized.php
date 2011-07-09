<?php
/*
Plugin Name: WP-OGP customized
Plugin URI: http://ietomi.is-blog.net
Description: This is a plugin to add Open Graph Protocol Data to the metadata of your WordPress blog.
Version: 0.0.3
Author: David Miller (Modified by Masayuki Ietomi)
Contributor: Joe Crawford
Author URI: http://www.millerswebsite.co.uk
License: GPL2
*/
// http://opengraphprotocol.org/

/*  This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * These are initially blank, to have a like button, at least one of these must be set
 */

include_once dirname(__FILE__) . '/libs/class-setting-page.php';
include_once dirname(__FILE__) . '/libs/class-form-component.php';

load_plugin_textdomain('wp-ogp-costomized', '', dirname( plugin_basename( __FILE__ ) ) . '/lang');

define('OGPT_PREFIX', 'wpogp-');
define('OGPT_DEFAULT_TYPE', 'website');
define('OGPT_VERSION', '0.0.3');

$ogpt_fields = array(
	'fb:admins' => array(
		'note' => __('A comma-separated list of Facebook user IDs that administers this site. You can find your user id by visiting <a href="http://apps.facebook.com/what-is-my-user-id/" target="_blank">http://apps.facebook.com/what-is-my-user-id/</a>', 'wp-ogp-costomized'),
		'style' => 'width: 200px'
	),
	'fb:app_id' => array(
		'note' => __('A Facebook Platform application ID that administers this site.', 'wp-ogp-costomized'),
		'style' => 'width: 200px'
	)
);

$ogpt_configs = array(
	'custom_field_key' => array(
		'label' => __('Custom field key', 'wp-ogp-costomized'),
		'note' => __('The custom field key that is used by og:description.', 'wp-ogp-costomized'),
		'style' => 'width: 200px',
		'default' => 'description'
	),
	'using_post_excerpt' => array(
		'label' => __('Using post excerpt', 'wp-ogp-costomized'),
		'type' => 'checkbox',
		'options' => 1,
		'note' => __('The og:description is used by the post excerpt.', 'wp-ogp-costomized'),
		'default' => 0
	),
	'extraction_type' => array(
		'label' => __('Text extraction type', 'wp-ogp-costomized'),
		'options' => array('word' => __('Word Count', 'wp-ogp-costomized'), 'character' => __('Character Count', 'wp-ogp-costomized')),
		'separator' => "<br />",
		'type' => 'radio',
		'default' => 'word'
	),
	'desc_size' => array(
		'label' => __('og:description max size', 'wp-ogp-costomized'),
		'note' => __('The max word count or character length of the og:description.', 'wp-ogp-costomized'),
		'style' => 'width: 100px',
		'default' => 75
	),
	'thumbnail_type' => array(
		'label' => __('Using og:image type', 'wp-ogp-costomized'),
		'default' => 'single',
		'type' => 'radio',
		'separator' => '<br />',
		'options' => array('single' => __('Post thumbnail', 'wp-ogp-costomized'), 'multiple' => __('Images used in post content', 'wp-ogp-costomized'))
	),
	'thumbnail_amount' => array(
		'label' => __('Max og:image amount', 'wp-ogp-costomized'),
		'default' => 1,
		'note' => __('The max og:image amount when the using og:image type is checked by "Images used in post content". If "0" is set, og:image is used from all.', 'wp-ogp-costomized')
	),
	'force_using_default_image' => array(
		'label' => __('Force using default image', 'wp-ogp-costomized'),
		'default' => 0,
		'options' => 1,
		'type' => 'checkbox',
		'note' => __('The default image is anytime used when og:image type is "Images used in post content".', 'wp-ogp-costomized')
	),
	'default_image' => array(
		'label' => __('Default image URL', 'wp-ogp-costomized'),
		'style' => 'width: 98%'
	),
	'auto_include' => array(
		'label' => __('Include automatically og tags', 'wp-ogp-costomized'),
		'type' => 'checkbox',
		'options' => 1,
		'default' => 1,
	)
);

$ogpt_default_options = array(
	'label' => '',
	'note' => '',
	'value' => '',
	'type' => 'text',
);

$WpOgpPage = new WpOgp_SettingPage('options', 'wpogp', 'WP-OGP costomized', 'WP-OGP costomized', 'manage_options');
$WpOgpDefaultSection = $WpOgpPage->createSection();

foreach ($ogpt_fields + $ogpt_configs as $settingKey => $settingOptions) {
	$settingOptions += $ogpt_default_options;
	$settingOptions['name'] = OGPT_PREFIX . $settingKey;

	$label = $settingOptions['label'] ? $settingOptions['label'] : $settingKey;
	$type = $settingOptions['type'];
	unset($settingOptions['label'], $settingOptions['type']);

	$WpOgpDefaultSection->createField($settingKey, $label, $type, $settingOptions);
}

add_theme_support( 'post-thumbnails' );

function wpogp_init() {
	global $ogpt_fields, $ogpt_configs;

	foreach ($ogpt_fields + $ogpt_configs as $settingKey => $settingOptions) {
		if (!empty($settingOptions['default']) && !get_option(OGPT_PREFIX . $settingKey)) {
			add_option(OGPT_PREFIX . $settingKey, $settingOptions['default']);
		}
	}
}

add_action('admin_init', 'wpogp_init');

function get_the_post_thumbnail_src($img)
{
	return (preg_match('~\bsrc="([^"]++)"~', $img, $matches)) ? $matches[1] : '';
}

function get_the_post_thumbnail_srcs($content)
{
	return (preg_match_all('~img.+?src="([^"]+?)"~', $content, $matches)) ? array_unique($matches[1]) : array();
}

function wpogp_plugin_path() {
	return get_option('siteurl') .'/wp-content/plugins/' . basename(dirname(__FILE__));
}

function wpogp_image_url_default() {
	// default image associated is in the plugin directory named "default.png"
	$defaultImage = get_option(OGPT_PREFIX . 'default_image');
	return empty($defaultImage) ? wpogp_plugin_path() . '/default.jpg' : $defaultImage;
}

function wpogp_image_url() {
	global $post;

	$image = get_the_post_thumbnail_src(get_the_post_thumbnail($post->ID));

	if ( empty($image) )
		{ return wpogp_image_url_default();}
	else
		{ return $image; }

}

function wpogp_image_urls() {
	global $post;
	return get_the_post_thumbnail_srcs($post->post_content);
}

function wpogp_set_data() {
	global $wp_query, $ogpt_fields;
	$data = array();
	if (is_home()) :
		$data['og:title'] = get_bloginfo('name');
		$data['og:type'] = OGPT_DEFAULT_TYPE;
		$data['og:image'] = wpogp_image_url_default();
		//$data['image_src'] = wpogp_image_url_default();
		$data['og:url'] = get_bloginfo('url');
		$data['og:site_name'] = get_bloginfo('name');
	elseif (is_single() || is_page()):
		$thumbnail_type = get_option(OGPT_PREFIX . 'thumbnail_type');
		$data['og:title'] = get_the_title();
		$data['og:type'] = 'article';

		if ($thumbnail_type == 'multiple') {
			$force_using_default_image = (bool)get_option(OGPT_PREFIX . 'force_using_default_image');
			$max_thumbnail_count = (int)get_option(OGPT_PREFIX . 'thumbnail_amount');
			$data['og:image'] = array();
			//$data['image_src'] = array();

			foreach (wpogp_image_urls() as $i => $image) {
				if ($max_thumbnail_count > 0 && count($data['og:image']) >= $max_thumbnail_count) {
					break;
				}

				if (
					!($imageSizes = @getimagesize($image))
					|| $imageSizes[0] < 50 // min width is 50px
					|| $imageSizes[1] < 50 // min height is 50px
					|| ($imageSizes[0] / $imageSizes[1]) > 3 // max aspect ratio is 3:1
					|| ($imageSizes[1] / $imageSizes[0]) > 3 // max aspect ratio is 3:1
				) {
					continue;
				}

				$data['og:image'][] = $image;
				//$data['image_src'][] = $image;
			}

			if ($force_using_default_image || empty($data['og:image'])) {
				$data['og:image'][] = wpogp_image_url();
				//$data['image_src'][] = wpogp_image_url();
			}

		} else {
			$data['og:image'][] = wpogp_image_url();
			//$data['image_src'][] = wpogp_image_url();
		}

		$data['og:url'] = get_permalink();
		$data['og:site_name'] = get_bloginfo('name');
	else:
		$data['og:title'] = get_bloginfo('name');
		$data['og:type'] = 'article';
		$data['og:image'] = wpogp_image_url();
		//$data['image_src'] = wpogp_image_url();
		$data['og:url'] = get_bloginfo('url');
		$data['og:site_name'] = get_bloginfo('name');
	endif;

	foreach ($ogpt_fields as $key => $value) {
		$default = isset($value['default']) ? $value['default'] : '';
		$data[$key] = get_option(OGPT_PREFIX . $key, $default);
	}

	return $data;
}

function wpogp_add_head() {
	static $added = false;

	if (!$added) {
		$data = wpogp_set_data();
		echo get_wpogp_headers($data);
		$added = true;
	}
}

function get_wpogp_headers($data) {
	if (!count($data)) {
		return;
	}
	$out = array();
	$out[] = "<!-- BEGIN: WP-OGP costomized by http://inspire-tech.jp Version: " . OGPT_VERSION . "  -->";
	foreach ($data as $property => $content) {
		foreach ((array)$content as $i => $_content) {
			if ($_content != '') {
				$out[] = get_wpogp_tag($property, $_content);
			} else {
				$comment = "<!--{$property}";

				if (count((array)$content) > 1) {
					$comment .= "({$i})";
				}

				$comment .= " value was blank-->";
				$out[] = $comment;
			}
		}
	}
	return implode("\n", $out);
}

function get_wpogp_tag($property, $content) {
	return "<meta property=\"{$property}\" content=\"".htmlentities($content, ENT_NOQUOTES, 'UTF-8')."\" />";
}

function wpogp_add_head_desc() {
	static $added = false;

	if (!$added) {
		$default_blog_desc = ''; // default description (setting overrides blog tagline)
		$post_desc_length  = (int)get_option(OGPT_PREFIX . 'desc_size'); // description length in # words for post/Page
		$post_use_excerpt  = (bool)get_option(OGPT_PREFIX . 'using_post_excerpt'); // 0 (zero) to force content as description for post/Page
		$extraction_type   = get_option(OGPT_PREFIX . 'extraction_type');
		$custom_desc_key   = get_option(OGPT_PREFIX . 'custom_field_key'); // custom field key; if used, overrides excerpt/content

		global $cat, $cache_categories, $wp_query, $wp_version;
		if(is_single() || is_page()) {
			$post = $wp_query->post;
			$post_custom = get_post_custom($post->ID);
			$custom_desc_value = $post_custom["$custom_desc_key"][0];

			if($custom_desc_value) {
				$text = $custom_desc_value;
			} elseif($post_use_excerpt && !empty($post->post_excerpt)) {
				$text = $post->post_excerpt;
			} else {
				$text = $post->post_content;
			}

			if ($extraction_type == 'word') {
				$text = str_replace(array("\r\n", "\r", "\n", "  "), " ", $text);
				$text = str_replace(array("\""), "", $text);
				$text = trim(strip_tags($text));
				$text = explode(' ', $text);
				if(count($text) > $post_desc_length) {
					$l = $post_desc_length;
					$ellipsis = '...';
				} else {
					$l = count($text);
					$ellipsis = '';
				}
				$description = '';
				for ($i=0; $i<$l; $i++)
					$description .= $text[$i] . ' ';

				$description .= $ellipsis;

			} else if ($extraction_type == 'character') {
				$text = str_replace(array("\r\n", "\r", "\n", "  "), "", $text);
				$text = str_replace(array("\""), "", $text);
				$text = trim(strip_tags($text));
				if(mb_strlen($text) > $post_desc_length) {
					$ellipsis = '...';
				} else {
					$ellipsis = '';
				}
				$description = mb_substr($text, 0, $post_desc_length) . $ellipsis;
			}
		} elseif(is_category()) {
			$category = $wp_query->get_queried_object();
			$description = trim(strip_tags($category->category_description));
		} else {
			$description = (empty($default_blog_desc)) ? trim(strip_tags(get_bloginfo('description'))) : $default_blog_desc;
		}

		if($description) {
			echo "\n<meta property=\"og:description\" content=\"".htmlentities($description, ENT_NOQUOTES, 'UTF-8')."\" />\n";
			echo "<!-- END: WP-OGP costomized by http://inspire-tech.jp Version: " . OGPT_VERSION . " -->\n";
		}

		$added = true;
	}
}

function wpogp_auto_include() {
	$auto_include = (bool)get_option(OGPT_PREFIX . 'auto_include');

	if ($auto_include) {
		wpogp_add_head();
		wpogp_add_head_desc();
	}
}

add_action('wp_head', 'wpogp_auto_include');
?>