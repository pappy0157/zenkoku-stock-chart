=== Zenkoku Stock Chart ===
Contributors: tsujigawa
Tags: stock, chart, tradingview, finance, shortcode
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.2

Display real-time stock charts on posts, pages, and custom post types using TradingView.

== Description ==

**Zenkoku Stock Chart** is a plugin that allows you to embed real-time stock charts into WordPress content.  
It supports posts, pages, and all public custom post types.  

Key features:

* Add a **stock code** (e.g. 7203) and **market suffix** (e.g. .T for Tokyo Stock Exchange) via a metabox on the edit screen.
* Display the chart using the shortcode `[zk_stock_chart]`.
* Supports shortcode attributes to customize:
  * `code` and `market` (or use `symbol` directly such as `TSE:7203`)
  * `height` (default 550px)
  * `theme` (light or dark)
  * `range` (1D, 5D, 1M, 3M, 6M, 12M, YTD, ALL)
  * `toolbar` and `details` display toggle
* Works with TradingView official widget API.
* Outputs an automatic credit line:  
  **© [全国企業データベース](https://companydata.tsujigawa.com/)**

Example shortcode usage:

