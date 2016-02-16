=== Post Glue ===
Contributors: goblindegook, log_oscon
Tags: sticky posts, stickiness
Requires at least: 4.4
Tested up to: 4.4.2
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sticky posts for WordPress, improved.

== Description ==

Sticky posts for WordPress, improved.

This plugin addresses the following issues and limitations found in the core's implementation of sticky posts:

* Only the core `post` type supports this feature.
* Sticky posts are added in front of your homepage's results, yielding more posts than the configured per-page setting.
* Sticky posts reappear as you navigate further into the archive.
* Sticky posts are prepended to your results when querying specific posts with `post__in`.
* Difficult to include sticky posts in custom queries.

Some of these issues cause strange behaviour (like when using the REST API) and complicate the creation of custom homepage layouts.

At one point we decided to stop fighting the core and partially reimplemented the feature using custom post fields and meta queries, giving you:

* Sticky posts for all non-hierarchical post types.
* Post stickiness on post type and taxonomy archives.
* Respect for your configured per-page setting, with stickies spilling over onto the next page.
* Duplicates filtered from your archive pages.
* The ability to sort by stickiness on virtually any `WP_Query` lookup.
* Support for the [`is_sticky()`](https://developer.wordpress.org/reference/functions/is_sticky/) function.
* A predictable WordPress REST API experience.
* A slight SQL query overhead (sorry!)

Post Glue will continue to save post IDs to the `sticky_posts` option, meaning you'll retain some core functionality even if you decide to stop using the plugin.

Please follow and contribute to Post Glue's development on [Github](https://github.com/log-oscon/post-glue).

== Installation ==

= Using [Composer](https://getcomposer.org) =

1. Install the plugin package using `composer require logoscon/post-glue`.

= Using the WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'post glue'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `post-glue.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `post-glue.zip`
2. Extract the `post-glue` directory to your computer
3. Upload the `post-glue` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Upgrade Notice ==

== Changelog ==

= 1.0.0 =

* Initial release.
