=== Outbound Click Tracker Plugin ===
Tags: outbound, links
Stable tag: trunk
Donate link: https://online.nwf.org/site/Donation2?df_id=6620&6620.donation=form1
Requires at least: 2.0
Tested up to: 2.9
Contributors: Keith Graham
Stable tag: 1.4

Tracks clicks on outbound links without Google Analytics. Find out where your readers are going.

== Description ==
The Outbound Click Tracker Plugin uses JavaScript to detect and record clicks on an off-site links. JavaScript security model limits which links it can track. It will not track links that advertisers or affiliates have placed in an iframe tag. It will not track Google Adsense clicks. It will not track JavaScript redirects from an onClick event.
Top links in the last 5 days are displayed on the settings page as well as details for each Click.
As an option the plugin can make outbound links open in a new window.

== Installation ==
1. Download the plugin.
2. Upload the plugin to your wp-content/plugins directory.
3. Activate the plugin.
4. Check the settings page for the Outbound Click Tracker. 

== Changelog ==

= 1.0 =
* initial release 

= 1.1 =
* Bug fix of a typo on the create table command.

= 1.2 =
* changes to Javascript so it will pass w3.org validation. 
* by request changed top outbound link count to 10.

= 1.3 =
* Added tracking of IP and link text. 
* Added Open outbound link in new window option.
* changed the logging of errors.
* updates table structure to add new fields for IP and link text.

= 1.4 =
* Added RSS feed of outbound links at request of John.


== Support ==
This plugin is in active development. All feedback is welcome on "<a href="http://www.blogseye.com/" title="Wordpress plugin: Outbound Click Plugin">program development pages</a>".
