<?php
if (!defined('ABSPATH')) exit;

class FormFree_Admin_Menu {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('FormFree', 'formfree'),
            __('FormFree', 'formfree'),
            'manage_options',
            'formfree-forms',
            array($this, 'render_forms_page'),
            'dashicons-feedback',
            30
        );

        add_submenu_page(
            'formfree-forms',
            __('Formularios', 'formfree'),
            __('Todos los Formularios', 'formfree'),
            'manage_options',
            'formfree-forms',
            array($this, 'render_forms_page')
        );

        add_submenu_page(
            'formfree-forms',
            __('Crear Formulario', 'formfree'),
            __('Crear Nuevo', 'formfree'),
            'manage_options',
            'formfree-builder',
            array($this, 'render_builder_page')
        );

        add_submenu_page(
            'formfree-forms',
            __('Envíos', 'formfree'),
            __('Envíos', 'formfree'),
            'manage_options',
            'formfree-submissions',
            array($this, 'render_submissions_page')
        );

        add_submenu_page(
            'formfree-forms',
            __('Configuración', 'formfree'),
            __('Configuración', 'formfree'),
            'manage_options',
            'formfree-settings',
            array($this, 'render_settings_page')
        );
         // Submen��: Herramientas (nuevo)
        add_submenu_page(
            'formfree-forms',
            __('Herramientas', 'formfree'),
            __('Herramientas', 'formfree'),
            'manage_options',
            'formfree-tools',
            array($this, 'render_tools_page')
        );
    }

    public function render_forms_page() {
        $forms_manager = FormFree_Form_Builder::get_instance();
        $forms_manager->render_forms_list();
    }

    public function render_builder_page() {
        $builder = FormFree_Form_Builder::get_instance();
        $builder->render_builder();
    }

    public function render_submissions_page() {
        $submissions = new FormFree_Submissions();
        $submissions->render_submissions_page();
    }

    public function render_settings_page() {
        $settings = new FormFree_Settings();
        $settings->render_settings_page();
    }
    
     public function render_tools_page() {
        require_once FORMFREE_PLUGIN_DIR . 'admin/class-tools.php';
        $tools = new FormFree_Tools();
        $tools->render_tools_page();
    }
}
