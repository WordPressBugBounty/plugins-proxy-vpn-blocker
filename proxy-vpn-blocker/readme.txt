=== Proxy & VPN Blocker ===
Contributors: rickstermuk
Tags: security, vpn blocker, proxy blocker, tor blocker, spam protection
Requires at least: 4.9
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 3.5.7
License: GPLv2

Block VPNs, proxies, Tor, and spam on WordPress. Strengthen security and stop fake users with smart IP blocking via proxycheck.io.

== Description ==
= Block VPNs, Proxies, Tor & Spam - Strengthen Your WordPress Security =

**Proxy & VPN Blocker** is a complete **WordPress security plugin** designed to protect your site from anonymous and abusive traffic.  
It functions as a powerful **VPN blocker**, **proxy blocker**, and **Tor blocker**, preventing unwanted visitors, spam bots, and fake users from accessing your site.  

Using the trusted [proxycheck.io](https://proxycheck.io) API, it detects connections from VPNs, open proxies, Tor nodes, and compromised servers — giving you real-time protection without slowing down your site.  

Perfect for login, registration, comments, or any page you want to secure, Proxy & VPN Blocker also includes smart **spam protection**, geoblocking, and IP logging to help you stay in control of who can access your WordPress site.

Whether you’re running a blog, store, or membership site, this plugin helps keep out fake users, block risky regions, and stop automated spam attempts before they start.

= Key Features =
* Powerful WordPress security plugin - blocks VPNs, proxies, Tor, Mysterium nodes, and compromised servers in real time  
* Country blocking & geoblocking - allow or deny traffic by country or region with flexible IP-based controls  
* Supports IP ranges, CIDRs, specific IPs, and ASNs for precise network-level blocking  
* Optionally use proxycheck.io’s Risk Score for smarter VPN and proxy detection decisions  
* Built-in API Key Statistics with live usage graphs and daily query totals  
* Visitor Action Log - view blocked IPs, detection reason, and plugin response directly in your dashboard  
* Caches known good IPs to reduce API usage and improve performance  
* Works seamlessly with both IPv4 and IPv6 addresses  
* Compatible with Cloudflare and other CDN headers for accurate IP detection  
* Block access to Login, Registration, Admin, Comments, or any page/post easily  
* Customize the “Access Denied” message or redirect visitors to a specific page  
* Log registration and recent login IPs in the Users list and profile - linked to proxycheck.io’s Threats page  
* Manage proxycheck.io Whitelist and Blacklist directly from WordPress  
* Simple integration via WordPress Editor and Toolbar for page-level protection  
* Lightweight, fast, and built to complement other security plugins  

And much more available in [Proxy & VPN Blocker Premium](https://proxyvpnblocker.com/premium)!

= The proxycheck.io API =
This Plugin can be used without a proxycheck.io API key, but it will be limited to 100 daily queries to the API. To enhance the capabilities, you can obtain a free API key from proxycheck.io, which allows for 1,000 free daily queries, making it suitable for small WordPress sites.

Here's an overview of the free and paid API options:

* Without an API key (100 queries/day)
* With a free API key (1,000 queries/day - ideal for small sites)
* With a paid API key (10,000 to over 10 million queries/day)

Your API key can be used across all of your sites and apps, you only need a proxycheck.io plan that fits your overall needs.

= User IP Logging Feature =
Proxy & VPN Blocker allows for local logging of user registration IP addresses. The IP addresses are displayed next to each user in the Users list and on their profile pages, visible to administrators. The Plugin also logs the most recent login IP address for each user, which is also displayed in the User's list and profile page, with the IP address linked to the proxycheck.io Threats page.

= Caching Plugin Notice =
If you're using caching plugins (like WP Rocket or WP Super Cache), IP-based page blocking might not function correctly due to static caching. A DONOTCACHEPAGE option is available to help mitigate this issue.

= Privacy & GDPR Compliance =
To check IP addresses, the plugin sends them to the proxycheck.io API. No personally identifiable information (PII) beyond the IP is transmitted. For details, refer to proxycheck.io’s [privacy notice](https://proxycheck.io/privacy) and [GDPR Compliance](https://proxycheck.io/gdpr) for further information.

= Disclaimer =
This Plugin is *not developed by proxycheck.io* despite being recommended by them.

* For plugin-related support, please use the WordPress.org support forum.
* For API or account questions, contact proxycheck.io directly.
* The proxycheck.io logo is used with express permission.

== Installation ==
Installing "Proxy & VPN Blocker" can be done either by searching for "Proxy & VPN Blocker" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the Plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the Plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What is proxycheck.io and how does it work? =
[proxycheck.io](https://proxycheck.io) is a reliable API that detects visitors using VPNs, proxies, Tor, or other anonymizing networks.  
**Proxy & VPN Blocker** connects to this API to check each visitor’s IP in real time, helping you block VPN users, proxies, or spam bots before they can access your WordPress site.

= How do I block all VPNs and proxies across my WordPress site? =
In the settings, enable “Block on all pages.” This option tells Proxy & VPN Blocker to check every page view for proxy or VPN traffic.  
⚠️ **Note:** Using this feature may increase API usage significantly and may not work well with caching plugins (see below).

= Why isn’t Proxy & VPN Blocker blocking VPNs when caching plugins are active? =
If you use caching plugins like WP Rocket, WP Super Cache, or W3 Total Cache, they may serve static pages that bypass the plugin’s IP detection.  
To fix this, enable the **Add DONOTCACHEPAGE Headers** option under PVB Settings > Page Caching. This ensures proxy and VPN checks always run correctly.

= How can I unblock myself if I accidentally blocked my country or region? =
No worries! Upload a text file named **disablepvb.txt** to your WordPress root directory.  
When this file exists, Proxy & VPN Blocker will skip proxycheck.io requests, allowing you to log in and adjust your blocking rules.  
After fixing your settings, delete the file to re-enable protection.

= Can I use the plugin without a proxycheck.io API key? =
Yes. The plugin can function without an API key, but you’ll be limited to **100 daily queries**.  
For more reliable protection, register for a **free proxycheck.io API key**, which gives you 1,000 daily queries. Paid plans provide 10,000+ per day for larger sites.

= Does Proxy & VPN Blocker log user IPs? =
Yes. The plugin optionally logs registration and recent login IP addresses, displayed in the WordPress Users list and profile pages.  
Each IP is linked to proxycheck.io’s Threats page for detailed insights.  
This feature helps you identify suspicious or abusive users at a glance.

= Is Proxy & VPN Blocker GDPR compliant? =
Yes. Only visitor IPs are transmitted to proxycheck.io for lookup, no personally identifiable data.  
See [proxycheck.io Privacy Policy](https://proxycheck.io/privacy) and [GDPR Compliance](https://proxycheck.io/gdpr) for details.

= Is this plugin made by proxycheck.io? =
No, this plugin is developed independently but is officially recommended by proxycheck.io.  
For plugin support, use the [WordPress.org support forum](https://wordpress.org/support/plugin/proxy-vpn-blocker).  
For API or account support, contact proxycheck.io directly.

== Screenshots ==
1. Settings UI.
2. Default Error message shown when a proxy or vpn is detected, this can be changed in the Settings.
3. Error message example if you opt to use a page within your site's theme.
4. API Key Stats page.
5. Whitelist editor page. The blacklist editor page looks similar to this.
6. Action Log - A list of recently detected IP Addresses.

== Changelog ==
= 3.5.7 2026-01-23 =
* Further Fixes for user table IP display

= 3.5.6 2026-01-19 =
* Fixes for user table IP display

= 3.5.5 2026-01-18 =
* Users list now searchable/filterable by IP Address so you can find users who may be using the same IP.
* Switched to newer 10th of November proxycheck.io API v3 Version.
* Removed redundant Days selector from the Advanced tab of Settings.
* Minor improvement made to cache buster option.
* Removed debug code.

= 3.5.4 2025-12-21 =
* Security Improvement for post/page bulk actions.

= 3.5.3 2025-12-05 =
* Fix for visitor action log database.

= 3.5.2 2025-11-09 =
* corrected a version number.

= 3.5.1 2025-11-09 =
* Improvements made regarding proxycheck.io v3 API.
* Added new "Detection Types" tab to PVB Settings, here you may change the types that are detected if required.

= 3.5.0 2025-11-08 =
* Upgraded to proxycheck.io v3 API.

= 3.4.5 2025-09-24 =
* Correction for potential PHP warning message with DONOTCACHEPAGE.

= 3.4.4 2025-09-17 =
* API Statistics Graph fixed in Settings, the proxycheck.io API format changed for the API Statistics graph recently which broke previous implementations.
* Improvements made to API Key field in settings for API Key Validation.

= 3.4.3 2025-08-05 =
* Further Improvement to plugin install and uninstall.
* proxycheck.io API Key is no longer displayed once saved - this is also encrypted.
* Protected Virtual Paths has been backported from the Premium version of the plugin for even more blocking options.

= 3.4.2 2025-07-29 =
* Correct issue where redirect may forcibly happen to PVB Settings page.

= 3.4.1 2025-07-20 =
* Correct issue with ajax on admin.

= 3.4.0 2025-07-20 =
* Proxy & VPN Blocker (free version) now known as Proxy & VPN Blocker Lite to better differentiate from Premium version, though little will change beyond the logo.
* Introducing Setup Wizard for new plugin installations.
* Minor changes made to settings UI.

= 3.3.1 2025-06-11 =
* Minor changes made to settings UI.

= 3.3.0 2025-05-15 =
* API Key Statistics page in Settings has been refreshed with amcharts 5 and statistics that automatically update every minute.
* Action log directory renamed in plugin files due to an edge case where some site backup systems ignore directories named /logs.
* Corrections for edge cases where Cloudflare HTTP_CF_CONNECTING_IP Header may have more than one IP address causing problems for checks.
* Backported editor sidebar widget to the WordPress Classic Editor.
* CSS Fixes for Settings Panel and related pages.

= 3.2.4 2025-04-23 =
* Fix for Whitelist page redirecting to incorrect page when adding a IP Address.

= 3.2.3 2025-04-15 =
* Fix to Whitelist/Blacklist pages to correct a potential issue if the editors are used on sites with special characters in their name - a request to update lists on proxycheck.io may not have been processed.
* Added option to Whitelist IP's quickly from the Action Log in Settings.

= 3.2.2 2025-03-26 =
* Fix for potential for checkbox to not display in the sidebar of the WordPress Block Editor for Custom Post Types (CPT). Thank you to @gdvd.
* Updated some plugin settings text.

= 3.2.1 2025-02-08 =
* Minor correction to CORS option javascript.

= 3.2.0 2025-01-24 =
* Introduced proxycheck.io CORs API as a backup option to the free version of Proxy & VPN Blocker - this may help some who have issues with the Plugin not always blocking IP's due to page caching.

= 3.1.3 2025-01-08 =
* Page Caching Headers option now enabled by default.
* Option for DONOTCACHEPAGE Headers extended to block on entire site option.

= 3.1.2 2024-12-12 =
* Correction for potential unavailable function if local user IP logging is off.

= 3.1.1 2024-12-11 =
* Correction for redirect option.

= 3.1.0 2024-11-13 =
* Implemented new and much more detailed Action Log on a new Action Log page in WordPress Dashboard, replacing the less detailed log on the API Key statistics page. 
* Alterations to some verbage in the Settings UI.
* Improvements to database update process.

= 3.0.5 2024-08-06 =
* Fix for potential for an intermittent AJAX Error popup.

= 3.0.4 2024-07-30 =
* Fix for PVB column not being able to be hidden on the Post/Pages list in the WordPress Dashboard.
* Minor code improvements.

= 3.0.3 2024-07-17 =
* Minor fix for non-working statistics page graph on newer PHP versions.

= 3.0.2 2024-07-16 =
* Minor code improvements.

= 3.0.1 2024-06-11 =
* Added check for WP Rest requests in order to help with other plugins which make use of the Rest API to contact their external API's.

= 3.0.0 2024-05-18 =
* Refreshed Settings UI.
* You can now see the current Proxy & VPN Blocker status of the page or Post that you are currently viewing on the front end by way of a new menu in the WordPress Toolbar.
* Page Post blocking has been overhauled.
*
* See changelog.txt for older versions.
