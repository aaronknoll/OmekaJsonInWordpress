=== Display Omeka Feed in Wordpress===
Contributors: aaronknoll
Donate link: http://aaronknoll.com
Tags: omeka, metadata, cataloging, xml, 
Requires at least: 3.0.1
Tested up to: 3.4+
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to include information from an Omeka installation on the same
site as a subdomain on pages in Wordpress in your Wordpress theme.


== Description ==

For this plugin to work, you will need an installation of Omeka on
the same domain as your wordpress site. The plugin is built to work 
with Omeka as some arbitrary [configurable via the wordpress admin 
panel] subdirectory underneath the Wordpress url.

To configure, install the plugin and set the subdirectory in the configuration
panel.

To make pages pull data from Omeka, create a new page in Wordpress. Give that page
a title [it can be different from Omeka] and fill in the Omeka pull field
underneath the primary text area with the ID number from Omeka of the entry
you would like to pull.

== Installation ==

First, install Omeka [omeka.org] in a subdirectory underneath your Wordpress
installation.

Upload plugin folder to your plugins folder; install.

Then go to the omekafeedpull settings era in your Wordpress Admin panel.

Enter in the subdirectory of the Omeka installation underneath your
Wordpress.

[Here you can also decide which fields will/will not appear by default]

Then, create a new page in Wordpress. Put the item # of the item you wish to
pull into your Wordpress theme in the box; save page*!

*Note, please make sure item # in omeka exists before adding it to Wordpress.

== Changelog ==

0.9 First beta luanch of plugin. 