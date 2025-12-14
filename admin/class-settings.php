<?php
if (!defined('ABSPATH')) exit;

class FormFree_Settings {

    public function render_settings_page() {
        if (isset($_POST['formfree_save_settings']) && check_admin_referer('formfree_settings_nonce')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada exitosamente', 'formfree') . '</p></div>';
        }

        $settings = get_option('formfree_settings', array());
        include FORMFREE_PLUGIN_DIR . 'admin/views/settings.php';
    }

    private function save_settings() {
        $settings = array(
            'retention_days' => isset($_POST['retention_days']) ? intval($_POST['retention_days']) : 365,
            'admin_email' => isset($_POST['admin_email']) ? sanitize_email($_POST['admin_email']) : get_option('admin_email'),
            'enable_notifications' => isset($_POST['enable_notifications']) ? 1 : 0,
            'enable_user_confirmation' => isset($_POST['enable_user_confirmation']) ? 1 : 0,
            'recaptcha_enabled' => isset($_POST['recaptcha_enabled']) ? 1 : 0,
            'recaptcha_site_key' => isset($_POST['recaptcha_site_key']) ? sanitize_text_field($_POST['recaptcha_site_key']) : '',
            'recaptcha_secret_key' => isset($_POST['recaptcha_secret_key']) ? sanitize_text_field($_POST['recaptcha_secret_key']) : '',
            'kommo_enabled' => isset($_POST['kommo_enabled']) ? 1 : 0,
            'kommo_subdomain' => isset($_POST['kommo_subdomain']) ? sanitize_text_field($_POST['kommo_subdomain']) : '',
            'kommo_client_id' => isset($_POST['kommo_client_id']) ? sanitize_text_field($_POST['kommo_client_id']) : '',
            'kommo_client_secret' => isset($_POST['kommo_client_secret']) ? sanitize_text_field($_POST['kommo_client_secret']) : '',
        );

        update_option('formfree_settings', $settings);
    }
}
