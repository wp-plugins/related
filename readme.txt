=== Related ===
Contributors: mpol
Tags: related posts, related, post, linked posts, linked, widget, post2post, posts2posts
Requires at least: 3.3
Tested up to: 3.8
Stable tag: trunk

A simple 'related posts' plugin that lets you select related posts manually.

== Description ==

A simple 'related posts' plugin that lets you select related posts manually. Supports any post types in WordPress, including custom ones.


Features:

* Add related posts to your blog posts, pages etc.
* Choose from posts, pages or any other post type
* Support for custom post types
* Re-order related posts via drag and drop
* Widget that shows the related posts
* Custom markup possible, or simply use the default output

The plugin was written to have the option to add related posts to each blog post using a simple but functional plugin. You can select the related posts yourself manually.

To display the related posts, you can use the widget that is included.

If you want more control, simply add the following line in your template, inside the WordPress loop.

	<?php global $related; echo $related->show(get_the_ID()); ?>

For advanced options, see the installation docs.

= Languages =

* nl_NL [Marcel Pol](http://timelord.nl)

== Installation ==

**Option 1 - Automatic install**

Use the plugin installer built into WordPress to search for the plugin. WordPress will then download and install it for you.

**Option 2 - Manual install**

1. Make sure the files are within a folder.
2. Copy the whole folder inside the wp-content/plugins/ folder.
3. In the backend, activate the plugin. You can now select related posts when you create or edit blog posts, pages etc.

**How to display the related posts on your website**

The related posts are displayed by adding

	<?php global $related; echo $related->show($post_id); ?>

to your template. Replace `` $post_id `` with a post ID. If you call it within the WordPress loop, you can use

	<?php global $related; echo $related->show(get_the_ID()); ?>

You have the option of either outputting a pre-formatted list or returning a PHP array of related posts to customise the
markup yourself.

**Examples**

*Example 1: Using the default output*

	<?php global $related; echo $related->show(get_the_ID()); ?>

This can be called within the WordPress loop. It will output a `` <ul> `` list with links.

*Example 2: Returning an array*

	<?php
		global $related;
		$rel = $related->show(get_the_ID(), true);
	?>

With the second argument set to true, it will return an array of post objects. Use it to generate your own custom markup.
Here is an example:

	<?php
		global $related;
		$rel = $related->show(get_the_ID(), true);

		// Display the title of each related post
		foreach ($rel as $r) :
			echo $r->post_title . '<br />';
		endforeach;
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
By default, the plugin will split the select boxes into max 50 posts, so it's easier to handle for the user.
Also, you can select on the Options page to not list all post types. This will trim down the number of posts that are listed.

= When I delete the plugin, will it delete the related posts data? =

With version 1.1, all data remains in the database when the plugin files are deleted through the plugins page in WordPress. So if you accidentally delete the plugin, or if you decide to install it again later, your data should still be there.

= Is this plugin actively maintained? =

Yes, it is again actively maintained.

== Screenshots ==

1. Choosing related posts in the edit post screen

== Changelog ==

= 1.4.1 =
* Update nl_NL

= 1.4 =
* Now includes a widget

= 1.3.2 =
* Move styling to stylesheet

= 1.3.1 =
* On blogs with many posts, split the select box in multiple select boxes

= 1.3 =
* Add options page:
* Only get shown on selected post types
* Only list selected post types to select as related post

= 1.2.1 =
* Add localisation
* Add nl_NL
* Only make an instance in the init function

= 1.2 =
* Don't overwrite default post
* Switch from jquery.live to jquery.on, requires WP 3.3 at least

= 1.1.1 =
* Minor rewrites that may prevent interference with other plugins

= 1.1 =
* Bugfix: related posts are now correctly saved (deleted) when all related posts are removed from the current post
* Feature: all post types in WordPress are now supported (including custom ones)
* Improvement: select box now sorts posts by title and displays post type
* Improvement: current post is now excluded from the list of posts
* Improvement: data now remains stored in database when plugin is deleted, to avoid accidental loss of data
* Improvement: general code quality improvements

= 1.0 =
* Initial release. No known issues.

== Upgrade Notice ==

Either let WordPress do the upgrade or just overwrite the files.
