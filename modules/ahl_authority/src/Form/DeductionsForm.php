<?php

namespace Drupal\ahl_authority\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class DeductionsForm extends ConfigFormBase {

  public function getFormId() {
    return 'ahl_authority_deductions';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $weight = 0;

    $form['#attached'] = array(
      'library' =>  array(
        'ahl_authority/ahl_authority'
      ),
    );

    $form['markup_1'] = array(
      '#markup' => '<p>Please complete all sections of the form</p>',
      '#weight' => $weight++,
    );

    $form['section_1'] = array(
      '#markup' => '<h2>Section 1 – Hostel staff to complete with resident. All information must be entered.</h2>',
      '#weight' => $weight++,
    );

    $form['full_name'] = [
      '#type' => 'textfield',
      '#size' => 15,
      '#weight' => $weight++,
      '#prefix' => '<span>I </span>',
      '#attributes' => [
        'placeholder' => 'Customers name',
      ]
    ];

    $form['customer_crn'] = [
      '#type' => 'textfield',
      '#size' => 15,
      '#weight' => $weight++,
      '#attributes' => [
        'placeholder' => 'Customers CRN',
      ]
    ];

    $form['deduction_amount'] = [
      '#type' => 'textfield',
      '#size' => 5,
      '#weight' => $weight++,
      '#prefix' => '<span> authorise the Department of Human Services to make a Deduction of $</span>',
      '#attributes' => [
        'placeholder' => 'eg 434.00',
      ]
    ];

    $form['centrelink_payment'] = [
      '#type' => 'textfield',
      '#size' => 15,
      '#weight' => $weight++,
      '#prefix' => '<span> each fortnight from my </span>',
      '#attributes' => [
        'placeholder' => 'e.g. IMX',
      ]
    ];

    $form['name_of_hostel'] = [
      '#type' => 'textfield',
      '#size' => 20,
      '#weight' => $weight++,
      '#prefix' => '<span> and pay this amount to Aboriginal Hostels Limited </span>',
      '#attributes' => [
        'placeholder' => 'e.g. Corroboree Hostel',
      ]
    ];

    $form['hostel_crn'] = [
      '#type' => 'textfield',
      '#size' => 10,
      '#weight' => $weight++,
      '#attributes' => [
        'placeholder' => 'Hostel CRN',
      ]
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#default_value' => array(
        'year' => date('Y'),
        'month' => date('m'),
        'day' => date('d'),
      ),
      '#weight' => $weight++,
      '#prefix' => '<span> for Indigenous Short-Term Accommodation commencing from </span>',
      '#suffix' => '<span>.</span>',
    ];

    $form['section_2'] = array(
      '#markup' => '<h2>Section 2 – Hostel staff to complete with resident. Choose one option by ticking the box and completing the relevant information.</h2>',
      '#weight' => $weight++,
    );

    $form['option_1'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'Option 1 – Setting up a <strong>target amount</strong>.',
      '#prefix' => '<div class="options-container">',
    ];

    $form['option_1_amount_start'] = [
      '#type' => 'textfield',
      '#size' => 5,
      '#weight' => $weight++,
      '#prefix' => '<div class="option-indent"><span>I request that this deduction of $</span>',
      '#attributes' => [
        'placeholder' => 'eg 434.00',
      ]
    ];

    $form['option_1_amount_end'] = [
      '#type' => 'textfield',
      '#size' => 5,
      '#weight' => $weight++,
      '#prefix' => '<span> continue until the target amount of $</span>',
      '#suffix' => '<span> is reached.</span>',
      '#attributes' => [
        'placeholder' => 'eg 434.00',
      ]
    ];

    $form['option_1_amount_markup'] = [
      '#markup' => '<p>* Please note that if a Deduction has a target amount 
                    and the final Deduction is set to pay less than $2, the 
                    second last deduction will be increased by up to $2 to 
                    cover the final amount.</p></div>',
      '#weight' => $weight++,
    ];

    $form['option_2'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'Option 2 – setting up an <strong>end date</strong>.',
    ];

    $form['option_2_amount'] = [
      '#type' => 'textfield',
      '#size' => 5,
      '#weight' => $weight++,
      '#prefix' => '<div class="option-indent"><span>I request that this deduction of $</span>',
      '#attributes' => [
        'placeholder' => 'eg 434.00',
      ]
    ];

    $form['option_2_start_date'] = [
      '#type' => 'date',
      '#default_value' => array(
        'year' => date('Y'),
        'month' => date('m'),
        'day' => date('d'),
      ),
      '#weight' => $weight++,
      '#prefix' => '<span> continue until </span>',
      '#suffix' => '<span> is reached.</span></div>',
    ];

    $form['option_3'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'Option 3 – selecting neither option 1 nor option 2.',
    ];

