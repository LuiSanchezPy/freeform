jQuery(document).ready(function($) {
    
    var formId = $('#form_id').val();

    // Sortable
    if ($('#formfree-fields-list').length) {
        $('#formfree-fields-list').sortable({
            handle: '.dashicons-menu',
            placeholder: 'formfree-field-placeholder',
            update: function() {
                var order = [];
                $('#formfree-fields-list .formfree-field-item').each(function() {
                    order.push($(this).data('field-id'));
                });
                $.post(formfree_builder.ajax_url, {
                    action: 'formfree_update_field_order',
                    nonce: formfree_builder.nonce,
                    order: order
                });
            }
        });
    }

    // Agregar campo
    $('.formfree-add-field').on('click', function() {
        var fieldType = $(this).data('type');
        openModal('add', fieldType, {});
    });

    // Editar campo
    $(document).on('click', '.formfree-edit-field', function(e) {
        e.preventDefault();
        var fieldId = $(this).data('field-id');
        $.post(formfree_builder.ajax_url, {
            action: 'formfree_get_field',
            nonce: formfree_builder.nonce,
            field_id: fieldId
        }, function(response) {
            if (response.success) {
                openModal('edit', response.data.field.field_type, response.data.field);
            }
        });
    });

    // Eliminar campo
    $(document).on('click', '.formfree-delete-field', function(e) {
        e.preventDefault();
        if (!confirm(formfree_builder.strings.confirm_delete)) return;
        
        var fieldId = $(this).data('field-id');
        $.post(formfree_builder.ajax_url, {
            action: 'formfree_delete_field',
            nonce: formfree_builder.nonce,
            field_id: fieldId
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        });
    });

    // Abrir modal
    function openModal(mode, fieldType, data) {
        $('#field_id').val(data.id || '');
        $('#field_type').val(fieldType);
        $('#field_name').val(data.field_name || '');
        $('#field_label').val(data.field_label || '');
        $('#field_placeholder').val(data.placeholder || '');
        $('#field_step').val(data.step_number || 1);
        $('#field_required').prop('checked', data.is_required == 1);
        
        if (fieldType === 'select' || fieldType === 'radio') {
            $('#field_options_row').show();
            var opts = '';
            if (data.options) {
                try {
                    var arr = JSON.parse(data.options);
                    opts = Array.isArray(arr) ? arr.join('\n') : data.options;
                } catch(e) {
                    opts = data.options || '';
                }
            }
            $('#field_options').val(opts);
        } else {
            $('#field_options_row').hide();
        }
        
        $('#formfree-modal-title').text(mode === 'add' ? 'Agregar Campo' : 'Editar Campo');
        $('#formfree-field-modal').fadeIn(200);
    }

    // Cerrar modal
    $('.formfree-modal-close').on('click', function() {
        $('#formfree-field-modal').fadeOut(200);
    });

    // Submit modal
    $('#formfree-field-form').on('submit', function(e) {
        e.preventDefault();
        
        var btn = $(this).find('button[type=submit]');
        btn.prop('disabled', true).text('Guardando...');
        
        $.post(formfree_builder.ajax_url, {
            action: 'formfree_save_field',
            nonce: formfree_builder.nonce,
            form_id: formId,
            field_data: {
                id: $('#field_id').val(),
                type: $('#field_type').val(),
                name: $('#field_name').val(),
                label: $('#field_label').val(),
                placeholder: $('#field_placeholder').val(),
                step: $('#field_step').val(),
                required: $('#field_required').is(':checked') ? 1 : 0,
                options: $('#field_options').val(),
                order: 0
            }
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error al guardar');
                btn.prop('disabled', false).text('Guardar Campo');
            }
        });
    });

});
