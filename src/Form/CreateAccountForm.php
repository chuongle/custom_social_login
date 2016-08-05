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
 * Class CreateAccountForm.
 *
 * @package Drupal\custom_social_login\Form
 */
class CreateAccountForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_account_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'textfield',
      '#attributes' => array(
        'class' => ['login-input',  'w-input'],
        'id' => 'email-create-account',
        'placeholder' => 'Email address'
      ),
      '#suffix' => '<span class="email-valid-message"></span>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Send verification email'),
      '#ajax' => array(
        'callback' => array($this, 'validateEmailAjax'),
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ),
      '#attributes' => array(
        'class' => ['btn-login', 'em', 'w-button'],
      ),
    ];

    return $form;
  }
  
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $email = $form_state->getValue('email');
    $domain_negotiator = \Drupal::service('domain.negotiator');
    if(\Drupal::service('email.validator')->isValid($email)) {
      if($user = user_load_by_mail($email)) {
        // email already exists
        // 1. send a link to reset your pass, or 2. click here to login
        $message = 'This email is already existed. Do you want to <strong>reset your password</strong> or login with your existing password?';
        $response->addCommand(new HtmlCommand('.email-valid-message', $message));
      }else {
        $user = User::create([
          'name' => $email,
          'pass' => user_password(),
          'mail' => $email,
          'status' => 1,
          'init' => $email,
          'roles' => ['authenticated'],
          'field_domain_access' => array('target_id' => $domain_negotiator->getActiveId()),
        ]);
        $user->save();
        _user_mail_notify('register_no_approval_required', $user);
        $message = '<p>We sent a verification link to your email. Please check your inbox.</p><a class="btn-login w-inline-block" data-ix="modal-login-hide" href="#"><div>OK</div></a>';
        $response->addCommand(new ReplaceCommand('#create-account-form', $message));
      }
    }else {
      // invalid email address
      $css = ['border' => '1px solid red'];
      $message = $this->t('Invalid email address. Please try again.');
      $response->addCommand(new CssCommand('#email-create-account', $css));
      $response->addCommand(new HtmlCommand('.email-valid-message', $message));
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