    $form['option_3_markup'] = [
      '#markup' => '<div class="option-indent"><p>I confirm that this deduction has no target amount and no end date.</p></div></div>',
      '#weight' => $weight++,
    ];

    $form['section_3'] = array(
      '#markup' => '<h2>Section 3 – Hostel staff to complete with resident. All information must be entered.</h2>',
      '#weight' => $weight++,
    );

    $form['section_3_markup'] = [
      '#markup' => '<p>I give permission for Aboriginal Hostels Limited to 
                    disclose my information to the Department of Human Services 
                    for the purposes of checking my account number, billing 
                    number and amount I want to pay, and reconciling my payment 
                    Deduction details.</p>
                    <p>I also give permission for Aboriginal Hostels Limited to 
                    give the Department of Human Services my correct account 
                    and billing number if required.</p>
                    <p>I understand that I can change or cancel my Deduction 
                    at any time, and further information about Centrepay can 
                    be found online at 
                    <a target="_blank" href="http://humanservices.gov.au/centrepay">humanservices.gov.au/centrepay</a>.</p>',
      '#weight' => $weight++,
    ];

    $form['date_of_birth'] = [
      '#type' => 'date',
      '#default_value' => array(
        'year' => date('Y'),
        'month' => date('m'),
        'day' => date('d'),
      ),
      '#weight' => $weight++,
      '#prefix' => '<span class="span-width">Date of birth: </span>',
      '#suffix' => '<br />',
    ];

    $form['date'] = [
      '#type' => 'date',
      '#default_value' => array(
        'year' => date('Y'),
        'month' => date('m'),
        'day' => date('d'),
      ),
      '#weight' => $weight++,
      '#prefix' => '<span class="span-width">Date: </span>',
    ];

    $form['section_4'] = array(
      '#markup' => '<h2>Section 4 – Hostel staff to complete.</h2>',
      '#weight' => $weight++,
    );

    $form['section_4_markup'] = array(
      '#markup' => '<p>Please tick to confirm each item completed:</p>',
      '#weight' => $weight++,
    );

    $form['section_4_option_1'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'I have confirmed the identity of <span class="customerName"></span>',
      '#required' => true,
    ];

    $form['section_4_option_2'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'I have completed Section 1, Section 2 and Section 3 with <span class="customerName"></span>',
      '#required' => true,
    ];

    $form['section_4_option_3'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'I have informed <span class="customerName"></span> that they can call or visit the Department of Human Services directly to suspend or cancel their deduction at any time.',
      '#required' => true,
    ];

    $form['section_4_option_4'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'If I become aware that <span class="customerName"></span> has excess credits or has left the hostel, within three business days I will contact my operations manager or business manager and discuss cancellation of the Deduction Authority.',
      '#required' => true,
    ];

    $form['section_4_option_5'] = [
      '#type' => 'checkbox',
      '#weight' => $weight++,
      '#title' => 'I will ensure this Deduction Authority is stored securely and held for a period of two years after the Deduction has ceased.',
      '#required' => true,
    ];

    $account = \Drupal::currentUser();

    $form['section_4_staff_member'] = [
      '#type' => 'textfield',
      '#weight' => $weight++,
      '#prefix' => '<span class="span-right-margin">Name of AHL staff member:</span>',
      '#required' => true,
      '#suffix' => '<br />',
      '#default_value' => $account->getAccountName(),
    ];

    $form['section_4_date'] = [
      '#type' => 'date',
      '#default_value' => array(
        'year' => date('Y'),
        'month' => date('m'),
        'day' => date('d'),
      ),
      '#weight' => $weight++,
      '#prefix' => '<span class="span-right-margin">Date: </span>',
    ];

    $form['footer_text'] = array(
      '#markup' => '<p>Additional information can be found on the <a target="_blank" href="https://www.humanservices.gov.au/organisations/business/services/centrelink/centrepay-businesses">Human Services website</a>.</p>
                    <p>The Centrepay Helpdesk can be reached on <strong>1800 044 063</strong>.</p>',
      '#weight' => $weight++,
    );

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Send Email here

    $message_html  = '<p>Aboriginal Hostels Limited Centrepay Deduction Authority Form</p>';

    $message_html .= '<p>Section 1</p>';

    $message_html .= t('<p>I @fullname @usercrn authorise the Department of Human Services to make a Deduction of $@amount each fortnight from my @account and pay this amount to Aboriginal Hostels Limited @hostel @hostelcrn for Indigenous Short-Term Accommodation commencing @date.</p>', [
      '@fullname' => $form_state->getValue('full_name'),
      '@usercrn' => $form_state->getValue('customer_crn'),
      '@amount' => $form_state->getValue('deduction_amount'),
      '@account' => $form_state->getValue('centrelink_payment'),
      '@hostel' => $form_state->getValue('name_of_hostel'),
      '@hostelcrn' => $form_state->getValue('hostel_crn'),
      '@date' => $form_state->getValue('start_date'),
    ]);

