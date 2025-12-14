<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1><?php _e('Formularios', 'formfree'); ?>
        <a href="<?php echo admin_url('admin.php?page=formfree-builder'); ?>" class="page-title-action"><?php _e('Crear Nuevo', 'formfree'); ?></a>
    </h1>

    <?php if (empty($forms)): ?>
        <p><?php _e('No hay formularios creados aún.', 'formfree'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'formfree'); ?></th>
                    <th><?php _e('Nombre', 'formfree'); ?></th>
                    <th><?php _e('Shortcode', 'formfree'); ?></th>
                    <th><?php _e('Envíos', 'formfree'); ?></th>
                    <th><?php _e('Estado', 'formfree'); ?></th>
                    <th><?php _e('Acciones', 'formfree'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $form): 
                    $db = FormFree_Database::get_instance();
                    $count = $db->count_submissions($form->id);
                ?>
                    <tr>
                        <td><?php echo esc_html($form->id); ?></td>
                        <td><strong><?php echo esc_html($form->name); ?></strong></td>
                        <td><code>[formfree id="<?php echo esc_attr($form->id); ?>"]</code></td>
                        <td><?php echo intval($count); ?></td>
                        <td><?php echo $form->is_active ? '<span style="color:green;">●</span> ' . __('Activo', 'formfree') : '<span style="color:red;">●</span> ' . __('Inactivo', 'formfree'); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=formfree-builder&form_id=' . $form->id); ?>"><?php _e('Editar', 'formfree'); ?></a> |
                            <a href="<?php echo admin_url('admin.php?page=formfree-submissions&form_id=' . $form->id); ?>"><?php _e('Ver envíos', 'formfree'); ?></a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formfree-forms&action=delete&form_id=' . $form->id), 'formfree_delete_' . $form->id); ?>" onclick="return confirm('<?php _e('¿Estás seguro?', 'formfree'); ?>')" style="color:red;"><?php _e('Eliminar', 'formfree'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
