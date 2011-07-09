=== Plugin Name ===
Contributors: David_Miller
Tags: open graph protocol, facebook, metadata, ogp, open graph data
Requires at least: 3.0
Tested up to: 3.1-RC3
Stable tag: 1.0.5

This is a plugin to add Open Graph Protocol Data to the metadata of your WordPress blog.

== Description ==

This is a plugin to add [Open Graph Protocol](http://opengraphprotocol.org/) data to the metadata of your WordPress blog for better communication with Facebook and other services which use this type of data.

If you have any queries regarding this plugin, please visit [http://www.millerswebsite.co.uk](http://www.millerswebsite.co.uk/2011/01/23/wordpress-plugin-wp-ogp)

== Installation ==

1. Download and unzip the file `wp-ogp.zip`
2. Upload `wp-ogp/` to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. You should now have Open Graph Protocol Data `<meta>` tags added to the `<head>` of your WordPress Blog

== Screenshots ==
1. Admin Panel
2. The data added to your Wordpress Blog
3. Request for permission from app
4. Click set up new app
5. Name you application
6. The application control panel
7. The application ID

== Changelog ==

= Version 1.0.5 =
* Updated FAQ.
* Added function to use excerpt ( which is created by plugin from content ) as description when not on home page otherwise uses wordpress description.

= Version 1.0.4 =
* Changed output to UTF-8

= Version 1.0.3 =
* Added support for themes which do not support featured images - Plugin does not work at all with themes which do not support featured images, so this update is essential!
* Note : Sorry for constant updating - I'm new to working with Wordpress and I'm finding errors!

= Version 1.0.2 =
* Updated images to include a screen shot of the open graph protocol data added to your wordpress blog.

= Version 1.0.1 =
* Updated readme and fixed listing on Wordpress.org - it now looks like it should!

= Version 1.0 =
* First Release

== Frequently Asked Questions ==
= What does this plugin do exactly? =

* It adds meta data to your wordpress blog so it can better interact with facebook and other services that use the open graph protocol.

= Did you make this plugin all by yourself? =

* The original code for this plugin was written by Joe Crawford, but I have extensively modified it and hopfully improved upon what he originally wrote!

= Only the default image is being used, whats wrong? =

* You need to add an image as a featured image for this plugin to work correctly. 

= It doesn't seem to be working correctly, whats wrong? =

* You may not have entered your Facebook ID and your Facebook Application ID, if you have, double check it, and make sure it's correct!

= Why have you updated so many times since its been submitted? =

* I'm new to working with Wordpress, so there will be updates to correct errors to make sure it works perfectly for everyone who downloads this!

= How do I replace the default image? =

* Go to "wordpress directory"/wp-content/plugins/wp-ogp and replace default.jpg with another image with the same name! This only works for the default image, you need to add a featured image on the post page for it to work with articles.

= How do I register a new application with Facebook? =

* Go to http://www.facebook.com/developers and allow the developer application access to your account. As of writing this, there is an issue with this and you will be presented with an 404 error after it redirects you back to facebook. - Screenshot 3

* Go to http://www.facebook.com/developers and click on the right hand side click on +set up new app if you get the 404 error. - Screenshot 4

* If this does not work, go to http://www.facebook.com/developers/createapp.php - Screenshot 5

* Fill in the form, click create application - Screenshot 6

* On the left hand side click on Web site and your application id should be there! - Screenshot 7