    $message_html .= '<p>Section 2</p>';

    if ($form_state->getValue('option_1') == true) {
      $message_html .= t('<p>Option 1 – Setting up a target amount.</p><p>I request that this deduction of $@o1start continue until the target amount of $@o1end is reached.</p><p>* Please note that if a Deduction has a target amount and the final Deduction is set to pay less than $2, the second last deduction will be increased by up to $2 to cover the final amount.</p>', [
        '@o1start' => $form_state->getValue('option_1_amount_start'),
        '@o1end' => $form_state->getValue('option_1_amount_end'),
      ]);
    }

    if ($form_state->getValue('option_2') == true) {
      $message_html .= t('<p>Option 2 – setting up an end date.</p><p>I request that this deduction of $@o2amount continue until @o2start is reached.</p>', [
        '@o2amount' => $form_state->getValue('option_2_amount'),
        '@o2start' => $form_state->getValue('option_2_start_date'),
      ]);
    }

    if ($form_state->getValue('option_3') == true) {
      $message_html .= t('<p>Option 3 – selecting neither option 1 nor option 2</p><p>I confirm that this deduction has no target amount and no end date.</p>');
    }

    $message_html .= '<p>Section 3</p>';

    $message_html .= t('<p>I give permission for Aboriginal Hostels Limited to disclose my information to the Department of Human Services for the purposes of checking my account number, billing number and amount I want to pay, and reconciling my payment Deduction details.</p>');
    $message_html .= t('<p>I also give permission for Aboriginal Hostels Limited to give the Department of Human Services my correct account and billing number if required.</p>');
    $message_html .= t('<p>I understand that I can change or cancel my Deduction at any time, and further information about Centrepay can be found online at humanservices.gov.au/centrepay.</p>');

    $message_html .= t('<br /><br /><p>Customer Signature</p><br /><br />');

    $message_html .= t('<p>Date of Birth: @dob</p><p>Date: @date.</p>', [
      '@dob' => $form_state->getValue('date_of_birth'),
      '@date' => $form_state->getValue('date'),
    ]);

    $message_html .= '<p>Section 4</p>';

    $message_html .= t('<p>Each following item has been ticked:</p>');

    if ($form_state->getValue('section_4_option_1') == true) {
      $message_html .= t('<p>I have confirmed the identity of @name.</p>', [
        '@name' => $form_state->getValue('full_name'),
      ]);
    }

    if ($form_state->getValue('section_4_option_2') == true) {
      $message_html .= t('<p>I have completed Section 1, Section 2 and Section 3 with @name.</p>', [
        '@name' => $form_state->getValue('full_name'),
      ]);
    }

    if ($form_state->getValue('section_4_option_3') == true) {
      $message_html .= t('<p>I have informed @name that they can call or visit the Department of Human Services directly to suspend or cancel their deduction at any time.</p>', [
        '@name' => $form_state->getValue('full_name'),
      ]);
    }

    if ($form_state->getValue('section_4_option_4') == true) {
      $message_html .= t('<p>If I become aware that @name has excess credits or has left the hostel, within three business days I will contact my operations manager or business manager and discuss cancellation of the Deduction Authority.</p>', [
        '@name' => $form_state->getValue('full_name'),
      ]);
    }

    if ($form_state->getValue('section_4_option_5') == true) {
      $message_html .= t('<p>I will ensure this Deduction Authority is stored securely and held for a period of two years after the Deduction has ceased.</p>');
    }

    $message_html .= '<br />';

    $message_html .= t('<p>Name of the AHL staff member: @name</p>', [
      '@name' => $form_state->getValue('section_4_staff_member'),
    ]);

    $message_html .= '<p>AHL staff member signature:</p><br /><br />';

    $message_html .= t('<p>Date: @date</p>', [
      '@date' => $form_state->getValue('section_4_date'),
    ]);

    $params = [];
    $params['mail_title'] = 'Centrepay Deduction Authority';
    $params['message']    = $message_html;

    $account = \Drupal::currentUser();
    $to = $account->getEmail();
    $to = 'toby.wild@opc.com.au';

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module      = 'ahl_authority';
    $key         = 'ahl_authority_key';
    $langcode    = \Drupal::currentUser()->getPreferredLangcode();
    $send        = true;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] != true) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      drupal_set_message($message, 'error');
      \Drupal::logger('custom_mail_sending_log')->notice($message);
    } else {
      $message = t('An email notification has been sent to @email ', array('@email' => $to));
      drupal_set_message($message,'status');
      \Drupal::logger('custom_mail_sending_log')->error($message);
    }

  }

  protected function getEditableConfigNames() {}

}