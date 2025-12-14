<?php
/**
 * Clase para constructor de formularios
 */

if (!defined('ABSPATH')) {
    exit;
}

class FormFree_Form_Builder {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // AJAX handlers
        add_action('wp_ajax_formfree_save_field', array($this, 'ajax_save_field'));
        add_action('wp_ajax_formfree_get_field', array($this, 'ajax_get_field'));
        add_action('wp_ajax_formfree_delete_field', array($this, 'ajax_delete_field'));
        add_action('wp_ajax_formfree_update_field_order', array($this, 'ajax_update_field_order'));
    }


    public function render_forms_list() {
        $db = FormFree_Database::get_instance();
        $forms = $db->get_all_forms();

        if (isset($_GET['action']) && isset($_GET['form_id']) && check_admin_referer('formfree_delete_' . intval($_GET['form_id']))) {
            $form_id = intval($_GET['form_id']);

            if ($_GET['action'] === 'delete') {
                $db->delete_form($form_id);
                echo '<div class="notice notice-success"><p>' . __('Formulario eliminado', 'formfree') . '</p></div>';
                $forms = $db->get_all_forms();
            }
        }

        include FORMFREE_PLUGIN_DIR . 'admin/views/forms-list.php';
    }

    public function render_builder() {
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        $db = FormFree_Database::get_instance();

        if ($form_id) {
            $form = $db->get_form($form_id);
            $fields = $db->get_form_fields($form_id);
        } else {
            $form = null;
            $fields = array();
        }

        // Procesar guardado de formulario
        if (isset($_POST['formfree_save_form']) && check_admin_referer('formfree_builder_nonce')) {
            $saved_id = $this->save_form($_POST);
            if ($saved_id) {
                echo '<div class="notice notice-success"><p>' . __('Formulario guardado exitosamente', 'formfree') . '</p></div>';
                $form = $db->get_form($saved_id);
                $fields = $db->get_form_fields($saved_id);
                $form_id = $saved_id;

                // Redirigir para limpiar POST
                echo '<script>window.location.href="' . admin_url('admin.php?page=formfree-builder&form_id=' . $saved_id) . '";</script>';
            } else {
                echo '<div class="notice notice-error"><p>' . __('Error al guardar el formulario', 'formfree') . '</p></div>';
            }
        }

        include FORMFREE_PLUGIN_DIR . 'admin/views/form-builder.php';
    }

    private function save_form($post_data) {
        $db = FormFree_Database::get_instance();

        $form_data = array(
            'id' => isset($post_data['form_id']) ? intval($post_data['form_id']) : 0,
            'name' => isset($post_data['form_name']) ? sanitize_text_field($post_data['form_name']) : '',
            'description' => isset($post_data['form_description']) ? sanitize_textarea_field($post_data['form_description']) : '',
            'steps' => isset($post_data['form_steps']) ? intval($post_data['form_steps']) : 1,
            'is_active' => isset($post_data['is_active']) ? 1 : 0,
            'settings' => array(
                'progress_bar_color' => isset($post_data['progress_bar_color']) ? sanitize_hex_color($post_data['progress_bar_color']) : '#10b981',
                'button_color' => isset($post_data['button_color']) ? sanitize_hex_color($post_data['button_color']) : '#10b981',
                'button_text_color' => isset($post_data['button_text_color']) ? sanitize_hex_color($post_data['button_text_color']) : '#ffffff',
                'success_message' => isset($post_data['success_message']) ? sanitize_text_field($post_data['success_message']) : 'Â¡Gracias! Tu mensaje ha sido enviado.',
            ),
        );

        if (empty($form_data['name'])) {
            return false;
        }

        return $db->save_form($form_data);
    }

    /**
 * AJAX: Guardar campo
 */
public function ajax_save_field() {
    check_ajax_referer('formfree_builder_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Sin permisos'));
    }

    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    $field_data = isset($_POST['field_data']) ? $_POST['field_data'] : array();

    if (!$form_id || empty($field_data)) {
        wp_send_json_error(array('message' => 'Datos inv¨¢lidos'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'formfree_fields';

    // Convertir opciones a JSON si es necesario
    $options = '';
    if (!empty($field_data['options'])) {
        // Si ya viene como array, usar directamente
        if (is_array($field_data['options'])) {
            $options_array = $field_data['options'];
        } else {
            // Separar por saltos de l¨ªnea, limpiar espacios y filtrar vac¨ªos
            $options_array = array_values(array_filter(array_map('trim', explode("\n", $field_data['options']))));
        }
        // Convertir a JSON
        $options = json_encode($options_array, JSON_UNESCAPED_UNICODE);
    }

    $data = array(
        'form_id' => $form_id,
        'field_type' => sanitize_text_field($field_data['type']),
        'field_name' => sanitize_text_field($field_data['name']),
        'field_label' => sanitize_text_field($field_data['label']),
        'placeholder' => sanitize_text_field($field_data['placeholder']),
        'is_required' => isset($field_data['required']) && $field_data['required'] ? 1 : 0,
        'step_number' => intval($field_data['step']),
        'field_order' => intval($field_data['order']),
        'options' => $options,
    );

    if (isset($field_data['id']) && $field_data['id']) {
        $wpdb->update($table, $data, array('id' => intval($field_data['id'])), array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s'), array('%d'));
        $field_id = intval($field_data['id']);
    } else {
        $wpdb->insert($table, $data, array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s'));
        $field_id = $wpdb->insert_id;
    }

    if ($field_id) {
        $field = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $field_id));
        wp_send_json_success(array('field' => $field));
    } else {
        wp_send_json_error(array('message' => 'Error al guardar'));
    }
}

    /**
     * AJAX: Obtener datos de un campo para editar
     */
    public function ajax_get_field() {
        check_ajax_referer('formfree_builder_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }
    
        $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;
    
        if (!$field_id) {
            wp_send_json_error(array('message' => 'ID inv¨¢lido'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'formfree_fields';
        $field = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $field_id));

        if ($field) {
            wp_send_json_success(array('field' => $field));
        } else {
            wp_send_json_error(array('message' => 'Campo no encontrado'));
        }
    }

    /**
     * AJAX: Eliminar campo
     */
    public function ajax_delete_field() {
        check_ajax_referer('formfree_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;

        if (!$field_id) {
            wp_send_json_error(array('message' => 'ID invÃ¡lido'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'formfree_fields';
        $deleted = $wpdb->delete($table, array('id' => $field_id), array('%d'));

        if ($deleted) {
            wp_send_json_success(array('message' => 'Campo eliminado'));
        } else {
            wp_send_json_error(array('message' => 'Error al eliminar'));
        }
    }

    /**
     * AJAX: Actualizar orden de campos
     */
    public function ajax_update_field_order() {
        check_ajax_referer('formfree_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sin permisos'));
        }

        $order = isset($_POST['order']) ? $_POST['order'] : array();

        if (empty($order)) {
            wp_send_json_error(array('message' => 'Orden vacío'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'formfree_fields';

        foreach ($order as $index => $field_id) {
            $wpdb->update(
                $table,
                array('field_order' => $index),
                array('id' => intval($field_id)),
                array('%d'),
                array('%d')
            );
        }

        wp_send_json_success(array('message' => 'Orden actualizado'));
    }
}
