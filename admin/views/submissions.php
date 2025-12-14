<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1><?php _e('Envíos de Formularios', 'formfree'); ?></h1>

    <p>
        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formfree-submissions&action=export' . ($form_id ? '&form_id=' . $form_id : '')), 'formfree_export'); ?>" class="button"><?php _e('Exportar a CSV', 'formfree'); ?></a>
    </p>

    <form method="get" action="">
        <input type="hidden" name="page" value="formfree-submissions">
        <select name="form_id" onchange="this.form.submit()">
            <option value=""><?php _e('Todos los formularios', 'formfree'); ?></option>
            <?php foreach ($forms as $f): ?>
                <option value="<?php echo esc_attr($f->id); ?>" <?php selected($form_id, $f->id); ?>>
                    <?php echo esc_html($f->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (empty($submissions)): ?>
        <p><?php _e('No hay envíos aún.', 'formfree'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'formfree'); ?></th>
                    <th><?php _e('Formulario', 'formfree'); ?></th>
                    <th><?php _e('Datos', 'formfree'); ?></th>
                    <th><?php _e('IP', 'formfree'); ?></th>
                    <th><?php _e('Fecha', 'formfree'); ?></th>
                    <th><?php _e('Kommo', 'formfree'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $sub): 
                    $db = FormFree_Database::get_instance();
                    $form_obj = $db->get_form($sub->form_id);
                    $data = json_decode($sub->form_data, true);
                ?>
                    <tr>
                        <td><?php echo esc_html($sub->id); ?></td>
                        <td><?php echo $form_obj ? esc_html($form_obj->name) : 'N/A'; ?></td>
                        <td>
                            <?php 
                            if (is_array($data)) {
                                echo '<ul style="margin:0;">';
                                foreach ($data as $key => $value) {
                                    echo '<li><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</li>';
                                }
                                echo '</ul>';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($sub->user_ip); ?></td>
                        <td><?php echo esc_html($sub->submitted_at); ?></td>
                        <td><?php echo $sub->kommo_sent ? '<span style="color:green;">✓</span>' : '<span style="color:gray;">—</span>'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    $page_links = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;', 'formfree'),
                        'next_text' => __('&raquo;', 'formfree'),
                        'total' => $total_pages,
                        'current' => $page,
                    ));
                    if ($page_links) {
                        echo '<span class="displaying-num">' . sprintf(__('%s envíos', 'formfree'), $total) . '</span>';
                        echo $page_links;
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
