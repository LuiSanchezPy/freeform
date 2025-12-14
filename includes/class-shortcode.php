<?php
if (!defined('ABSPATH')) exit;

class FormFree_Shortcode {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('formfree', array($this, 'render_form'));
    }

    public function render_form($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $form_id = intval($atts['id']);
        if (!$form_id) {
            return '<p>' . __('ID de formulario inválido', 'formfree') . '</p>';
        }

        $db = FormFree_Database::get_instance();
        $form = $db->get_form($form_id);

        if (!$form || !$form->is_active) {
            return '<p>' . __('Formulario no encontrado o inactivo', 'formfree') . '</p>';
        }

        ob_start();
        include FORMFREE_PLUGIN_DIR . 'public/templates/form-template.php';
        return ob_get_clean();
    }
}
