<?php

namespace Drupal\tfa\Form;

use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Drupal\tfa\Plugin\TfaSendInterface;
use Drupal\tfa\TfaContext;
use Drupal\tfa\TfaDataTrait;
use Drupal\tfa\TfaLoginTrait;
use Drupal\tfa\TfaLoginPluginManager;
use Drupal\tfa\TfaValidationPluginManager;
use Drupal\user\Form\UserLoginForm;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserDataInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * TFA user login form.
 */
class TfaLoginForm extends UserLoginForm {
  use TfaDataTrait;
  use TfaLoginTrait;

  /**
   * The validation plugin manager to fetch plugin information.
   *
   * @var \Drupal\tfa\TfaValidationPluginManager
   */
  protected $tfaValidationManager;

  /**
   * The login plugin manager to fetch plugin information.
   *
   * @var \Drupal\tfa\TfaLoginPluginManager
   */
  protected $tfaLoginManager;

  /**
   * The current validation plugin.
   *
   * @var \Drupal\tfa\Plugin\TfaValidationInterface
   */
  protected $tfaValidationPlugin;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * Redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $destination;

  /**
   * Current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Tfa login context object.
   *
   * This will be initialized in the submitForm() method.
   *
   * @var \Drupal\tfa\TfaContext
   */
  protected $tfaContext;

  /**
   * Constructs a new user login form.
   *
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The user authentication object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\tfa\TfaValidationPluginManager $tfa_validation_manager
   *   Tfa validation plugin manager.
   * @param \Drupal\tfa\TfaLoginPluginManager $tfa_plugin_manager
   *   Tfa setup plugin manager.
   * @param \Drupal\user\UserDataInterface $user_data
   *   User data service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $destination
   *   Redirect destination.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request stack for getting current request.
   */
  public function __construct(FloodInterface $flood, UserStorageInterface $user_storage, UserAuthInterface $user_auth, RendererInterface $renderer, TfaValidationPluginManager $tfa_validation_manager, TfaLoginPluginManager $tfa_plugin_manager, UserDataInterface $user_data, RedirectDestinationInterface $destination, Request $request) {
    parent::__construct($flood, $user_storage, $user_auth, $renderer);
    $this->tfaValidationManager = $tfa_validation_manager;
    $this->tfaLoginManager = $tfa_plugin_manager;
    $this->userData = $user_data;
    $this->destination = $destination;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flood'),
      $container->get('entity.manager')->getStorage('user'),
      $container->get('user.auth'),
      $container->get('renderer'),
      $container->get('plugin.manager.tfa.validation'),
      $container->get('plugin.manager.tfa.login'),
      $container->get('user.data'),
      $container->get('redirect.destination'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#submit'][] = '::tfaLoginFormRedirect';
    $form['#cache'] = ['max-age' => 0];

    return $form;
  }

  /**
   * Login submit handler.
   *
   * Determine if TFA process applies. If not, call the parent form submit.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Similar to tfa_user_login() but not required to force user logout.
    $user = $this->userStorage->load($form_state->get('uid'));
    $this->tfaContext = new TfaContext(
      $this->tfaValidationManager,
      $this->tfaLoginManager,
      $this->configFactory(),
      $user,
      $this->userData,
      $this->getRequest()
    );

    // Uncomment when things go wrong and you get logged out.
    // user_login_finalize($user);
    // $form_state->setRedirect('<front>');
    // return;

    // Stop processing if Tfa is not enabled.
    if (!$this->tfaContext->isModuleSetup() || !$this->tfaContext->isTfaRequired()) {
      return parent::submitForm($form, $form_state);
    }

    // Setup TFA.
    if ($this->tfaContext->isReady()) {
      $this->loginWithTfa($form_state);
    }
    else {
      $this->loginWithoutTfa($form_state);
    }
  }

  /**
   * Handle login when TFA is set up for the user.
   *
   * TFA is set up for this user, and $this->tfaContext is initialized.
   *
   * If any of the TFA plugins allows login, then finalize the login. Otherwise,
   * set a redirect to enter a second factor.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the login form.
   */
  public function loginWithTfa(FormStateInterface $form_state) {
    $user = $this->tfaContext->getUser();
    if ($this->tfaContext->pluginAllowsLogin()) {
      $this->tfaContext->doUserLogin();
      drupal_set_message('You have logged in on a trusted browser.');
      $form_state->setRedirect('<front>');
    }
    else {
      // Begin TFA and set process context.
      // @todo This is used in send plugins which has not been implemented yet.
      // $this->begin($tfaValidationPlugin);
      $parameters = $this->destination->getAsArray();
      $parameters['user'] = $user->id();
      $parameters['hash'] = $this->getLoginHash($user);
      $this->request->query->remove('destination');
      $form_state->setRedirect('tfa.entry', $parameters);
    }
  }

  /**
   * Handle the case where TFA is not yet set up.
   *
   * TFA is not set up for this user, and $this->tfaContext is initialized.
   *
   * If the user has any remaining logins, then finalize the login with a
   * message to set up TFA. Otherwise, leave the user logged out.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the login form.
   */
  public function loginWithoutTfa(FormStateInterface $form_state) {
    // User may be able to skip TFA, depending on module settings and number of
    // prior attempts.
    $remaining = $this->tfaContext->remainingSkips();
    if ($remaining) {
      $user = $this->tfaContext->getUser();
      $tfa_setup_link = Url::fromRoute('tfa.overview', [
        'user' => $user->id(),
      ])->toString();
      drupal_set_message($this->t('You are required to setup two-factor authentication <a href="@link">here.</a> You have @remaining attempts left after this you will be unable to login.', [
        '@remaining' => $remaining - 1,
        '@link' => $tfa_setup_link,
      ]), 'error');
      $this->tfaContext->hasSkipped();
      $this->tfaContext->doUserLogin();
      $form_state->setRedirect('<front>');
    }
  }

  /**
   * Login submit handler for TFA form redirection.
   *
   * Should be last invoked form submit handler for forms user_login and
   * user_login_block so that when the TFA process is applied the user will be
   * sent to the TFA form.
   *
   * @param array $form
   *   The current form api array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function tfaLoginFormRedirect(array $form, FormStateInterface $form_state) {
    $route = $form_state->getValue('tfa_redirect');
    if (isset($route)) {
      $form_state->setRedirect($route);
    }
  }

  /**
   * Begin the TFA process.
   *
   * @param \Drupal\tfa\Plugin\TfaSendInterface $tfaSendPlugin
   *   The send plugin instance.
   */
  protected function begin(TfaSendInterface $tfaSendPlugin) {
    // Invoke begin method on send validation plugins.
    if (method_exists($tfaSendPlugin, 'begin')) {
      $tfaSendPlugin->begin();
    }
  }

}
