<?php
if (!defined('ABSPATH')) exit;

class FormFree_Tools {
    
    public function render_tools_page() {
        // Procesar acción de crear tablas
        if (isset($_POST['formfree_crear_tablas']) && check_admin_referer('formfree_tools_nonce')) {
            $result = FormFree_Database::crear_tablas();
            
            if ($result['forms'] && $result['fields'] && $result['submissions']) {
                echo '<div class="notice notice-success"><p><strong>&#x2705; ¡&#x00C9;xito!</strong> Las 3 tablas se crearon correctamente.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p><strong>&#x274C; Error:</strong> Algunas tablas no se pudieron crear. Revisa los logs de PHP.</p>';
                echo '<p>Estado: Forms=' . ($result['forms'] ? 'OK' : 'FALLO') . ', Fields=' . ($result['fields'] ? 'OK' : 'FALLO') . ', Submissions=' . ($result['submissions'] ? 'OK' : 'FALLO') . '</p></div>';
            }
        }
        
        $tablas_existen = FormFree_Database::tablas_existen();
        global $wpdb;
        ?>
        <div class="wrap">
            <h1><?php _e('Herramientas FormFree', 'formfree'); ?></h1>
            
            <div class="card">
                <h2>Estado de las Tablas</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Tabla</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tables = array(
                            'formfree_forms' => 'Formularios',
                            'formfree_fields' => 'Campos',
                            'formfree_submissions' => 'Env&#x00ED;os'
                        );
                        
                        foreach ($tables as $table => $label) {
                            $full_table = $wpdb->prefix . $table;
                            $exists = ($wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table);
                            ?>
                            <tr>
                                <td><code><?php echo esc_html($full_table); ?></code></td>
                                <td>
                                    <?php if ($exists): ?>
                                        <span style="color: green;">&#x2705; Existe</span>
                                    <?php else: ?>
                                        <span style="color: red;">&#x274C; No existe</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!$tablas_existen): ?>
                <div class="card" style="border-left: 4px solid #dc3232;">
                    <h2 style="color: #dc3232;">&#x26A0; Acci&#x00F3;n Requerida</h2>
                    <p>Las tablas de FormFree no existen en la base de datos. Haz clic en el bot&#x00F3;n de abajo para crearlas manualmente.</p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('formfree_tools_nonce'); ?>
                        <p>
                            <button type="submit" name="formfree_crear_tablas" class="button button-primary button-hero">
                                &#x1F527; Crear Tablas Ahora
                            </button>
                        </p>
                    </form>
                </div>
            <?php else: ?>
                <div class="notice notice-success inline">
                    <p><strong>&#x2705; Todo correcto:</strong> Las tablas de FormFree existen y est&#x00E1;n listas para usar.</p>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Informaci&#x00F3;n del Servidor</h2>
                <ul>
                    <li><strong>Prefijo de tablas:</strong> <code><?php echo esc_html($wpdb->prefix); ?></code></li>
                    <li><strong>Base de datos:</strong> <?php echo esc_html($wpdb->dbname); ?></li>
                    <li><strong>Versi&#x00F3;n MySQL/MariaDB:</strong> <?php echo esc_html($wpdb->db_version()); ?></li>
                    <li><strong>Charset:</strong> <?php echo esc_html($wpdb->charset); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}
