/* FormFree - JavaScript Multi-Step */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Inicializar todos los formularios FormFree
        $('.formfree-form').each(function() {
            const $form = $(this);
            const formId = $form.closest('.formfree-container').data('form-id');
            const totalSteps = parseInt($form.data('steps'));
            let currentStep = 1;

            // Actualizar barra de progreso
            function updateProgress() {
                const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
                $form.closest('.formfree-wrapper').find('.formfree-progress-fill').css('width', progress + '%');

                // Actualizar indicador de pasos
                $form.closest('.formfree-wrapper').find('.formfree-step-dot').each(function(index) {
                    const stepNum = index + 1;
                    $(this).removeClass('active completed');
                    if (stepNum < currentStep) {
                        $(this).addClass('completed');
                    } else if (stepNum === currentStep) {
                        $(this).addClass('active');
                    }
                });
            }

            // Validar paso actual
            function validateStep() {
                let isValid = true;
                const $currentStep = $form.find('.formfree-step[data-step="' + currentStep + '"]');

                $currentStep.find('input, textarea, select').each(function() {
                    const $field = $(this);
                    const $fieldGroup = $field.closest('.formfree-field-group');
                    const $errorMsg = $fieldGroup.find('.formfree-error-message');

                    // Limpiar errores previos
                    $fieldGroup.removeClass('error');
                    $errorMsg.text('');

                    // Validar campo requerido
                    if ($field.prop('required') && !$field.val()) {
                        isValid = false;
                        $fieldGroup.addClass('error');
                        $errorMsg.text(formfree_ajax.messages.required);
                        return;
                    }

                    // Validar email
                    if ($field.attr('type') === 'email' && $field.val()) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test($field.val())) {
                            isValid = false;
                            $fieldGroup.addClass('error');
                            $errorMsg.text(formfree_ajax.messages.invalid_email);
                        }
                    }

                    // Validar tel¨¦fono
                    if ($field.attr('type') === 'tel' && $field.val()) {
                        const phoneRegex = /^[+]?[0-9\s-()]{10,}$/;
                        if (!phoneRegex.test($field.val())) {
                            isValid = false;
                            $fieldGroup.addClass('error');
                            $errorMsg.text(formfree_ajax.messages.invalid_phone);
                        }
                    }
                });

                return isValid;
            }

            // Bot¨®n Siguiente
            $form.on('click', '.formfree-btn-next', function() {
                if (validateStep()) {
                    $form.find('.formfree-step[data-step="' + currentStep + '"]').removeClass('active');
                    currentStep++;
                    $form.find('.formfree-step[data-step="' + currentStep + '"]').addClass('active');
                    updateProgress();

                    // Scroll al inicio del formulario
                    $('html, body').animate({
                        scrollTop: $form.offset().top - 100
                    }, 300);
                }
            });

            // Bot¨®n Anterior
            $form.on('click', '.formfree-btn-prev', function() {
                $form.find('.formfree-step[data-step="' + currentStep + '"]').removeClass('active');
                currentStep--;
                $form.find('.formfree-step[data-step="' + currentStep + '"]').addClass('active');
                updateProgress();

                // Scroll al inicio del formulario
                $('html, body').animate({
                    scrollTop: $form.offset().top - 100
                }, 300);
            });

            // Env¨ªo del formulario
            $form.on('submit', function(e) {
                e.preventDefault();

                if (!validateStep()) {
                    return false;
                }

                const $submitBtn = $form.find('.formfree-btn-submit');
                const originalText = $submitBtn.text();
                $submitBtn.prop('disabled', true).text('Enviando...');

                // Recopilar datos
                const formData = {};
                $form.find('input, textarea, select').each(function() {
                    const $field = $(this);
                    const name = $field.attr('name');

                    if (!name) return;

                    if ($field.attr('type') === 'checkbox') {
                        formData[name] = $field.is(':checked') ? $field.val() : '';
                    } else if ($field.attr('type') === 'radio') {
                        if ($field.is(':checked')) {
                            formData[name] = $field.val();
                        }
                    } else {
                        formData[name] = $field.val();
                    }
                });

                // AJAX
                $.ajax({
                    url: formfree_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'formfree_submit',
                        nonce: formfree_ajax.nonce,
                        form_id: formId,
                        form_data: formData,
                        'g-recaptcha-response': typeof grecaptcha !== 'undefined' && typeof grecaptcha.getResponse === 'function' ? grecaptcha.getResponse() : ''
                    },
                    success: function(response) {
                        if (response.success) {
                            $form.hide();
                            $('.formfree-message')
                                .addClass('success')
                                .text(response.data.message)
                                .show();

                            // Reset reCAPTCHA
                            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.reset === 'function') {
                                grecaptcha.reset();
                            }
                        } else {
                            $('.formfree-message')
                                .addClass('error')
                                .text(response.data.message || formfree_ajax.messages.error_general)
                                .show();
                            $submitBtn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        $('.formfree-message')
                            .addClass('error')
                            .text(formfree_ajax.messages.error_general)
                            .show();
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });

                return false;
            });

            // Inicializar progreso
            updateProgress();
        });
    });

})(jQuery);

