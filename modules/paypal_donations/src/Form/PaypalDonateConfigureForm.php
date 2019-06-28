<?php

/**
 * @file
 * Contains \Drupal\paypal_donations\Form\PaypalDonateConfigureForm.
 */

namespace Drupal\paypal_donations\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the 'PayPal donations' configuration form.
 */
class PaypalDonateConfigureForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paypal_donations_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'paypal_donations.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('paypal_donations.settings');

    $form['single_label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Single donation label'),
      '#default_value' => $config->get('label.single'),
    );

    $form['recurring_label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Recurring donation label'),
      '#default_value' => $config->get('label.recurring'),
    );

    $form['donate_button_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Donate button text'),
      '#default_value' => $config->get('button'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @todo: cache clear or re-save PayPal block instances after submit.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('paypal_donations.settings')
      ->set('label.single', (string) $form_state->getValue('single_label'))
      ->set('label.recurring', (string) $form_state->getValue('recurring_label'))
      ->set('button', (string) $form_state->getValue('donate_button_text'))
      ->save(TRUE);
  }

}