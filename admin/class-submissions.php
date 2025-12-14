<?php
if (!defined('ABSPATH')) exit;

class FormFree_Submissions {

    public function render_submissions_page() {
        $db = FormFree_Database::get_instance();

        if (isset($_GET['action']) && $_GET['action'] === 'export' && check_admin_referer('formfree_export')) {
            $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : null;
            FormFree_CSV_Exporter::export_submissions($form_id);
            exit;
        }

        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : null;
        $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $submissions = $db->get_submissions($form_id, $per_page, $offset);
        $total = $db->count_submissions($form_id);
        $total_pages = ceil($total / $per_page);

        $forms = $db->get_all_forms();

        include FORMFREE_PLUGIN_DIR . 'admin/views/submissions.php';
    }
}
