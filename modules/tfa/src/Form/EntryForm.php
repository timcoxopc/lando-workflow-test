<?php

namespace Drupal\tfa\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\tfa\TfaLoginPluginManager;
use Drupal\tfa\TfaValidationPluginManager;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TFA entry form.
 */
class EntryForm extends FormBase {

  /**
   * Validation plugin manager.
   *
   * @var \Drupal\tfa\TfaValidationPluginManager
   */
  protected $tfaValidationManager;

  /**
   * Login plugin manager.
   *
   * @var \Drupal\tfa\TfaLoginPluginManager
   */
  protected $tfaLoginManager;

  /**
   * The validation plugin object.
   *
   * @var \Drupal\tfa\Plugin\TfaValidationInterface
   */
  protected $tfaValidationPlugin;

  /**
   * The login plugins.
   *
   * @var \Drupal\tfa\Plugin\TfaLoginInterface
   */
  protected $tfaLoginPlugins;

  /**
   * The fallback plugin object.
   *
   * @var \Drupal\tfa\Plugin\TfaValidationInterface
   */
  protected $tfaFallbackPlugin;

  /**
   * TFA configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $tfaSettings;

  /**
   * The flood control mechanism.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The flood control identifier.
   *
   * @var string
   */
  protected $floodIdentifier;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * User data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * EntryForm constructor.
   *
   * @param \Drupal\tfa\TfaValidationPluginManager $tfa_validation_manager
   *   Plugin manager for validation plugins.
   * @param \Drupal\tfa\TfaLoginPluginManager $tfa_login_manager
   *   Plugin manager for login plugins.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood control mechanism.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date service.
   * @param \Drupal\user\UserDataInterface $user_data
   *   User data service.
   */
  public function __construct(TfaValidationPluginManager $tfa_validation_manager, TfaLoginPluginManager $tfa_login_manager, FloodInterface $flood, DateFormatterInterface $date_formatter, UserDataInterface $user_data) {
    $this->tfaValidationManager = $tfa_validation_manager;
    $this->tfaLoginManager = $tfa_login_manager;
    $this->tfaSettings = $this->config('tfa.settings');
    $this->flood = $flood;
    $this->dateFormatter = $date_formatter;
    $this->userData = $user_data;
  }

