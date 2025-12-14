<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1><?php _e('Configuración de FormFree', 'formfree'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('formfree_settings_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Email del administrador', 'formfree'); ?></th>
                <td>
                    <input type="email" name="admin_email" value="<?php echo esc_attr($settings['admin_email'] ?? get_option('admin_email')); ?>" class="regular-text">
                    <p class="description"><?php _e('Email donde se recibirán las notificaciones', 'formfree'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Retención de datos', 'formfree'); ?></th>
                <td>
                    <input type="number" name="retention_days" value="<?php echo esc_attr($settings['retention_days'] ?? 365); ?>" min="30" max="3650">
                    <p class="description"><?php _e('Días que se conservarán los envíos (GDPR)', 'formfree'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Notificaciones', 'formfree'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_notifications" value="1" <?php checked(!empty($settings['enable_notifications'])); ?>>
                        <?php _e('Enviar email al admin cuando llega un formulario', 'formfree'); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="enable_user_confirmation" value="1" <?php checked(!empty($settings['enable_user_confirmation'])); ?>>
                        <?php _e('Enviar email de confirmación al usuario', 'formfree'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <h2><?php _e('reCAPTCHA v2', 'formfree'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Habilitar reCAPTCHA', 'formfree'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="recaptcha_enabled" value="1" <?php checked(!empty($settings['recaptcha_enabled'])); ?>>
                        <?php _e('Activar validación reCAPTCHA', 'formfree'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Site Key', 'formfree'); ?></th>
                <td>
                    <input type="text" name="recaptcha_site_key" value="<?php echo esc_attr($settings['recaptcha_site_key'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Secret Key', 'formfree'); ?></th>
                <td>
                    <input type="text" name="recaptcha_secret_key" value="<?php echo esc_attr($settings['recaptcha_secret_key'] ?? ''); ?>" class="regular-text">
                    <p class="description"><?php _e('Obtén las claves en:', 'formfree'); ?> <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a></p>
                </td>
            </tr>
        </table>

        <h2><?php _e('Integración con Kommo CRM', 'formfree'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Habilitar Kommo', 'formfree'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="kommo_enabled" value="1" <?php checked(!empty($settings['kommo_enabled'])); ?>>
                        <?php _e('Enviar leads a Kommo CRM', 'formfree'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Subdominio', 'formfree'); ?></th>
                <td>
                    <input type="text" name="kommo_subdomain" value="<?php echo esc_attr($settings['kommo_subdomain'] ?? ''); ?>" class="regular-text">
                    <p class="description"><?php _e('Ej: tuempresa (si tu URL es tuempresa.kommo.com)', 'formfree'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Client ID', 'formfree'); ?></th>
                <td>
                    <input type="text" name="kommo_client_id" value="<?php echo esc_attr($settings['kommo_client_id'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Client Secret', 'formfree'); ?></th>
                <td>
                    <input type="text" name="kommo_client_secret" value="<?php echo esc_attr($settings['kommo_client_secret'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>
        </table>

        <?php submit_button(__('Guardar Configuración', 'formfree'), 'primary', 'formfree_save_settings'); ?>
    </form>
</div>
