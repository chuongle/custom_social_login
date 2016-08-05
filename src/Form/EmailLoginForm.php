<?php

namespace Drupal\custom_social_login\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\DataCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Class EmailLoginForm.
 *
 * @package Drupal\custom_social_login\Form
 */
class EmailLoginForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
    '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Sign in with email and password'),
    ];

    $form['email'] = [
      '#type' => 'email',
      '#attributes' => array(
        'class' => ['login-input',  'w-input'],
        'id' => 'email-signin',
        'placeholder' => 'Email address'
      ),
    ];
    $form['password'] = [
      '#type' => 'password',
      '#maxlength' => 64,
      '#size' => 64,
      '#attributes' => array(
        'class' => ['login-input',  'w-input'],
        'id' => 'password-signin',
        'placeholder' => 'Password'
      ),
      '#suffix' => '<span class="email-valid-message"></span>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#ajax' => array(
        'callback' => array($this, 'validateLogin'),
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ),
      '#attributes' => array(
        'class' => ['btn-login', 'w-button'],
      ),
    ];

    return $form;
  }

  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
    * {@inheritdoc}
    */
  public function validateLogin(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($user = user_load_by_mail($form_state->getValue('email'))) {
      if(\Drupal::service('user.auth')->authenticate($user->getAccountName(), $form_state->getValue('password'))) {
        user_login_finalize($user);
        $current_path = \Drupal::service('path.current')->getPath();
        $response->addCommand(new RedirectCommand($current_path));
        return $response;
      }
    }
    $css = ['border' => '1px solid red'];
    $message = $this->t('Incorrect Email or Password. Please try again.');
    $response->addCommand(new CssCommand('#password-signin', $css));
    $response->addCommand(new CssCommand('#email-signin', $css));
    $response->addCommand(new HtmlCommand('.email-valid-message', $message));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
  }
}
