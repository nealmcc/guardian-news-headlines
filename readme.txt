=== Guardian News Headlines ===
Contributors: NealMcConachie, openplatform
Tags: Guardian, News, Headlines, News Headlines, widget
Requires at least: 3.3
Tested up to: 3.4.2
Stable Tag: 0.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Shows the latest headlines from a selectable news category in a sidebar widget. Headlines link to the live article on the Guardian.

== Description ==

The Guardian News Headlines plugin allows you to add live headlines and thumbnails from the Guardian to a sidebar on your site. The text is indexable by search engines, and relevant to a category of your choice. Have a blog about education? Pick that news category to add relevant content to your blog.

Display options:

*	Selectable news category per widget
*	Number of headlines per widget
*	Choose between most recent and most viewed articles

== Installation ==

1.	Install the plugin from the Plugins page on your WordPress dashboard.
1.	Activate the Guardian News Headlines plugin.
1.	Add one or more Guardian News Headline widgets to the sidebar of your choice.

== Frequently asked questions ==

= Can I have more than one headline widget? =

Yes, you can have as many widgets as you like. Each widget is specific to a news category.

= Are the news headlines visible to search engines? =

Yes. By picking a news category relevant to your blog's topic, you can add some relevant and up-to-date content to your site.

= How often do the headlines update? =

This plugin fetches the headlines from the Guardian's Open Platform Content API. It then caches the headlines for five minutes. When that five minutes is up, the next time someone visits your site the plugin will again retrieve fresh data.

So, when a new article is published on the Guardian website in your category, all your visitors will see that headline within five minutes. (Assuming you've chosen the 'latest' feed.)

= Does the widget 'fit' with the visual look of my site? =

The widget inherits your theme's style. It uses css for positioning and thumbnail size only - all other style elements, including font, text size and colour etc. are set by your theme.  The plugin also gives four options for the Guardian's logo to fit with any background.

= What about article data? Can I get whole article contents on my blog? =

If you would like to pull in full article data to your site, check out [the Guardian News Feed](http://wordpress.org/extend/plugins/the-guardian-news-feed/).

== Screenshots ==

1.	The widget settings panel
2.	Available news categories
3.	The resulting widget
4.	Changing to most-viewed
5.	Different feed results for most-viewed articles
6.	The widget on a dark background

== Changelog ==

= 0.5.2 =
*	Correctly validate widget options for the 'All' category
*	Make the 'All' category default
*	Rename "UK" and "World" to "UK News" and "World News"

= 0.5.1 =
*	Bug Fix release.

= 0.5 =
*	Add cache for headline data.

= 0.4 =
*	Alpha release, with a fully functional plugin, but no caching yet.

== Upgrade Notice ==

= 0.5.2 =
Fixes a widget validation bug with the "All" category.

= 0.5.1 =
Fix a bug with the caching.  The cache timestamp will now update appropriately even if the fresh data is the same as the old.

= 0.5 =
Caching enabled.  Speeds up page load times, and reduces load on the Guardian content API.

= 0.4 =
This version gets it all started. Check it out!
