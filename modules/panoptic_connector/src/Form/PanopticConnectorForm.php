<?php

namespace Drupal\panoptic_connector\Form;

use Drupal\Core\Form\ConfigFormBase;
 
use Drupal\Core\Form\FormStateInterface;
 
class PanopticConnectorForm extends ConfigFormBase {

  public function getFormId() {
    return 'panoptic_connector_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('panoptic_connector.settings');
    $form['vcode'] = array(
      '#type' => 'textfield',
      '#title' => t('Panoptic Verification Code:'),
      '#default_value' => $config->get('panoptic_connector.vcode'),
      '#description' => t('Enter your Panoptic site verification code here.'),
      '#required' => TRUE,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('panoptic_connector.settings');
    $config->set('panoptic_connector.vcode', $form_state->getValue('vcode'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  protected function getEditableConfigNames() {
    return [
      'panoptic_connector.settings',
    ];
  }

}
