=== Zenkoku Stock Chart ===
Contributors: spl, zenkoku
Tags: stock, chart, tradingview, finance, shortcode
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.2

Display real-time stock charts on posts, pages, and custom post types using TradingView. Add a stock code and market suffix, and embed a chart with a simple shortcode.

== Description ==

**Zenkoku Stock Chart** lets you embed real-time stock charts into WordPress content.  
Supports posts, pages, and all public custom post types.

Key features:

* Metabox to save **stock code** (e.g. 7203) and **market suffix** (e.g. .T for TSE).
* Shortcode `[zk_stock_chart]` with customizable attributes:
  * `code`, `market` or `symbol` (e.g. `TSE:7203`)
  * `height` (default 550)
  * `theme` (`light`|`dark`)
  * `range` (`1D`,`5D`,`1M`,`3M`,`6M`,`12M`,`YTD`,`ALL`)
  * `toolbar` (`true|false`), `details` (`true|false`)
* Uses official TradingView widget.
* Automatically outputs credit: **© [全国企業データベース](https://companydata.tsujigawa.com/)**

Example:

[zk_stock_chart code="7203" market=".T" height="600" theme="dark"]

pgsql
コードをコピーする

== Installation ==

1. Plugins → Add New → search “Zenkoku Stock Chart” → Install → Activate
OR
1. Upload the ZIP via Plugins → Add New → Upload Plugin → Install Now → Activate

== Frequently Asked Questions ==

= The chart doesn’t show. =
Check that `code` and `market` (or `symbol`) are valid on TradingView (e.g., `7203.T`).

= Can I force dark theme? =
Use `[zk_stock_chart theme="dark"]`.

== Screenshots ==

1. Chart embedded in a post with credit.
2. Metabox for code/market on the edit screen.

== Changelog ==

= 1.2.1 =
* Align plugin name with readme.
* Update “Tested up to” to 6.8.
* Separate Plugin URI and Author URI.

= 1.2.0 =
* Add credit line © 全国企業データベース with link.
* Improve CPT compatibility.

= 1.1.0 =
* Extend support to all public custom post types.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.2.1 =
Compatibility header updates and name alignment for WordPress.org listing.

== Arbitrary section 1 ==
This plugin is developed for the **全国企業データベース** project.  
Website: https://companydata.tsujigawa.com/
