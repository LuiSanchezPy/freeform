<?php
if (!defined('ABSPATH')) exit;

class FormFree_Database {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Crear tablas de FormFree
     * Ahora con logging para debugging
     */
    public static function crear_tablas() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Tabla de formularios
        $table_forms = $wpdb->prefix . 'formfree_forms';
        $sql_forms = "CREATE TABLE $table_forms (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            steps int(11) DEFAULT 1,
            settings text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Tabla de campos
        $table_fields = $wpdb->prefix . 'formfree_fields';
        $sql_fields = "CREATE TABLE $table_fields (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            form_id bigint(20) unsigned NOT NULL,
            field_type varchar(50) NOT NULL,
            field_name varchar(255) NOT NULL,
            field_label varchar(255) NOT NULL,
            placeholder varchar(255),
            is_required tinyint(1) DEFAULT 0,
            step_number int(11) DEFAULT 1,
            field_order int(11) DEFAULT 0,
            options text,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";

        // Tabla de envíos
        $table_submissions = $wpdb->prefix . 'formfree_submissions';
        $sql_submissions = "CREATE TABLE $table_submissions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            form_id bigint(20) unsigned NOT NULL,
            form_data longtext NOT NULL,
            user_ip varchar(100),
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            kommo_sent tinyint(1) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Ejecutar dbDelta con logging
        error_log('FormFree: Intentando crear tabla formfree_forms...');
        $result1 = dbDelta($sql_forms);
        error_log('FormFree: Resultado forms = ' . print_r($result1, true));
        
        error_log('FormFree: Intentando crear tabla formfree_fields...');
        $result2 = dbDelta($sql_fields);
        error_log('FormFree: Resultado fields = ' . print_r($result2, true));
        
        error_log('FormFree: Intentando crear tabla formfree_submissions...');
        $result3 = dbDelta($sql_submissions);
        error_log('FormFree: Resultado submissions = ' . print_r($result3, true));
        
        // Verificar si se crearon
        $tables_created = array(
            'forms' => ($wpdb->get_var("SHOW TABLES LIKE '$table_forms'") == $table_forms),
            'fields' => ($wpdb->get_var("SHOW TABLES LIKE '$table_fields'") == $table_fields),
            'submissions' => ($wpdb->get_var("SHOW TABLES LIKE '$table_submissions'") == $table_submissions)
        );
        
        error_log('FormFree: Tablas creadas = ' . print_r($tables_created, true));
        
        return $tables_created;
    }

    /**
     * Verificar si las tablas existen
     */
    public static function tablas_existen() {
        global $wpdb;
        $table_forms = $wpdb->prefix . 'formfree_forms';
        $table_fields = $wpdb->prefix . 'formfree_fields';
        $table_submissions = $wpdb->prefix . 'formfree_submissions';
        
        return (
            $wpdb->get_var("SHOW TABLES LIKE '$table_forms'") == $table_forms &&
            $wpdb->get_var("SHOW TABLES LIKE '$table_fields'") == $table_fields &&
            $wpdb->get_var("SHOW TABLES LIKE '$table_submissions'") == $table_submissions
        );
    }

    public function get_form($form_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}formfree_forms WHERE id = %d",
            $form_id
        ));
    }

    public function get_all_forms() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}formfree_forms ORDER BY created_at DESC");
    }

    public function save_form($form_data) {
        global $wpdb;
        $table = $wpdb->prefix . 'formfree_forms';

        $data = array(
            'name' => $form_data['name'],
            'description' => $form_data['description'],
            'steps' => $form_data['steps'],
            'settings' => json_encode($form_data['settings']),
            'is_active' => $form_data['is_active'],
        );

        if ($form_data['id']) {
            $result = $wpdb->update($table, $data, array('id' => $form_data['id']));
            if ($result === false) {
                error_log('FormFree UPDATE Error: ' . $wpdb->last_error);
                error_log('FormFree Last Query: ' . $wpdb->last_query);
                return false;
            }
            return $form_data['id'];
        } else {
            $result = $wpdb->insert($table, $data);
            if ($result === false) {
                error_log('FormFree INSERT Error: ' . $wpdb->last_error);
                error_log('FormFree Last Query: ' . $wpdb->last_query);
                return false;
            }
            return $wpdb->insert_id;
        }
    }

    public function delete_form($form_id) {
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'formfree_forms', array('id' => $form_id));
        $wpdb->delete($wpdb->prefix . 'formfree_fields', array('form_id' => $form_id));
        $wpdb->delete($wpdb->prefix . 'formfree_submissions', array('form_id' => $form_id));
    }

    public function get_form_fields($form_id, $step = null) {
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}formfree_fields WHERE form_id = %d";
        if ($step !== null) {
            $query .= $wpdb->prepare(" AND step_number = %d", $step);
        }
        $query .= " ORDER BY field_order ASC";
        return $wpdb->get_results($wpdb->prepare($query, $form_id));
    }

    public function save_submission($form_id, $form_data, $user_ip) {
        global $wpdb;
        return $wpdb->insert(
            $wpdb->prefix . 'formfree_submissions',
            array(
                'form_id' => $form_id,
                'form_data' => json_encode($form_data),
                'user_ip' => $user_ip,
                'kommo_sent' => 0
            ),
            array('%d', '%s', '%s', '%d')
        );
    }

    public function get_submissions($form_id = null, $limit = 20, $offset = 0) {
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}formfree_submissions";
        if ($form_id) {
            $query .= $wpdb->prepare(" WHERE form_id = %d", $form_id);
        }
        $query .= " ORDER BY submitted_at DESC LIMIT %d OFFSET %d";
        return $wpdb->get_results($wpdb->prepare($query, $limit, $offset));
    }

    public function count_submissions($form_id = null) {
        global $wpdb;
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}formfree_submissions";
        if ($form_id) {
            $query .= $wpdb->prepare(" WHERE form_id = %d", $form_id);
        }
        return $wpdb->get_var($query);
    }

    public static function limpiar_submissions_antiguas($days) {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}formfree_submissions WHERE submitted_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }
}
