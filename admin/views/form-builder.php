<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1><?php echo $form ? __('Editar Formulario', 'formfree') : __('Crear Formulario', 'formfree'); ?></h1>

    <form method="post" action="" id="formfree-main-form">
        <?php wp_nonce_field('formfree_builder_nonce'); ?>
        <input type="hidden" name="form_id" id="form_id" value="<?php echo $form ? esc_attr($form->id) : '0'; ?>">

        <table class="form-table">
            <tr>
                <th scope="row"><label for="form_name"><?php _e('Nombre del formulario *', 'formfree'); ?></label></th>
                <td>
                    <input type="text" name="form_name" id="form_name" value="<?php echo $form ? esc_attr($form->name) : ''; ?>" class="regular-text" required>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="form_description"><?php _e('Descripción', 'formfree'); ?></label></th>
                <td>
                    <textarea name="form_description" id="form_description" rows="3" class="large-text"><?php echo $form ? esc_textarea($form->description) : ''; ?></textarea>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="form_steps"><?php _e('Número de pasos', 'formfree'); ?></label></th>
                <td>
                    <input type="number" name="form_steps" id="form_steps" value="<?php echo $form ? esc_attr($form->steps) : '1'; ?>" min="1" max="5">
                    <p class="description"><?php _e('Multi-step (máximo 5 pasos)', 'formfree'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="progress_bar_color"><?php _e('Color de la barra de progreso', 'formfree'); ?></label></th>
                <td>
                    <?php 
                    $settings = $form ? json_decode($form->settings, true) : array();
                    $progress_color = isset($settings['progress_bar_color']) ? $settings['progress_bar_color'] : '#10b981';
                    ?>
                    <input type="text" name="progress_bar_color" id="progress_bar_color" value="<?php echo esc_attr($progress_color); ?>" class="color-picker">
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="button_color"><?php _e('Color del botón', 'formfree'); ?></label></th>
                <td>
                    <?php $button_color = isset($settings['button_color']) ? $settings['button_color'] : '#10b981'; ?>
                    <input type="text" name="button_color" id="button_color" value="<?php echo esc_attr($button_color); ?>" class="color-picker">
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="success_message"><?php _e('Mensaje de éxito', 'formfree'); ?></label></th>
                <td>
                    <?php $success_msg = isset($settings['success_message']) ? $settings['success_message'] : '¡Gracias! Tu mensaje ha sido enviado.'; ?>
                    <input type="text" name="success_message" id="success_message" value="<?php echo esc_attr($success_msg); ?>" class="large-text">
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Estado', 'formfree'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?php checked($form && $form->is_active); ?>>
                        <?php _e('Formulario activo', 'formfree'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Guardar Configuración', 'formfree'), 'primary', 'formfree_save_form'); ?>
    </form>

    <?php if ($form && $form->id): ?>
        <hr style="margin: 30px 0;">

        <h2><?php _e('Campos del Formulario', 'formfree'); ?></h2>

        <div class="formfree-builder-container">
            <!-- Panel de tipos de campo -->
            <div class="formfree-field-types">
                <h3><?php _e('Agregar Campo', 'formfree'); ?></h3>
                <button type="button" class="button formfree-add-field" data-type="text">
                    📝 <?php _e('Texto', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="email">
                    📧 <?php _e('Email', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="tel">
                    📱 <?php _e('Teléfono', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="number">
                    🔢 <?php _e('Número', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="date">
                    📅 <?php _e('Fecha', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="textarea">
                    📄 <?php _e('Área de texto', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="select">
                    📋 <?php _e('Selección', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="radio">
                    🔘 <?php _e('Radio', 'formfree'); ?>
                </button>
                <button type="button" class="button formfree-add-field" data-type="checkbox">
                    ☑️ <?php _e('Checkbox', 'formfree'); ?>
                </button>
            </div>

            <!-- Lista de campos actuales -->
            <div class="formfree-fields-list" id="formfree-fields-list">
                <?php if (!empty($fields)): ?>
                    <?php foreach ($fields as $field): ?>
                        <div class="formfree-field-item" data-field-id="<?php echo esc_attr($field->id); ?>">
                            <div class="formfree-field-header">
                                <span class="dashicons dashicons-menu"></span>
                                <strong><?php echo esc_html($field->field_label); ?></strong>
                                <span class="formfree-field-type-badge"><?php echo esc_html($field->field_type); ?></span>
                                <span class="formfree-field-step-badge"><?php echo sprintf(__('Paso %d', 'formfree'), $field->step_number); ?></span>
                                <?php if ($field->is_required): ?>
                                    <span class="formfree-required-badge">*</span>
                                <?php endif; ?>
                            </div>
                            <div class="formfree-field-actions">
                                <button type="button" class="button button-small formfree-edit-field" data-field-id="<?php echo esc_attr($field->id); ?>">
                                    <?php _e('Editar', 'formfree'); ?>
                                </button>
                                <button type="button" class="button button-small button-link-delete formfree-delete-field" data-field-id="<?php echo esc_attr($field->id); ?>">
                                    <?php _e('Eliminar', 'formfree'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="formfree-no-fields"><?php _e('No hay campos aún. Usa los botones de arriba para agregar campos.', 'formfree'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <hr style="margin: 30px 0;">

        <h2><?php _e('Shortcode', 'formfree'); ?></h2>
        <div class="formfree-shortcode-box">
            <p><?php _e('Copia este shortcode para insertar el formulario en cualquier página:', 'formfree'); ?></p>
            <input type="text" value='[formfree id="<?php echo esc_attr($form->id); ?>"]' readonly class="large-text" onclick="this.select();" style="font-family: monospace;">
        </div>
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('Primero guarda el formulario para poder agregar campos.', 'formfree'); ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para editar campo -->
<div id="formfree-field-modal" class="formfree-modal" style="display:none;">
    <div class="formfree-modal-content">
        <span class="formfree-modal-close">&times;</span>
        <h2 id="formfree-modal-title"><?php _e('Agregar Campo', 'formfree'); ?></h2>
        <form id="formfree-field-form">
            <input type="hidden" id="field_id" name="field_id" value="">
            <input type="hidden" id="field_type" name="field_type" value="">

            <table class="form-table">
                <tr>
                    <th><label for="field_name"><?php _e('Nombre del campo *', 'formfree'); ?></label></th>
                    <td>
                        <input type="text" id="field_name" name="field_name" class="regular-text" required>
                        <p class="description"><?php _e('Nombre interno (sin espacios, ej: nombre_completo)', 'formfree'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="field_label"><?php _e('Etiqueta *', 'formfree'); ?></label></th>
                    <td>
                        <input type="text" id="field_label" name="field_label" class="regular-text" required>
                        <p class="description"><?php _e('Texto visible para el usuario', 'formfree'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="field_placeholder"><?php _e('Placeholder', 'formfree'); ?></label></th>
                    <td>
                        <input type="text" id="field_placeholder" name="field_placeholder" class="regular-text">
                    </td>
                </tr>
                <tr id="field_options_row" style="display:none;">
                    <th><label for="field_options"><?php _e('Opciones', 'formfree'); ?></label></th>
                    <td>
                        <textarea id="field_options" name="field_options" rows="4" class="large-text"></textarea>
                        <p class="description"><?php _e('Una opción por línea', 'formfree'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="field_step"><?php _e('Paso', 'formfree'); ?></label></th>
                    <td>
                        <select id="field_step" name="field_step">
                            <?php for ($i = 1; $i <= ($form ? $form->steps : 1); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo sprintf(__('Paso %d', 'formfree'), $i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Obligatorio', 'formfree'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" id="field_required" name="field_required" value="1">
                            <?php _e('Este campo es obligatorio', 'formfree'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Guardar Campo', 'formfree'); ?></button>
                <button type="button" class="button formfree-modal-close"><?php _e('Cancelar', 'formfree'); ?></button>
            </p>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.color-picker').wpColorPicker();
});
</script>
