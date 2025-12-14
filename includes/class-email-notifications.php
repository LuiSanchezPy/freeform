<?php
if (!defined('ABSPATH')) exit;

class FormFree_Email_Notifications {

    public function send_admin_notification($form_id, $form_data) {
        $settings = get_option('formfree_settings', array());
        $admin_email = isset($settings['admin_email']) ? $settings['admin_email'] : get_option('admin_email');

        $db = FormFree_Database::get_instance();
        $form = $db->get_form($form_id);

        $subject = sprintf(__('Nuevo envío de formulario: %s', 'formfree'), $form->name);

        $message = __('Se ha recibido un nuevo envío de formulario:', 'formfree') . "\n\n";
        $message .= __('Formulario:', 'formfree') . ' ' . $form->name . "\n";
        $message .= __('Fecha:', 'formfree') . ' ' . current_time('mysql') . "\n\n";
        $message .= __('Datos del formulario:', 'formfree') . "\n";
        $message .= str_repeat('-', 40) . "\n";

        foreach ($form_data as $key => $value) {
            $message .= ucfirst($key) . ': ' . $value . "\n";
        }

        $headers = array('Content-Type: text/plain; charset=UTF-8');

        return wp_mail($admin_email, $subject, $message, $headers);
    }

    public function send_user_confirmation($user_email, $form_id) {
        $db = FormFree_Database::get_instance();
        $form = $db->get_form($form_id);

        $subject = __('Confirmación de envío de formulario', 'formfree');

        $message = __('Gracias por contactarnos.', 'formfree') . "\n\n";
        $message .= __('Hemos recibido tu mensaje y te responderemos pronto.', 'formfree') . "\n\n";
        $message .= __('Saludos,', 'formfree') . "\n";
        $message .= get_bloginfo('name');

        $headers = array('Content-Type: text/plain; charset=UTF-8');

        return wp_mail($user_email, $subject, $message, $headers);
    }
}
