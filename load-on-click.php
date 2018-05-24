<?php

/*
 * Plugin Name: Load On Click
 * Plugin URI: https://github.com/moewe-io/load-on-click
 * Description: Simple shortcode to load content, when clicked: [load_on_click id="XYZ"]Content[/load_on_click]
 * Version: 1.1.1
 * Author: MOEWE
 * Author URI: https://www.moewe.io/
 * Requires at least: 4.9.6
 * Text Domain: load-on-click
 * Domain Path: /languages
 */


class MOEWE_Load_on_Click {

    function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_shortcode('load_on_click', [$this, 'render_load_on_click']);

        add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
    }

    function load_plugin_textdomain() {
        load_plugin_textdomain('load-on-click', false, basename(dirname(__FILE__)) . '/languages/');
    }

    function enqueue() {
    }

    function render_load_on_click($atts = array(), $content = '') {

        $privacy_page = get_option('wp_page_for_privacy_policy');
        $privacy_page = get_permalink($privacy_page);

        $atts = shortcode_atts(array(
            'description'  => sprintf(__('Please click the button, to show the external content. We will store a cookie to save your decision. Please note, that some of your data may be send to external services for this. Please read our <a href="%s" target="_blank">privacy policy</a> for more information.', 'load-on-click'), $privacy_page),
            'button_label' => __('Show content', 'load-on-click'),
            'reload'       => false,
            'id'           => false,
            'title'        => false
        ), $atts);

        if (!$atts['id']) {
            return __('Please provide a unique id as shortcode parameter', 'load-on-click');
        }
        $id = str_replace('-', '_', esc_attr('load_on_click_' . $atts['id']));

        if (isset($_COOKIE[$id])) {
            return do_shortcode($content);
        }
        ob_start();
        ?>
        <div id="<?php echo $id ?>-wrapper" class="load-on-click-wrapper">
            <?php if ($atts['title']) echo '<p class="title">' . $atts['title'] . '</p>' ?>
            <p><?php echo $atts['description'] ?></p>
            <button onclick="<?php echo $id ?>()"><?php echo $atts['button_label'] ?></button>
            <script>
                function <?php echo $id ?>() {
                    var date = new Date();
                    date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
                    document.cookie = " <?php echo $id ?>=true; expires=" + date.toUTCString();
                    <?php
                    if ($atts['reload']) {
                    echo 'location.reload();';
                } else {
                    ?>
                    var content = <?php echo wp_json_encode(do_shortcode($content)) ?>;
                    jQuery('#<?php echo $id ?>-wrapper').html(content);
                    <?php
                    }
                    ?>
                }

                if (document.cookie.indexOf("<?php echo $id ?>=") >= 0) {
                    <?php echo $id ?>();
                }
            </script>

        </div>
        <?php
        return ob_get_clean();
    }
}

new MOEWE_Load_on_Click();

// Updates
require 'libs/plugin-update-checker-4.4/plugin-update-checker.php';
Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/moewe-io/load-on-click/',
    __FILE__,
    'load-on-click'
)->setBranch('master');