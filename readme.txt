=== FrontPage Buddy ===
Contributors: ckchaudhary
Donate link: https://www.recycleb.in/u/chandan/
Tags: buddypress, buddyboss, bbpress, customize profiles, customize groups
Requires PHP: 7.4
Requires at least: 5.8
Tested up to: 6.7.1
Stable tag: 1.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personalised front pages for buddypress & buddyboss members & groups, bbpress profiles and 'Ultimate Member' profiles.

== Description ==

Allow your website's members to provide detailed information about themselves, embed videos, their social media profiles, etc.

## 🔌 Integrations 
It builds upon the default functionality of few other plugins. Currently it has integrations for the following plugins:

**1. BuddyPress & BuddyBoss Platform**

- **Member profiles:** BuddyPress/BuddyBoss member profiles have always been very impersonal. This plugin allows your website's members to take control of their profile pages and add information about themselves. Your members can customize their profile pages by adding descriptions, embedding videos, embedding their social media profiles, etc. Check screenshots to see a preview.

- **Groups** Allow group admins to customize the group's front page by adding details about the group, embedding videos, promoting related social media profiles, etc. Check screenshots to see a preview.

👉 [Visit this link](https://www.recycleb.in/demo/wp/frontpage-buddy-buddypress/) for a live demonstration.

**2. bbPress**

This plugin allows your bbPress forum's  members to take control of their profile pages and add information about themselves. Your members can customize their profile pages by adding descriptions, embedding videos, embedding their social media profiles, etc.

**3. Ultimate Member**

This plugin allows your 'Ultimate Member' website's users to take control of their profile pages and add information about themselves. Your members can customize their profile pages by adding descriptions, embedding videos, embedding their social media profiles, etc.  

📣 `Integration with other, compatible plugins may be added in future.`


➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖


## ✏️ Widgets 

This plugin provides a list of 'widgets' that can be added to the custom front pages. These are completely **unrelated to standard WordPress widgets** and are called so for the lack of a better word. These represent the type of content that can be added to custom front pages.

Currently the 'widgets' this plugins provides are: 

- **Rich Content:** To add and format text. 
- **Youtube video embed:** To embed a youtube video player.
- **Social media profiles:** To embed a facebook page, an instagram profile or a twitter/X profile feed.

All the widgets are disabled by default. As an administrator you have complete control on which widgets you allow.

[Read More](https://www.recycleb.in/frontpage-buddy/ "Plugin documentation") about the integrations and widgets [here](https://www.recycleb.in/frontpage-buddy/ "Plugin documentation").


➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖


## ⏩ Use of 3rd Party Services 

Some of the widgets use external APIs and/or services which may track your website visitor's data and may add cookies on their browser. Please update your privacy and cookie policies accordingly. It befalls on you ( the website administrator ) to collect opt-in consent beforehand.
Please review and do not enable any such widget if deemed necessary. Below are the details of those:

**YouTube iFrame Embed**

- **Purpose:** Used to embed YouTube videos on user profiles or other sections of the site, enhancing the interactivity of the pages.
- **Data Usage:** Embedding videos through YouTube iFrame does not involve sending or storing personal user data. However, when a video is played, YouTube may collect data as per their policies.
- **Privacy Note:** Please note that YouTube may collect information such as IP addresses and viewing activity when videos are played. For more details, refer to [YouTube's Privacy Policy](https://www.youtube.com/t/privacy).
- **Documentation:** For technical details on YouTube embeds, visit the [YouTube iFrame Player API Documentation](https://developers.google.com/youtube/iframe_api_reference).


**Facebook SDK for JavaScript**

- **Purpose:** Enables embedding of Facebook profile feeds or public page feeds.
- **Data Usage:** This integration uses the Facebook JavaScript SDK to fetch and display publicly available Facebook content. No personal user data is stored or transmitted by the plugin.
- **Privacy Note:** While the plugin does not store user data, Facebook may collect information such as IP addresses and user interaction data when the feed is displayed. For details, refer to [Facebook's Privacy Policy](https://www.facebook.com/policy.php).
- **Documentation:** For more information on the Facebook JavaScript SDK, visit the [official documentation](https://developers.facebook.com/docs/javascript).

**Instagram Embed.js**

- **Purpose:** Allows embedding of Instagram posts, photos, and videos within user profiles or other sections of the site.
- **Data Usage:** The `https://www.instagram.com/embed.js` script fetches publicly available Instagram content for display. The plugin does not collect, transmit, or store any user data.
- **Privacy Note:** When embedding Instagram content, Instagram may collect user data such as IP addresses, interaction data, and browser information. For more details, refer to [Instagram's Privacy Policy](https://privacycenter.instagram.com/policy).
- **Documentation:** For technical details, refer to the [Instagram Embedding Documentation](https://developers.facebook.com/docs/instagram/oembed/).

**Twitter Widgets.js**

- **Purpose:** Used to embed Twitter profile feeds, tweets, or timelines in user profiles or other sections of the site.
- **Data Usage:** The `https://platform.twitter.com/widgets.js` script fetches publicly available Twitter content for display. No personal user data is collected or stored by the plugin.
- **Privacy Note:** When embedding Twitter content, Twitter may collect data such as IP addresses, browser details, and interaction metrics. For more information, refer to [Twitter's Privacy Policy](https://twitter.com/en/privacy).
- **Documentation:** For more details about embedding Twitter content, visit the [Twitter Developer Documentation](https://developer.twitter.com/en/docs).

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
