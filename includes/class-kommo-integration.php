<?php
if (!defined('ABSPATH')) exit;

class FormFree_Kommo_Integration {
    private $subdomain;
    private $client_id;
    private $client_secret;
    private $access_token;

    public function __construct() {
        $settings = get_option('formfree_settings', array());
        $this->subdomain = isset($settings['kommo_subdomain']) ? $settings['kommo_subdomain'] : '';
        $this->client_id = isset($settings['kommo_client_id']) ? $settings['kommo_client_id'] : '';
        $this->client_secret = isset($settings['kommo_client_secret']) ? $settings['kommo_client_secret'] : '';
        $this->access_token = get_option('formfree_kommo_access_token', '');
    }

    public function send_lead($form_data) {
        if (empty($this->subdomain) || empty($this->access_token)) {
            return array('success' => false, 'message' => 'Kommo no configurado');
        }

        $api_url = "https://{$this->subdomain}.kommo.com/api/v4/leads";

        $lead_data = array(
            'name' => isset($form_data['nombre']) ? $form_data['nombre'] : 'Lead desde FormFree',
            'custom_fields_values' => array(),
        );

        if (isset($form_data['email'])) {
            $contact_data = array(
                'first_name' => isset($form_data['nombre']) ? $form_data['nombre'] : '',
                'custom_fields_values' => array(
                    array(
                        'field_code' => 'EMAIL',
                        'values' => array(
                            array('value' => $form_data['email'])
                        )
                    )
                )
            );

            if (isset($form_data['telefono'])) {
                $contact_data['custom_fields_values'][] = array(
                    'field_code' => 'PHONE',
                    'values' => array(
                        array('value' => $form_data['telefono'])
                    )
                );
            }

            $lead_data['_embedded'] = array('contacts' => array($contact_data));
        }

        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array($lead_data)),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        return array('success' => true, 'response' => $result);
    }

    public function get_authorization_url() {
        return "https://{$this->subdomain}.kommo.com/oauth?client_id={$this->client_id}";
    }
}
