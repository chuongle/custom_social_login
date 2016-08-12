<?php

namespace Drupal\custom_social_login\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\user\Entity\User;

/**
 * Class SetPasswordForm.
 *
 * @package Drupal\custom_social_login\Form
 */
class SetPasswordForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'set_password_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => $this->t('Set your password'),
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => t('Type a password *'),
      '#maxlength' => 64,
      '#size' => 64,
      '#attributes' => array(
        'class' => ['login-input',  'w-input'],
        'id' => 'password-create',
        'placeholder' => 'Type a password'
      ),
      '#required' => TRUE,
    ];
    $form['password_confirm'] = [
      '#type' => 'password',
      '#title' => t('Confirm your password *'),
      '#maxlength' => 64,
      '#size' => 64,
      '#attributes' => array(
        'class' => ['login-input',  'w-input'],
        'id' => 'password-create-confirm',
        'placeholder' => 'Confirm your password'
      ),
      '#required' => TRUE,
      '#suffix' => '<p class="txt-error"></p>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Set my password'),
      '#attributes' => array(
        'class' => ['btn-login', 'w-button'],
      ),
      '#ajax' => array(
        'callback' => array($this, 'validateConfirmPassword'),
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ),
    ];
    
    return $form;
  }

  public function validateConfirmPassword(array &$form, FormStateInterface $form_state) {
    $password = $form_state->getValue('password');
    $password_confirm = $form_state->getValue('password_confirm');
    $response = new AjaxResponse();
    $active_theme = \Drupal::theme()->getActiveTheme()->getName();
    $theme_path = drupal_get_path('theme', $active_theme);
    if($password === $password_confirm) {
      $current_user = \Drupal::currentUser();
      $uid = $current_user->id();
      $user = user_load($uid);
      $user->set('pass', $password);
      $user->save();
      $message = '
        <h3>Welcome to the new Imperial Sugar Website!</h3>
        <p>You can find your account settings and profile info in the top-right corner of the website.</p>
        <img src="/'. $theme_path .'/images/welcome-modal-account-settings.png">
        <a class="btn-login w-inline-block" href="/user/edit-profile"><div>Update my profile now →</div></a>';
      $response->addCommand(new ReplaceCommand('#set-password-form', $message));
      $response->addCommand(new HtmlCommand('.modal-reset-pass-hide', 'I\'ll update it later…'));
    }else {
      $css = ['border' => '1px solid red'];
      $message = $this->t('Passwords don\'t match :(');
      $response->addCommand(new CssCommand('#password-create-confirm', $css));
      $response->addCommand(new HtmlCommand('.txt-error', $message));
    }
    return $response;
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
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
