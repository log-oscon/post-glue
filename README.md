# Post Glue

[![Latest Stable Version](https://poser.pugx.org/logoscon/post-glue/v/stable.svg)](https://packagist.org/packages/logoscon/post-glue) [![Latest Unstable Version](https://poser.pugx.org/logoscon/post-glue/v/unstable.svg)](https://packagist.org/packages/logoscon/post-glue) [![Build Status](https://travis-ci.org/log-oscon/post-glue.svg?branch=master)](https://travis-ci.org/log-oscon/post-glue) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/log-oscon/post-glue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/log-oscon/post-glue/?branch=master)

Sticky posts for WordPress, improved.

This plugin addresses the following issues and limitations found in the core's implementation of sticky posts:

* Only the core `post` type supports this feature.
* Sticky posts are added in front of your homepage's results, yielding more posts than the configured per-page setting.
* Sticky posts reappear as you navigate further into the archive.
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
