=== MEXP Flickr ===
Contributors:      akeda
Tags:              media explorer, mexp, flickr, media
Requires at least: 3.6
Tested up to:      3.8
Stable tag:        trunk
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Flickr extension for the Media Explorer.

== Description ==

This plugin is an extension for [Media Exporer](https://github.com/Automattic/media-explorer/) plugin that adds Flickr service.
This extension allows user to search photos from Flickr by text, tags and user ID.

**Development of this plugin is done [on GitHub](https://github.com/gedex/mexp-flickr). Pull requests welcome.**

== Installation ==

1. Upload MEXP Flickr plugin to your blog's `wp-content/plugins/` directory and activate.
2. Add mu-plugin, say `wp-content/mu-plugins/mexp-flickr-api-key.php`, with following content:

   ```
   <?php

   add_filter( 'mexp_flickr_api_key', 'mexp_flickr_api_key_callback' );
   function mexp_flickr_api_key_callback() {
     return 'YOUR_FLICKR_API_KEY';
   }
   ```

== Screenshots ==

1. Search photos from Flickr within media explorer

== Changelog ==

= 0.1.0 =
Initial release
