=== Related ===
Contributors: mpol
Tags: related, post, related post, related posts, related content, similar posts, link, linked, linked post, linked posts, internal links, widget, post2post, posts2posts, posts 2 posts, pods
Requires at least: 3.3
Tested up to: 4.1
Stable tag: 1.6.0

A simple 'related posts' plugin that lets you select related posts manually.

== Description ==

A simple 'related posts' plugin that lets you select related posts manually. Supports any post types in WordPress, including custom ones.


Features:

* Add related posts to your blog posts, pages etc.
* Choose from posts, pages or any other post type.
* Support for custom post types.
* Re-order related posts via drag and drop.
* Widget that shows the related posts.
* Custom markup possible, or simply use the default output.
* Support for multilang plugins, like WPML and Qtranslate.

The plugin was written to have the option to add related posts to each blog post using a simple but functional plugin. You can select the related posts yourself manually.

To display the related posts, there are three options:

* You can use the widget that is included.
* Add PHP code to your template, see the installation docs.
* Use the content filter inside the settings.

For advanced options, see the installation docs.

= Languages =

* nl_NL [Marcel Pol](http://zenoweb.nl)
* sr_RS [Borisa Djuraskovic](http://www.webhostinghub.com)

If you care to translate this plugin into your own language, please send the translated po file to marcel at zenoweb dot nl.

== Installation ==

**Option 1 - Automatic install**

Use the plugin installer built into WordPress to search for the plugin. WordPress will then download and install it for you.

**Option 2 - Manual install**

1. Make sure the files are within a folder.
2. Copy the whole folder inside the wp-content/plugins/ folder.
3. In the backend, activate the plugin. You can now select related posts when you create or edit blog posts, pages etc.

**How to display the related posts on your website**

The related posts are displayed by adding

	<?php global $related; echo $related->show( $post_id ); ?>

to your template. Replace `` $post_id `` with a post ID. If you call it within the WordPress loop, you can use

	<?php global $related; echo $related->show( get_the_ID() ); ?>

You have the option of either outputting a pre-formatted list or returning a PHP array of related posts to customise the
markup yourself.

**Examples**

*Example 1: Using the default output*

	<?php global $related; echo $related->show( get_the_ID() ); ?>

This can be called within the WordPress loop. It will output a `` <ul> `` list with links.

*Example 2: Returning an array*

	<?php
		global $related;
		$rel = $related->show( get_the_ID(), true );
	?>

With the second argument set to true, it will return an array of post objects. Use it to generate your own custom markup.
Here is an example:

	<?php
		global $related;
		$rel = $related->show( get_the_ID(), true );

		// Display the title of each related post
		if( is_array( $rel ) && count( $rel ) > 0 ) {
			foreach ( $rel as $r ) {
				if ( is_object( $r ) ) {
					if ($r->post_status != 'trash') {
						echo get_the_title( $r->ID ) . '<br />';
					}
				}
			}
		}
	?>

If you want to run it with a real WordPress loop, then use it as follows. You can then use functions like the_content or the_excerpt.
But make sure you don't use the content filter for related posts, because you might get an endless stream of related posts that are related to each other :).

	<?php
		global $related;
		$rel = $related->show( get_the_ID(), true );

		// Display the title and excerpt of each related post
		if( is_array( $rel ) && count( $rel ) > 0 ) {
			foreach ( $rel as $r ) {
				if ( is_object( $r ) ) {
					if ($r->post_status != 'trash') {
						setup_postdata( $r );
						echo get_the_title( $r->ID ) . '<br />';
						the_excerpt();
					}
				}
			}
			wp_reset_postdata();
		}
	?>

== Frequently Asked Questions ==

= Who should use this plugin? =

People who want to list 'related posts' in their blog posts or pages, and want to choose the related posts manually themselves.

= Where does the plugin store its data? =

Data is stored in the existing postmeta table in the WordPress database. No additional tables are created.

= How many related posts can I add? =

As many as you like, there's no limit.

= I have many posts, how can I deal with that in the best way? =

There are 2 things that are done or possible.
The Javascript Chosen.js is being used so you can easily navigate through the select-box.
Also, you can select on the Options page to not list all post types. This will trim down the number of posts that are listed.

= When I delete the plugin, will it delete the related posts data? =

All data remains in the database when the plugin files are deleted through the plugins page in WordPress.
So if you accidentally delete the plugin, or if you decide to install it again later, your data should still be there.

= Is this plugin actively maintained? =

Yes, it is again actively maintained.

== Screenshots ==

1. Choosing related posts in the edit post screen
2. Widget in the frontend on Twenty Fourteen Theme

== Changelog ==

= 1.6.0 =
* 2015-03-01
* Support Qtranslate-X plugin.
* Place settingspage in own php-file.

= 1.5.9 =
* 2015-02-28
* Support attachments by showing posts with post_status 'inherit' as well.

= 1.5.8 =
* 2015-01-07
* Only show the content filter on single posts, not on blog.

= 1.5.7 =
* 2014-12-05
* Use chosen.js for easy select-boxes (thanks rembem).
* Use in_array instead of looping with foreach.
* Update nl_NL.

= 1.5.6 =
* 2014-10-22
* Test if the metakey really holds values and avoid PHP Warnings
* Improved examples in Readme

= 1.5.5 =
* 2014-10-21
* Add sr_RS Serbian Language (Borisa Djuraskovic)

= 1.5.4 =
* 2014-08-22
* Compatibility with Qtranslate and MQtranslate
* Don't show posts with status 'trash'.

= 1.5.3 =
* 2014-08-13
* Add option for content filter title
* sanitize values in update_option()

= 1.5.2 =
* 2014-08-08
* Only show header if there are related posts (content filter)

= 1.5.1 =
* 2014-05-10
* Show header above the related posts in content filter

= 1.5.0 =
* 2014-05-07
* Remember tab after submit

= 1.4.9 =
* 2014-05-05
* Better naming of variables

= 1.4.8 =
* 2014-05-02
* Add a filter for the content, with an option to use it
* Option page now uses tabs
* Update nl_NL

= 1.4.7 =
* 2014-04-18
* No need to add explicit support
* Rewrite save function, meta_key gets deleted correctly now

= 1.4.6 =
* 2014-04-15
* Support Widget Customizer in 3.9

= 1.4.5 =
* 2014-03-23
* Cleanup duplicate code

= 1.4.4 =
* 2014-03-22
* Add settings link to main plugin page

= 1.4.3 =
* 2014-03-18
* Also delete just added post

= 1.4.2 =
* 2014-02-14
* Fix post update on wp_update_post()

= 1.4.1 =
* 2013-12-17
* Update nl_NL

= 1.4 =
* 2013-12-13
* Now includes a widget

= 1.3.2 =
* 2013-12-07
* Move styling to stylesheet

= 1.3.1 =
* 2013-12-07
* On blogs with many posts, split the select box in multiple select boxes

= 1.3 =
* 2013-12-07
* Add options page:
* Only get shown on selected post types
* Only list selected post types to select as related post

= 1.2.1 =
* 2013-11-09
* Add localisation
* Add nl_NL
* Only make an instance in the init function

= 1.2 =
* 2013-11-09
* Don't overwrite default post
* Switch from jquery.live to jquery.on, requires WP 3.3 at least

= 1.1.1 =
* 2011-09-21
* Minor rewrites that may prevent interference with other plugins

= 1.1 =
* 2011-09-21
* Bugfix: related posts are now correctly saved (deleted) when all related posts are removed from the current post
* Feature: all post types in WordPress are now supported (including custom ones)
* Improvement: select box now sorts posts by title and displays post type
* Improvement: current post is now excluded from the list of posts
* Improvement: data now remains stored in database when plugin is deleted, to avoid accidental loss of data
* Improvement: general code quality improvements

= 1.0 =
* 2010-04-12
* Initial release. No known issues.

== Upgrade Notice ==

Either let WordPress do the upgrade or just overwrite the files.
