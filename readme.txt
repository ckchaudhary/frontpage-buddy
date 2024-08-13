=== FrontPage Buddy ===
Contributors: ckchaudhary
Donate link: https://www.recycleb.in/u/chandan/
Tags: buddypress, buddyboss, bbpress, customize profiles, customize groups
Requires PHP: 5.6
Requires at least: 5.8
Tested up to: 6.6.1
Stable tag: 1.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personalised front pages for buddypress members & groups, bbpress profiles and 'Ultimate Member' profiles.

== Description ==

Allow your website's members to provide detailed information about themselves, embed videos, images, their social media profiles, etc.

## Integrations 
It builds upon the default functionality of few other plugins. Currently it has integrations for the following plugins:

### 1. BuddyPress & BuddyBoss Platform ###

- **Member profiles:** BuddyPress/BuddyBoss member profiles have always been very impersonal. This plugin allows your website's members to take control of their profile pages and add information about themselves. Your members can customize their profile pages by adding descriptions, embedding images and videos, embedding their social media profiles, etc. Check screenshots to see a preview.

- **Groups** Allow group admins to customize the group's front page by adding details about the group, embedding images & videos, promoting related social media profiles, etc. Check screenshots to see a preview.

[Check this](https://www.recycleb.in/demo/wp/frontpage-buddy-buddypress/) for a live demonstration.

### 2. bbPress ###

This plugin allows your bbPress forum's  members to take control of their profile pages and add information about themselves. Your members can customize their profile pages by adding descriptions, embedding images and videos, embedding their social media profiles, etc.

### 3. Ultimate Member

This plugin allows your 'Ultimate Member' website's users to take control of their profile pages and add information about themselves. Your members can customize their profile pages by adding descriptions, embedding images and videos, embedding their social media profiles, etc.  

> Integration with other, compatible plugins may be added in future.


### Widgets 

This plugin provides a list of 'widgets' that can be added to the custom front pages. These are completely **unrelated to standard WordPress widgets** and are called so for the lack of a better word. These represent the type of content that can be added to custom front pages.

Currently the 'widgets' this plugins provides are: 

- **Rich Content:** To add text, insert links and embed(using a url) images. 
- **Youtube video embed:** To embed a youtube video player.
- **Social media profiles:** To embed a facebook page, an instagram profile or a twitter/X profile feed.

All the widgets are diabled by default. As an administrator you have complete control on which widgets you allow.

[Read More](https://www.recycleb.in/frontpage-buddy/ "Plugin documentation") about the integrations and widgets [here](https://www.recycleb.in/frontpage-buddy/ "Plugin documentation").

**.....................................................................................**

### Usage of 3rd party or external services 

Some of the widgets use external APIs and/or services which may track your website visitor's data and may add cookies on their devices. Please update your privacy and cookie policies accordingly. It befalls on you ( the website administrator ) to collect opt-in consent beforehand.
Please review and do not enable any such widget if deemed necessary. Below are the details of those:

**Facebook API:** The 'Facebook Page' widget makes use of an external API to fetch and show a Facebook profile preview. It loads a javascript file from  [https://connect.facebook.net/en_US/sdk.js](https://connect.facebook.net/en_US/sdk.js). Please check the policy at [http://developers.facebook.com/policy/](http://developers.facebook.com/policy/) and ascertain what kind of information is sent to third party servers, if any. If you have concerns, you should keep this widget disabled.

**Instagram API:** The 'Instagram Profile' widget makes use of an external API to fetch and show an Instagram profile preview. It loads a javascript file from [https://www.instagram.com/embed.js](https://www.instagram.com/embed.js). Please check the policy at [http://developers.facebook.com/policy/](http://developers.facebook.com/policy/) and ascertain what kind of information is sent to third party servers, if any. If you have concerns, you should keep this widget disabled.

**Twitter Widget API:** The 'Twitter Profile Feed' widget makes use of an external API to fetch and show a Twitter/X profile feed. It loads a javascript file from  [https://platform.twitter.com/widgets.js](https://platform.twitter.com/widgets.js). Please check the policy at [https://developer.x.com/en/more/developer-terms/agreement-and-policy](https://developer.x.com/en/more/developer-terms/agreement-and-policy) and ascertain what kind of information is sent to third party servers, if any. If you have concerns, you should keep this widget disabled.

**Youtube API:** The 'youtube video' widget makes use of an iframe to embed a youtube video. The iframe source is set to https://www.youtube.com/embed/**Youtube-video-id** . Please check youtube's policy [https://www.youtube.com/static?gl=CA&template=terms](https://www.youtube.com/static?gl=CA&template=terms) and ascertain what kind of information is sent to third party servers, if any. If you have concerns, you should keep this widget disabled.

== Frequently Asked Questions ==

= I activated the plugin but nothing happened. Why? =

First, please ensure that you go to plugin settings and check everything. In some cases, the front page isn't enabled for all members( or groups ) by default. For example, If you have buddyboss, the front pages for member profiles and groups isn't enabled for all your members & groups. Your members and group admins need to enable front pages for their profiles and groups individually.

= Will this work on WordPress multisite? =

Yes.

== Screenshots ==

1. A sample front page for a user profile.
2. A samlple edit-front-page screen.
3. The admin settings screen.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->FrontPage Buddy screen to configure the plugin

== Changelog ==

= 1.0.0 =

* Initial Release

== Upgrade Notice ==

Nothing yet.
