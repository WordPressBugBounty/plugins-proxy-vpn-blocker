=== Proxy & VPN Blocker ===
Contributors: rickstermuk
Tags: security, proxy blocker, vpn blocker, proxycheck, ip address
Requires at least: 4.9
Tested up to: 6.8.1
Requires PHP: 7.2
Stable tag: 3.4.1
License: GPLv2

Stop unwanted traffic—block proxies, VPNs, TOR and spam using the proxycheck.io API. Easy to configure with geoblocking, IP logging & admin controls.

== Description ==
= Block Proxies, VPNs, TOR, and More – Protect Your WordPress Site =
[Proxy & VPN Blocker](https://proxyvpnblocker.com) helps secure your WordPress site by detecting and blocking traffic from anonymous sources using the powerful [proxycheck.io](https://proxycheck.io) API. Prevent unwanted access to login, registration, wp-admin, specific pages or posts — or even your entire site — from:

* Proxies
* VPNs (optional)
* Tor nodes
* Mysterium nodes
* Compromised servers
* Specific IPs, ranges, ASNs, or countries

It also blocks spam comments from anonymous networks commonly used by spammers.

= Key Features =
✅ Detects and blocks Proxies, VPNs (optional), TOR, Mysterium Nodes, Web Proxies, and Compromised Servers  
🌐 Geo-blocking support – easily block or allow traffic from specific countries  
🎯 Supports IP ranges, CIDRs, specific IPs, and ASN blocking  
🔍 Uses proxycheck.io’s Risk Score for more intelligent blocking decisions  
📊 Built-in API Key Statistics page with live query usage graphs and daily totals  
📋 Visitor Action Log – view blocked IPs, detection reason, and plugin response  
🧠 Caches known good IPs to reduce API queries and improve performance  
🛡 Works with both IPv4 and IPv6 addresses  
☁️ Compatible with Cloudflare and other CDN headers  
🛑 Blocks access to Login, Registration, Admin, Comments, and specified pages/posts  
📄 Customize the “Access Denied” message or redirect to a specific page  
👥 Logs registration and most recent login IPs in the Users list and profile  
📊 View API usage statistics directly in your WordPress Dashboard  
📝 Manage your proxycheck.io Whitelist and Blacklist without leaving WordPress  
🔧 Simple integration via the WordPress Editor and WordPress Toolbar for page-level protection  

And much more available in [Proxy & VPN Blocker Premium](https://proxyvpnblocker.com/premium)!

= The proxycheck.io API =
This Plugin can be used without a proxycheck.io API key, but it will be limited to 100 daily queries to the API. To enhance the capabilities, you can obtain a free API key from proxycheck.io, which allows for 1,000 free daily queries, making it suitable for small WordPress sites.

Here's an overview of the free and paid API options:

* Without an API key (100 queries/day)
* With a free API key (1,000 queries/day – ideal for small sites)
* With a paid API key (10,000 to over 10 million queries/day)

Your API key can be reused across multiple sites and apps.

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
=What is proxycheck.io?=

Proxycheck.io is a simple, accurate and reliable API for the detection and blocking of people using Proxies, Tor & VPN servers.

=Blocking Proxies and VPN's on all pages?=

Although this Plugin has an option to block Proxies & VPN's on all pages, this option is not generally recommended due to significantly higher query usage, but was added on user request.

It is important to note that if you are using a WordPress caching Plugin (eg WP Super Cache, WP Rocket, W3 Total Cache and many others), these may prevent the Proxy or VPN from being blocked if you are using 'Block on all pages' as the caching Plugin will likely serve the visitor a static cached version of your website pages. As the cached pages are served by the caching Plugin in static HTML, the code for proxy detection will not run on these cached pages. This won't affect the normal protections this Plugin provides for Log-in, Registration and commenting.

=I accidently locked myself out by blocking my own country/continent, what do I do?=
The fix is simple, upload a .txt file called disablepvb.txt to your wordpress root directory, PVB looks for this file when the proxy and VPN checks are made, if the file exists it will prevent the Plugin from contacting the proxycheck.io API. You will now be able to log in and remove your country/continent in the PVB Settings.

Remember: If you ever have to do this, delete the disablepvb.txt file after you are done! If you don't remove it, the Plugin wont be protecting your site.

== Screenshots ==
1. Settings UI.
2. Default Error message shown when a proxy or vpn is detected, this can be changed in the Settings.
3. Error message example if you opt to use a page within your site's theme.
4. API Key Stats page.
5. Whitelist editor page. The blacklist editor page looks similar to this.
6. Action Log - A list of recently detected IP Addresses.

== Changelog ==
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
