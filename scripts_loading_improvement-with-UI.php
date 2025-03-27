<?php
/**
 * Plugin Name: Stellarwise Optimize
 * Plugin URI: https://example.com
 * Description: Plugin WordPress do optymalizacji zasobów z panelem administracyjnym.
 * Author: Your Name
 * Version: 1.0
 * Author URI: https://example.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class Stellarwise_Optimizer {
    private $settings_file;
    private $settings;

    public function __construct() {
        $this->settings_file = plugin_dir_path(__FILE__) . 'stellarwise-settings.json';
        $this->settings = $this->load_settings();

        add_action('admin_menu', [$this, 'create_admin_page']);
        add_action('admin_init', [$this, 'save_settings']);
        add_filter('style_loader_tag', [$this, 'optimize_css'], 20, 4);
        add_filter('script_loader_tag', [$this, 'optimize_js'], 10, 3);
        add_action('wp_head', [$this, 'add_preload_links'], 5);
    }

    private function load_settings() {
        if (file_exists($this->settings_file)) {
            return json_decode(file_get_contents($this->settings_file), true);
        }
        return ['css_priority' => [], 'css_turn_off' => [], 'js_priority' => [], 'js_turn_off' => [], 'preload_assets' => []];
    }

    private function save_settings_to_file($data) {
        file_put_contents($this->settings_file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function create_admin_page() {
        add_menu_page('Stellarwise Optimize', 'Stellarwise Optimize', 'manage_options', 'stellarwise-optimize', [$this, 'admin_page'], 'dashicons-performance');
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Stellarwise Optimize</h1>
            <form method="post">
                <h2>Priorytetowe CSS</h2>
                <textarea name="css_priority" rows="5" cols="50"><?php echo implode("\n", $this->settings['css_priority']); ?></textarea>
                <h2>Wyłączone CSS</h2>
                <textarea name="css_turn_off" rows="5" cols="50"><?php echo implode("\n", $this->settings['css_turn_off']); ?></textarea>
                <h2>Priorytetowe JS</h2>
                <textarea name="js_priority" rows="5" cols="50"><?php echo implode("\n", $this->settings['js_priority']); ?></textarea>
                <h2>Wyłączone JS</h2>
                <textarea name="js_turn_off" rows="5" cols="50"><?php echo implode("\n", $this->settings['js_turn_off']); ?></textarea>
                <h2>Preload Assets</h2>
                <textarea name="preload_assets" rows="5" cols="50"><?php echo json_encode($this->settings['preload_assets'], JSON_PRETTY_PRINT); ?></textarea>
                <br>
                <input type="submit" name="save_settings" value="Zapisz ustawienia" class="button button-primary">
            </form>
        </div>
        <?php
    }

    public function save_settings() {
        if (isset($_POST['save_settings'])) {
            $this->settings['css_priority'] = array_filter(array_map('trim', explode("\n", $_POST['css_priority'])));
            $this->settings['css_turn_off'] = array_filter(array_map('trim', explode("\n", $_POST['css_turn_off'])));
            $this->settings['js_priority'] = array_filter(array_map('trim', explode("\n", $_POST['js_priority'])));
            $this->settings['js_turn_off'] = array_filter(array_map('trim', explode("\n", $_POST['js_turn_off'])));
            $this->settings['preload_assets'] = json_decode($_POST['preload_assets'], true);

            $this->save_settings_to_file($this->settings);
        }
    }

    public function optimize_css($html, $handle, $href, $media) {
        if (in_array($handle, $this->settings['css_priority'])) {
            return '<link rel="stylesheet" href="' . esc_url($href) . '" fetchpriority="high">';
        } elseif (in_array($handle, $this->settings['css_turn_off'])) {
            return '<script>console.log("CSS wyłączony: ' . $handle . '")</script>';
        }
        return $html;
    }

    public function optimize_js($tag, $handle, $src) {
        if (in_array($handle, $this->settings['js_priority'])) {
            return '<script src="' . esc_url($src) . '" fetchpriority="high"></script>';
        } elseif (in_array($handle, $this->settings['js_turn_off'])) {
            return '<script>console.log("JS wyłączony: ' . $handle . '")</script>';
        }
        return $tag;
    }

    public function add_preload_links() {
        foreach ($this->settings['preload_assets'] as $url => $type) {
            echo '<link rel="preload" href="' . esc_url($url) . '" as="' . esc_attr($type) . '" crossorigin="anonymous">\n';
        }
    }
}

new Stellarwise_Optimizer();
