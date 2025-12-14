<?php
/**
 * Plugin Name: FormFree
 * Plugin URI: https://github.com/luissanchez-perplexity/formfree
 * Description: Formulario de contacto multi-step con integración a Kommo CRM, almacenamiento en base de datos y exportación CSV.
 * Version: 1.0.1
 * Author: Luis Sánchez y Perplexity
 * Author URI: https://formfree.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: formfree
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FORMFREE_VERSION', '1.0.1');
define('FORMFREE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FORMFREE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FORMFREE_PLUGIN_BASENAME', plugin_basename(__FILE__));

class FormFree {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->cargar_dependencias();
        $this->definir_hooks();
        $this->inicializar_componentes();
    }

    private function cargar_dependencias() {
        require_once FORMFREE_PLUGIN_DIR . 'includes/class-database.php';
        require_once FORMFREE_PLUGIN_DIR . 'includes/class-form-handler.php';
        require_once FORMFREE_PLUGIN_DIR . 'includes/class-kommo-integration.php';
        require_once FORMFREE_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once FORMFREE_PLUGIN_DIR . 'includes/class-validator.php';
        require_once FORMFREE_PLUGIN_DIR . 'includes/class-email-notifications.php';
        require_once FORMFREE_PLUGIN_DIR . 'includes/class-csv-exporter.php';

        if (is_admin()) {
            require_once FORMFREE_PLUGIN_DIR . 'admin/class-admin-menu.php';
            require_once FORMFREE_PLUGIN_DIR . 'admin/class-form-builder.php';
            require_once FORMFREE_PLUGIN_DIR . 'admin/class-settings.php';
            require_once FORMFREE_PLUGIN_DIR . 'admin/class-submissions.php';
        }
    }

    private function definir_hooks() {
        register_activation_hook(__FILE__, array($this, 'activar_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'desactivar_plugin'));
        add_action('wp_enqueue_scripts', array($this, 'cargar_assets_publicos'));
        add_action('admin_enqueue_scripts', array($this, 'cargar_assets_admin'));
        add_filter('plugin_action_links_' . FORMFREE_PLUGIN_BASENAME, array($this, 'agregar_enlaces_plugin'));
        add_action('formfree_daily_cleanup', array($this, 'limpiar_datos_antiguos'));
    }

    private function inicializar_componentes() {
        FormFree_Database::get_instance();
        FormFree_Form_Handler::get_instance();
        FormFree_Shortcode::get_instance();
        if (is_admin()) {
            FormFree_Admin_Menu::get_instance();
            FormFree_Form_Builder::get_instance();
        }
    }

    public function activar_plugin() {
        FormFree_Database::crear_tablas();

        $opciones_default = array(
            'version' => FORMFREE_VERSION,
            'retention_days' => 365,
            'admin_email' => get_option('admin_email'),
            'enable_notifications' => true,
            'enable_user_confirmation' => true,
            'recaptcha_enabled' => false,
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'kommo_enabled' => false,
            'kommo_subdomain' => '',
            'kommo_client_id' => '',
            'kommo_client_secret' => '',
        );
        add_option('formfree_settings', $opciones_default);

        if (!wp_next_scheduled('formfree_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'formfree_daily_cleanup');
        }
        flush_rewrite_rules();
    }

    public function desactivar_plugin() {
        $timestamp = wp_next_scheduled('formfree_daily_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'formfree_daily_cleanup');
        }
        flush_rewrite_rules();
    }

    public function cargar_assets_publicos() {
        wp_enqueue_style('formfree-public', FORMFREE_PLUGIN_URL . 'public/css/formfree-public.css', array(), FORMFREE_VERSION);
        wp_enqueue_script('formfree-multistep', FORMFREE_PLUGIN_URL . 'public/js/formfree-multistep.js', array('jquery'), FORMFREE_VERSION, true);

        wp_localize_script('formfree-multistep', 'formfree_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('formfree_nonce'),
            'messages' => array(
                'error_general' => __('Ha ocurrido un error. Por favor, inténtalo de nuevo.', 'formfree'),
                'success' => __('¡Formulario enviado exitosamente!', 'formfree'),
                'required' => __('Este campo es obligatorio.', 'formfree'),
                'invalid_email' => __('Por favor, ingresa un email válido.', 'formfree'),
                'invalid_phone' => __('Por favor, ingresa un teléfono válido.', 'formfree'),
            ),
        ));

        $settings = get_option('formfree_settings', array());
        if (!empty($settings['recaptcha_enabled']) && !empty($settings['recaptcha_site_key'])) {
            wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
        }
    }

    public function cargar_assets_admin($hook) {
        if (strpos($hook, 'formfree') === false) {
            return;
        }

        wp_enqueue_style('formfree-admin', FORMFREE_PLUGIN_URL . 'assets/css/formfree-admin.css', array(), FORMFREE_VERSION);
        wp_enqueue_script('formfree-admin', FORMFREE_PLUGIN_URL . 'assets/js/formfree-admin.js', array('jquery'), FORMFREE_VERSION, true);

        if (strpos($hook, 'formfree-builder') !== false || strpos($hook, 'formfree_page_formfree-builder') !== false) {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('formfree-builder', FORMFREE_PLUGIN_URL . 'assets/js/formfree-builder.js', array('jquery', 'jquery-ui-sortable'), FORMFREE_VERSION, true);

            wp_localize_script('formfree-builder', 'formfree_builder', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('formfree_builder_nonce'),
                'strings' => array(
                    'confirm_delete' => __('¿Estás seguro de eliminar este campo?', 'formfree'),
                ),
            ));
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    public function agregar_enlaces_plugin($links) {
        $links_plugin = array(
            '<a href="' . admin_url('admin.php?page=formfree-settings') . '">' . __('Configuración', 'formfree') . '</a>',
            '<a href="' . admin_url('admin.php?page=formfree-forms') . '">' . __('Formularios', 'formfree') . '</a>',
        );
        return array_merge($links_plugin, $links);
    }

    public function limpiar_datos_antiguos() {
        $settings = get_option('formfree_settings', array());
        $retention_days = isset($settings['retention_days']) ? intval($settings['retention_days']) : 365;
        FormFree_Database::limpiar_submissions_antiguas($retention_days);
    }
}

function formfree_init() {
    return FormFree::get_instance();
}
add_action('plugins_loaded', 'formfree_init');
