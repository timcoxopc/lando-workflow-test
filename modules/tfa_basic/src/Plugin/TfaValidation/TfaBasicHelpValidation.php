<?php

/**
 * @file class for TFA Basic
 */

namespace Drupal\tfa_basic\Plugin\TfaValidation;

use Drupal\Component\Utility\Html;
use Drupal\tfa\Plugin\TfaBasePlugin;
use Drupal\tfa\Plugin\TfaValidationInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @TfaValidation(
 *   id = "tfa_basic_help",
 *   label = @Translation("TFA Basic Help"),
 *   description = @Translation("TFA Basic Help Plugin")
 * )
 */
class TfaBasicHelpValidation extends TfaBasePlugin implements TfaValidationInterface {

  /**
   * @copydoc TfaBasePlugin::getForm()
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    $default = t('Contact support to reset your access');
    $content = \Drupal::config('tfa_basic.settings')->get('help_text');
    $form['help'] = array(
      '#type' => 'markup',
      '#markup' => Html::escape($content),
    );
    // Disallow login plugins from applying to this step.
    $form['#tfa_no_login'] = TRUE;
    return $form;
  }

  /**
   * @copydoc TfaValidationPluginInterface::validateForm()
   */
  public function validateForm(array $form, FormStateInterface $form_state) {
    // Unused.
  }

}
