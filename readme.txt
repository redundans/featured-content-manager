=== Featured Content Manager ===
Contributors: klandestino
Tags: featured content
Requires at least: 4.7
Tested up to: 4.7.4
Stable tag: 0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Featured Content is a WordPress plugin that lets users create featured items that mirrors posts – then order them and edit their representation inside featured areas.

== Description ==

Featured Content is a WordPress plugin that lets users create featured items that mirrors posts – then order them and edit their representation inside featured areas. Find out more at https://plugins.klandestino.se

== Installation ==

1. Download the plugin and upload the zip-file via Plugins -> Add new in WordPress
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add your license under Settings->Featured Content
4. Now you are ready to use Featured Content Manager

== Frequently Asked Questions ==

= Does it support my theme? =

Yes, all themes that use standard WordPress functionality works out of the box by overriding the main loop on the blog posts page.

= Does it work with multisite? =

Yes! If you don't want to network activate the plugin it has to be active on the main site for updates to work.

== Changelog ==

= 0.7.1 =

* Remove plugin updater functionality

= 0.7 =

* Support for elasticpress multisite search
* Support for blurbs
* Code cleanup
* Bump composer/installers to v1.2.0

= 0.6 =

* Composer support
* Better documentation
* Implementing reversed post population

= 0.5.3 =

* Bugfix: Escapes post title correctly in customizer

= 0.5.2 =
* A faster function for deleting old drafts when updating featured items in the customizer

= 0.5.1 =

* Bugfix: problem with form input fields in sortables for Forefox

= 0.5 =

* Introduce fcm_get_children() for getting children of a specified post
* Only check if terms exists when opening customizer, makes it faster

= 0.4.2 =

* Bugfix: last featured content is now being deleted correctly

= 0.4.1 =

* Make sure menu item is always visible
* Strip tags and shortcode from content before inserting into customizer

= 0.4 =

* UX improvements
* WP 4.1 support
* Use excerpt if it exists

= 0.3 =
* Adds autoupdater and license support

= 0.2 =
* Alters main query if there is no theme support

= 0.1 =
* Initial release