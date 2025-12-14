<?php
if (!defined('ABSPATH')) exit;

class FormFree_Form_Handler {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_formfree_submit', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_formfree_submit', array($this, 'handle_submission'));
    }

    public function handle_submission() {
        check_ajax_referer('formfree_nonce', 'nonce');

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : array();

        if (!$form_id || empty($form_data)) {
            wp_send_json_error(array('message' => __('Datos inválidos', 'formfree')));
        }

        $settings = get_option('formfree_settings', array());

        // Verificar reCAPTCHA
        if (!empty($settings['recaptcha_enabled']) && !empty($settings['recaptcha_secret_key'])) {
            $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
            if (!$this->verify_recaptcha($recaptcha_response, $settings['recaptcha_secret_key'])) {
                wp_send_json_error(array('message' => __('Error de verificación reCAPTCHA', 'formfree')));
            }
        }

        // Validar y sanitizar datos
        $validator = new FormFree_Validator();
        $clean_data = array();
        foreach ($form_data as $key => $value) {
            $clean_data[$validator->sanitize_text($key)] = $validator->sanitize_text($value);
        }

        // Guardar en base de datos
        $db = FormFree_Database::get_instance();
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $submission_saved = $db->save_submission($form_id, $clean_data, $user_ip);

        if (!$submission_saved) {
            wp_send_json_error(array('message' => __('Error al guardar', 'formfree')));
        }

        // Enviar a Kommo si está habilitado
        if (!empty($settings['kommo_enabled'])) {
            $kommo = new FormFree_Kommo_Integration();
            $kommo->send_lead($clean_data);
        }

        // Enviar notificaciones por email
        if (!empty($settings['enable_notifications'])) {
            $email = new FormFree_Email_Notifications();
            $email->send_admin_notification($form_id, $clean_data);

            if (!empty($settings['enable_user_confirmation']) && isset($clean_data['email'])) {
                $email->send_user_confirmation($clean_data['email'], $form_id);
            }
        }

        // Obtener mensaje de éxito personalizado
        $form = $db->get_form($form_id);
        $form_settings = json_decode($form->settings, true);
        $success_message = isset($form_settings['success_message']) ? $form_settings['success_message'] : __('¡Gracias! Tu mensaje ha sido enviado.', 'formfree');

        wp_send_json_success(array('message' => $success_message));
    }

    private function verify_recaptcha($response, $secret) {
        if (empty($response)) {
            return false;
        }

        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $response_data = wp_remote_post($verify_url, array(
            'body' => array(
                'secret' => $secret,
                'response' => $response
            )
        ));

        if (is_wp_error($response_data)) {
            return false;
        }

        $result = json_decode(wp_remote_retrieve_body($response_data), true);
        return isset($result['success']) && $result['success'] === true;
    }
}
