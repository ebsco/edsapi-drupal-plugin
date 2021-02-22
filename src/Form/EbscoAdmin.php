<?php

/**
 * @file
 * Contains \Drupal\ebsco\Form\EbscoAdmin.
 */

namespace Drupal\ebsco\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class EbscoAdmin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ebsco_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ebsco.settings');

    $values = $form_state->getValues();
    $config->set("ebsco_guest", $values["ebsco_guest"]);
    $config->set("ebsco_default_limit", $values["ebsco_default_limit"]);
    $config->set("ebsco_default_sort", $values["ebsco_default_sort"]);
    $config->set("ebsco_default_amount", $values["ebsco_default_amount"]);
    $config->set("ebsco_default_mode", $values["ebsco_default_mode"]);
    $config->set("ebsco_user", $values["ebsco_user"]);
    $config->set("ebsco_password", $values["ebsco_password"]);
    $config->set("ebsco_interface", $values["ebsco_interface"]);
    $config->set("ebsco_autocomplete", $values["ebsco_autocomplete"]);
    $config->set("ebsco_organization", $values["ebsco_organization"]);
    $config->set("ebsco_profile", $values["ebsco_profile"]);
    $config->set("ebsco_local_ips", $values["ebsco_local_ips"]);
    $config->save(); 

	
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ebsco.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [];

	$config = $this->config('ebsco.settings');
	
    $form['ebsco_credentials'] = [
      '#type' => 'fieldset',
      '#title' => t('EDS API credentials'),
    ];

    $form['ebsco_credentials']['ebsco_profile'] = [
      '#type' => 'textfield',
      '#title' => t('Profile Id'),
      '#default_value' => $config->get('ebsco_profile'),
      '#size' => 50,
      '#description' => t("The API Profile Id."),
      '#required' => TRUE,
    ];

    $form['ebsco_credentials']['ebsco_user'] = [
      '#type' => 'textfield',
      '#title' => t('User Id'),
      '#default_value' => $config->get('ebsco_user'),
      '#size' => 50,
      '#description' => t("The API User Id."),
      '#required' => TRUE,
    ];

    $form['ebsco_credentials']['ebsco_password'] = [
      '#type' => 'textfield',
      '#title' => t('Password'),
      '#default_value' => $config->get('ebsco_password'),
      '#size' => 50,
      '#description' => t("The API password."),
      '#required' => TRUE,
    ];

    $form['ebsco_credentials']['ebsco_interface'] = [
      '#type' => 'textfield',
      '#title' => t('Interface Id'),
      '#default_value' => $config->get('ebsco_interface'),
      '#size' => 50,
      '#description' => t("The API Interface Id."),
      '#required' => FALSE,
    ];

    $form['ebsco_credentials']['ebsco_autocomplete'] = [
      '#type' => 'textfield',
      '#title' => t('Autocomplete'),
      '#default_value' => $config->get('ebsco_autocomplete'),
      '#size' => 50,
      '#description' => t("The API has Autocomplete."),
      '#required' => TRUE,
    ];

    $form['ebsco_credentials']['ebsco_organization'] = [
      '#type' => 'textfield',
      '#title' => t('Organization Id'),
      '#default_value' => $config->get('ebsco_organization'),
      '#size' => 50,
      '#description' => t("The API Organization Id."),
      '#required' => FALSE,
    ];

    $form['ebsco_credentials']['ebsco_local_ips'] = [
      '#type' => 'textfield',
      '#title' => t('Local IP addresses'),
      '#default_value' => $config->get('ebsco_local_ips'),
      '#size' => 100,
      '#description' => t("Local IP address list for guest detection (ex: 127.0.0.1, 192.168.10.1, 172.18.12)"),
      '#required' => FALSE,
    ];

    $form['ebsco_credentials']['ebsco_guest'] = [
      '#type' => 'radios',
      '#title' => t('Guest ?'),
      '#default_value' => $config->get('ebsco_guest'),
      '#description' => t("The Guest session."),
      '#options' => [
        t('No'),
        t('Yes'),
      ],
      '#required' => TRUE,
    ];

    $form['ebsco_general'] = [
      '#type' => 'fieldset',
      '#title' => t('General Settings'),
    ];

    $form['ebsco_general']['ebsco_default_limit'] = [
      '#type' => 'select',
      '#title' => t('Default limit'),
      '#default_value' => $config->get('ebsco_default_limit'),
      '#description' => t("Default number of results per page."),
      '#options' => \EBSCODocument::limit_options(),
      '#required' => TRUE,
    ];

    $form['ebsco_general']['ebsco_default_sort'] = [
      '#type' => 'select',
      '#title' => t('Default sort'),
      '#default_value' => $config->get('ebsco_default_sort'),
      '#description' => t("Default sorting option."),
      '#options' => \EBSCODocument::sort_options(),
      '#required' => TRUE,
    ];

    $form['ebsco_general']['ebsco_default_amount'] = [
      '#type' => 'select',
      '#title' => t('Default detail level'),
      '#default_value' => $config->get('ebsco_default_amount'),
      '#description' => t("Default level of data detail."),
      '#options' => \EBSCODocument::amount_options(),
      '#required' => TRUE,
    ];

    $form['ebsco_general']['ebsco_default_mode'] = [
      '#type' => 'select',
      '#title' => t('Default search mode'),
      '#default_value' => $config->get('ebsco_default_mode'),
      '#description' => t("Default search mode."),
      '#options' => \EBSCODocument::mode_options(),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

}
?>
