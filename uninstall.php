<?php
/**
 * Desinstalación de FormFree
 * 
 * Este archivo se ejecuta cuando el plugin es eliminado desde WordPress
 */

// Si no es llamado por WordPress, salir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Nombres de las tablas
$table_forms = $wpdb->prefix . 'formfree_forms';
$table_submissions = $wpdb->prefix . 'formfree_submissions';
$table_fields = $wpdb->prefix . 'formfree_fields';

// Eliminar tablas
$wpdb->query("DROP TABLE IF EXISTS $table_forms");
$wpdb->query("DROP TABLE IF EXISTS $table_submissions");
$wpdb->query("DROP TABLE IF EXISTS $table_fields");

// Eliminar opciones
delete_option('formfree_settings');
delete_option('formfree_kommo_access_token');
delete_option('formfree_version');

// Limpiar cron jobs
wp_clear_scheduled_hook('formfree_daily_cleanup');

// Limpiar transients
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_formfree_%'");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_formfree_%'");
