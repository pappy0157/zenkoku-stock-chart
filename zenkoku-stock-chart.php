<?php
/**
 * Plugin Name: Zenkoku Stock Chart
 * Plugin URI: https://companydata.tsujigawa.com/zenkoku-stock-chart/
 * Description: 投稿・固定ページ・公開カスタム投稿タイプで証券コード（例: 7203）と市場サフィックス（例: .T）を入力し、TradingViewの株価チャートを表示するショートコードを提供します。[zk_stock_chart]
 * Version: 1.2.1
 * Author: SPL
 * Author URI: https://companydata.tsujigawa.com/
 * License: GPLv2 or later
 * Text Domain: zenkoku-stock-chart
 */

if (!defined('ABSPATH')) exit;

class Zenkoku_Stock_Chart {
    const META_CODE   = '_zkc_code';
    const META_MARKET = '_zkc_market';
    const NONCE       = 'zkc_meta_nonce';
    const TV_HANDLE   = 'tradingview-tvjs';

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_metabox_for_all']);
        add_action('save_post',      [$this, 'save_metabox']);

        add_shortcode('zk_stock_chart', [$this, 'shortcode']);

        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
        add_action('wp_head', [$this, 'inject_credit_style']); // creditの最低限のCSS
    }

    public function register_scripts() {
        wp_register_script(self::TV_HANDLE, 'https://s3.tradingview.com/tv.js', [], null, true);
    }

    public function register_metabox_for_all() {
        $types = get_post_types(['public' => true], 'names');
        foreach ($types as $ptype) {
            add_meta_box(
                'zkc_meta',
                '株価チャート設定（Zenkoku Stock Chart）',
                [$this, 'render_metabox'],
                $ptype,
                'side',
                'default'
            );
        }
    }

    public function render_metabox($post) {
        wp_nonce_field(self::NONCE, self::NONCE);
        $code   = get_post_meta($post->ID, self::META_CODE, true);
        $market = get_post_meta($post->ID, self::META_MARKET, true);
        ?>
        <p><label for="zkc_code"><strong>証券コード</strong>（例: 7203）</label><br>
            <input type="text" name="zkc_code" id="zkc_code" value="<?php echo esc_attr($code); ?>" style="width:100%;" placeholder="例: 7203">
        </p>
        <p><label for="zkc_market"><strong>市場サフィックス</strong>（例: <code>.T</code> 東証）</label><br>
            <input type="text" name="zkc_market" id="zkc_market" value="<?php echo esc_attr($market); ?>" style="width:100%;" placeholder="例: .T">
        </p>
        <p style="font-size:12px;color:#555;">
            本文に <code>[zk_stock_chart]</code> を挿入すると、この値でチャートが表示されます。<br>
            直接指定例：<code>[zk_stock_chart code="7203" market=".T" height="600" theme="dark"]</code><br>
            TVシンボル直接：<code>[zk_stock_chart symbol="TSE:7203"]</code>
        </p>
        <?php
    }

    public function save_metabox($post_id) {
        if (!isset($_POST[self::NONCE]) || !wp_verify_nonce($_POST[self::NONCE], self::NONCE)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $post_type = get_post_type($post_id);
        if (!current_user_can($this->cap_for_post_type($post_type), $post_id)) return;

        $code   = isset($_POST['zkc_code']) ? sanitize_text_field($_POST['zkc_code']) : '';
        $market = isset($_POST['zkc_market']) ? sanitize_text_field($_POST['zkc_market']) : '';

        if ($code !== '')  update_post_meta($post_id, self::META_CODE, $code); else delete_post_meta($post_id, self::META_CODE);
        if ($market !== '') update_post_meta($post_id, self::META_MARKET, $market); else delete_post_meta($post_id, self::META_MARKET);
    }

    private function cap_for_post_type($post_type) {
        $obj = get_post_type_object($post_type);
        if ($obj && isset($obj->cap->edit_post)) return $obj->cap->edit_post;
        return ($post_type === 'page') ? 'edit_page' : 'edit_post';
    }

    public function shortcode($atts = [], $content = null) {
        $defaults = [
            'code'   => '',
            'market' => '',
            'symbol' => '',
            'height' => '550',
            'theme'  => 'light',
            'range'  => '12M',
            'toolbar'=> 'true',
            'details'=> 'true',
            'locale' => 'ja',
        ];
        $a = shortcode_atts($defaults, $atts, 'zk_stock_chart');

        if ((empty($a['code']) || empty($a['market'])) && is_singular()) {
            $post_id = get_queried_object_id();
            if ($post_id) {
                $meta_code   = get_post_meta($post_id, self::META_CODE, true);
                $meta_market = get_post_meta($post_id, self::META_MARKET, true);
                if (empty($a['code']) && $meta_code)     $a['code'] = $meta_code;
                if (empty($a['market']) && $meta_market) $a['market'] = $meta_market;
            }
        }

        if (!empty($a['symbol'])) {
            $symbol = sanitize_text_field($a['symbol']);
        } else {
            $code = preg_replace('/\s+/', '', $a['code']);
            $market = preg_replace('/\s+/', '', $a['market']);
            if (empty($code) || empty($market)) {
                return '<div class="zkc-error" style="color:#a00;">[zk_stock_chart]：証券コードまたは市場サフィックスが未設定です。（編集画面のメタボックス、またはショートコード引数で指定してください）</div>';
            }
            $symbol = $code . $market; // 例: 7203.T
        }

        $height = max(300, intval($a['height']));
        $theme  = ($a['theme'] === 'dark') ? 'dark' : 'light';
        $range  = in_array($a['range'], ['1D','5D','1M','3M','6M','12M','YTD','ALL'], true) ? $a['range'] : '12M';
        $toolbar= ($a['toolbar'] === 'false') ? false : true;
        $details= ($a['details'] === 'false') ? false : true;
        $locale = 'ja';

        $container_id = 'zkc_tv_' . wp_generate_uuid4();
        wp_enqueue_script(self::TV_HANDLE);

        ob_start(); ?>
        <div class="zkc-stock-chart-wrap">
            <div class="zkc-stock-chart" id="<?php echo esc_attr($container_id); ?>" style="width:100%;height:<?php echo esc_attr($height); ?>px;"></div>
            <div class="zkc-credit">
                © <a href="<?php echo esc_url('https://companydata.tsujigawa.com/'); ?>" target="_blank" rel="noopener">全国企業データベース</a>
            </div>
        </div>
        <script>
        (function(){
            function initZKCTV(){
                if (typeof TradingView === 'undefined' || typeof TradingView.widget === 'undefined') {
                    setTimeout(initZKCTV, 150);
                    return;
                }
                try {
                    new TradingView.widget({
                        container_id: "<?php echo esc_js($container_id); ?>",
                        width: "100%",
                        height: "<?php echo esc_js($height); ?>",
                        autosize: true,
                        symbol: "<?php echo esc_js($symbol); ?>",
                        interval: "D",
                        timezone: "Asia/Tokyo",
                        theme: "<?php echo esc_js($theme); ?>",
                        style: "1",
                        locale: "<?php echo esc_js($locale); ?>",
                        withdateranges: true,
                        range: "<?php echo esc_js($range); ?>",
                        hide_top_toolbar: <?php echo $toolbar ? 'false' : 'true'; ?>,
                        details: <?php echo $details ? 'true' : 'false'; ?>,
                        allow_symbol_change: false,
                        studies: [],
                        calendar: false,
                        support_host: "https://www.tradingview.com"
                    });
                } catch (e) { console.error('ZKC TV init error:', e); }
            }
            if (typeof TradingView !== 'undefined') {
                initZKCTV();
            } else {
                var tries = 0;
                (function waitTV(){
                    if (typeof TradingView !== 'undefined') return initZKCTV();
                    if (tries++ > 100) return;
                    setTimeout(waitTV, 150);
                })();
            }
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    public function inject_credit_style() {
        ?>
        <style id="zkc-credit-style">
            .zkc-stock-chart-wrap .zkc-credit{
                margin-top:6px;
                font-size:12px;
                color:#777;
                text-align:right;
                line-height:1.2;
            }
            .zkc-stock-chart-wrap .zkc-credit a{
                color:inherit;
                text-decoration:none;
                border-bottom:1px dotted currentColor;
            }
            .zkc-stock-chart-wrap .zkc-credit a:hover{
                text-decoration:underline;
            }
        </style>
        <?php
    }
}

new Zenkoku_Stock_Chart();
