<?php
if (!defined('ABSPATH')) exit;

class FormFree_CSV_Exporter {

    public static function export_submissions($form_id = null) {
        $db = FormFree_Database::get_instance();
        $submissions = $db->get_submissions($form_id, 10000, 0);

        if (empty($submissions)) {
            return false;
        }

        $filename = 'formfree-export-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $headers = array('ID', 'Formulario', 'Fecha', 'IP', 'Datos');
        fputcsv($output, $headers);

        foreach ($submissions as $submission) {
            $form = $db->get_form($submission->form_id);
            $form_data = json_decode($submission->form_data, true);

            $row = array(
                $submission->id,
                $form ? $form->name : 'N/A',
                $submission->submitted_at,
                $submission->user_ip,
                json_encode($form_data, JSON_UNESCAPED_UNICODE),
            );

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