  /**
   * Creates service objects for the class contructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to get the required services.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.tfa.validation'),
      $container->get('plugin.manager.tfa.login'),
      $container->get('flood'),
      $container->get('date.formatter'),
      $container->get('user.data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tfa_entry_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $alternate_plugin = $this->getRequest()->get('plugin');
    $validation_plugin_definitions = $this->tfaValidationManager->getDefinitions();
    $user_settings = $this->userData->get('tfa', $user->id(), 'tfa_user_settings');
    $user_enabled_validation_plugins = isset($user_settings['data']['plugins']) ? $user_settings['data']['plugins'] : [];

    // Default validation plugin, then check for enabled alternate plugin.
    $validation_plugin = $this->tfaSettings->get('default_validation_plugin');

    if ($alternate_plugin && !empty($validation_plugin_definitions[$alternate_plugin]) && !empty($user_enabled_validation_plugins[$alternate_plugin])) {
      $validation_plugin = $alternate_plugin;
      $form['#cache'] = ['max-age' => 0];
    }

    // Get current validation plugin form.
    $this->tfaValidationPlugin = $this->tfaValidationManager->createInstance($validation_plugin, ['uid' => $user->id()]);
    $form = $this->tfaValidationPlugin->getForm($form, $form_state);

    $this->tfaLoginPlugins = $this->tfaLoginManager->getPlugins(['uid' => $user->id()]);
    if ($this->tfaLoginPlugins) {
      foreach ($this->tfaLoginPlugins as $login_plugin) {
        if (method_exists($login_plugin, 'getForm')) {
          $form = $login_plugin->getForm($form, $form_state);
        }
      }
    }

    $form['account'] = [
      '#type' => 'value',
      '#value' => $user,
    ];

    // Build a list of links for using other enabled validation methods.
    $other_validation_plugin_links = [];
    foreach ($user_enabled_validation_plugins as $user_enabled_validation_plugin) {
      // Do not show the current plugin.
      if ($validation_plugin == $user_enabled_validation_plugin) {
        continue;
      }
      // Do not show plugins without labels.
      if (empty($validation_plugin_definitions[$user_enabled_validation_plugin]['label'])) {
        continue;
      }

      $other_validation_plugin_links[$user_enabled_validation_plugin] = [
        'title' => $validation_plugin_definitions[$user_enabled_validation_plugin]['label'],
        'url' => Url::fromRoute('tfa.entry', [
          'user' => $user->id(),
          'hash' => $this->getRequest()->get('hash'),
          'plugin' => $user_enabled_validation_plugin,
        ]),
      ];
    }
    // Show other enabled and configured validation plugins.
    $form['validation_plugin'] = [
      '#type' => 'value',
      '#value' => $validation_plugin,
    ];

    // Don't show an empty fieldset when no other tfa methods are available.
    if (!empty($other_validation_plugin_links)) {
      $form['change_validation_plugin'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Having Trouble?'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        'content' => [
          'help' => [
            '#markup' => $this->t('Try one of your other enabled validation methods.'),
          ],
          'other_validation_plugins' => [
            '#theme' => 'links',
            '#links' => $other_validation_plugin_links,
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $window = ($this->tfaSettings->get('tfa_flood_window')) ?: 300;
    $threshold = ($this->tfaSettings->get('tfa_flood_threshold')) ?: 6;

    if ($this->tfaSettings->get('tfa_flood_uid_only')) {
      // Register flood events based on the uid only, so they apply for any
      // IP address. This is the most secure option.
      $this->floodIdentifier = $values['account']->id();
    }
    else {
      // The default identifier is a combination of uid and IP address. This
      // is less secure but more resistant to denial-of-service attacks that
      // could lock out all users with public user names.
      $this->floodIdentifier = $values['account']->id() . '-' . $this->getRequest()->getClientIP();
    }

    // Flood control.
    if (!$this->flood->isAllowed('tfa.failed_validation', $threshold, $window, $this->floodIdentifier)) {
      $form_state->setErrorByName('', $this->t('Failed validation limit reached. %limit wrong codes in @interval. Try again later.', [
        '%limit' => $threshold,
        '@interval' => $this->dateFormatter->formatInterval($window),
      ]));
      return;
    }

    // Star validation.
    $validated = $this->tfaValidationPlugin->validateForm($form, $form_state);
    $fallbacks = $this->tfaSettings->get('fallback_plugins');
    $auth_success = true;
    if (!$validated) {
      $auth_success = false;
      $form_state->clearErrors();
      $errors = $this->tfaValidationPlugin->getErrorMessages();
      $form_state->setErrorByName(key($errors), current($errors));
      if(isset($fallbacks[$this->tfaSettings->get('default_validation_plugin')])) {
        foreach ($fallbacks[$this->tfaSettings->get('default_validation_plugin')] as $fallback => $val) {
          $fallback_plugin = $this->tfaValidationManager->createInstance($fallback, ['uid' => $values['account']->id()]);
          if (!$fallback_plugin->validateForm($form, $form_state)) {
            $errors = $fallback_plugin->getErrorMessages();
            $form_state->setErrorByName(key($errors), current($errors));
          }
          else {
            $auth_success = true;
            $form_state->clearErrors();
            break;
          }
        }
      }

      // Register failed login in flood controls.
      if (!$auth_success)
        $this->flood->register('tfa.failed_validation', $this->tfaSettings->get('tfa_flood_window'), $this->floodIdentifier);
    }
  }

  /**
   * For the time being, assume there is no fallback options available.
   * If the form is submitted and passes validation, the user should be able
   * to log in.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = $form_state->getValue('account');
    // TODO This could be improved with EventDispatcher.
    if (!empty($this->tfaLoginPlugins)) {
      foreach ($this->tfaLoginPlugins as $plugin) {
        if (method_exists($plugin, 'submitForm')) {
          $plugin->submitForm($form, $form_state);
        }
      }
    }

    user_login_finalize($user);

    // TODO Should finalize() be after user_login_finalize or before?!
    // TODO This could be improved with EventDispatcher.
    $this->finalize();
    $this->flood->clear('tfa.failed_validation', $this->floodIdentifier);
    $form_state->setRedirect('<front>');
  }

  /**
   * Run TFA process finalization.
   */
  public function finalize() {
    // Invoke plugin finalize.
    if (method_exists($this->tfaValidationPlugin, 'finalize')) {
      $this->tfaValidationPlugin->finalize();
    }
    // Allow login plugins to act during finalization.
    if (!empty($this->tfaLoginPlugins)) {
      foreach ($this->tfaLoginPlugins as $plugin) {
        if (method_exists($plugin, 'finalize')) {
          $plugin->finalize();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['tfa.settings'];
  }

}
