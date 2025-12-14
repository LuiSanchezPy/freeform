<?php
if (!defined('ABSPATH')) exit;

$db = FormFree_Database::get_instance();
$form_settings = json_decode($form->settings, true);
$settings = get_option('formfree_settings', array());
?>

<div class="formfree-container" data-form-id="<?php echo esc_attr($form->id); ?>">
    <div class="formfree-wrapper">

        <?php if ($form->steps > 1): ?>
        <div class="formfree-progress-bar">
            <div class="formfree-progress-fill" style="background-color: <?php echo esc_attr($form_settings['progress_bar_color'] ?? '#10b981'); ?>; width: 0%;"></div>
        </div>

        <div class="formfree-step-indicator">
            <?php for ($i = 1; $i <= $form->steps; $i++): ?>
                <span class="formfree-step-dot <?php echo $i === 1 ? 'active' : ''; ?>" data-step="<?php echo $i; ?>">
                    <?php echo $i; ?>
                </span>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <form class="formfree-form" id="formfree-form-<?php echo esc_attr($form->id); ?>" data-steps="<?php echo esc_attr($form->steps); ?>">

            <?php
            for ($step = 1; $step <= $form->steps; $step++):
                $fields = $db->get_form_fields($form->id, $step);
            ?>
                <div class="formfree-step <?php echo $step === 1 ? 'active' : ''; ?>" data-step="<?php echo $step; ?>">

                    <?php if ($form->steps > 1): ?>
                        <h3 class="formfree-step-title"><?php echo sprintf(__('Paso %d de %d', 'formfree'), $step, $form->steps); ?></h3>
                    <?php endif; ?>

                    <?php foreach ($fields as $field): ?>
                        <div class="formfree-field-group">
                            <label for="field-<?php echo esc_attr($field->id); ?>" class="formfree-label">
                                <?php echo esc_html($field->field_label); ?>
                                <?php if ($field->is_required): ?>
                                    <span class="formfree-required">*</span>
                                <?php endif; ?>
                            </label>

                            <?php
                            $field_name = esc_attr($field->field_name);
                            $field_id = 'field-' . esc_attr($field->id);
                            $placeholder = esc_attr($field->placeholder);
                            $required = $field->is_required ? 'required' : '';

                            switch ($field->field_type):
                                case 'text':
                                case 'email':
                                case 'tel':
                                case 'number':
                                case 'date':
                                    ?>
                                    <input 
                                        type="<?php echo esc_attr($field->field_type); ?>" 
                                        id="<?php echo $field_id; ?>" 
                                        name="<?php echo $field_name; ?>" 
                                        placeholder="<?php echo $placeholder; ?>"
                                        class="formfree-input"
                                        <?php echo $required; ?>
                                    >
                                    <?php
                                    break;

                                case 'textarea':
                                    ?>
                                    <textarea 
                                        id="<?php echo $field_id; ?>" 
                                        name="<?php echo $field_name; ?>" 
                                        placeholder="<?php echo $placeholder; ?>"
                                        class="formfree-textarea"
                                        rows="4"
                                        <?php echo $required; ?>
                                    ></textarea>
                                    <?php
                                    break;

                                case 'select':
                                    $options = json_decode($field->options, true);
                                    ?>
                                    <select 
                                        id="<?php echo $field_id; ?>" 
                                        name="<?php echo $field_name; ?>"
                                        class="formfree-select"
                                        <?php echo $required; ?>
                                    >
                                        <option value=""><?php _e('Selecciona una opci&#x00F3;n', 'formfree'); ?></option>
                                        <?php if (is_array($options)): ?>
                                            <?php foreach ($options as $option): ?>
                                                <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php
                                    break;

                                case 'radio':
                                    $options = json_decode($field->options, true);
                                    if (is_array($options)):
                                        foreach ($options as $index => $option):
                                    ?>
                                        <label class="formfree-radio-label">
                                            <input 
                                                type="radio" 
                                                name="<?php echo $field_name; ?>" 
                                                value="<?php echo esc_attr($option); ?>"
                                                class="formfree-radio"
                                                <?php echo $required; ?>
                                            >
                                            <?php echo esc_html($option); ?>
                                        </label>
                                    <?php
                                        endforeach;
                                    endif;
                                    break;

                                case 'checkbox':
                                    ?>
                                    <label class="formfree-checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            id="<?php echo $field_id; ?>" 
                                            name="<?php echo $field_name; ?>" 
                                            value="1"
                                            class="formfree-checkbox"
                                            <?php echo $required; ?>
                                        >
                                        <?php echo esc_html($field->placeholder); ?>
                                    </label>
                                    <?php
                                    break;
                            endswitch;
                            ?>

                            <span class="formfree-error-message"></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="formfree-buttons">
                        <?php if ($step > 1): ?>
                            <button type="button" class="formfree-btn formfree-btn-prev" data-step="<?php echo $step; ?>">
                                <?php _e('Anterior', 'formfree'); ?>
                            </button>
                        <?php endif; ?>

                        <?php if ($step < $form->steps): ?>
                            <button type="button" class="formfree-btn formfree-btn-next" data-step="<?php echo $step; ?>" style="background-color: <?php echo esc_attr($form_settings['button_color'] ?? '#10b981'); ?>; color: <?php echo esc_attr($form_settings['button_text_color'] ?? '#ffffff'); ?>;">
                                <?php _e('Siguiente', 'formfree'); ?>
                            </button>
                        <?php else: ?>
                            <button type="submit" class="formfree-btn formfree-btn-submit" style="background-color: <?php echo esc_attr($form_settings['button_color'] ?? '#10b981'); ?>; color: <?php echo esc_attr($form_settings['button_text_color'] ?? '#ffffff'); ?>;">
                                <?php _e('Enviar', 'formfree'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>

            <?php if (!empty($settings['recaptcha_enabled']) && !empty($settings['recaptcha_site_key'])): ?>
                <div class="formfree-recaptcha">
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($settings['recaptcha_site_key']); ?>"></div>
                </div>
            <?php endif; ?>

        </form>

        <div class="formfree-message" style="display: none;"></div>
    </div>
</div>
