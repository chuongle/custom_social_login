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
 * Class UpdateDisplayNameAndPasswordForm.
 *
 * @package Drupal\custom_social_login\Form
 */
class UpdateDisplayNameAndPasswordForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_display_name_and_password_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['password'] = [
      '#type' => 'password',
      '#maxlength' => 64,
      '#size' => 64,
      '#attributes' => array(
        'class' => ['login-input',  'w-input'],
        'id' => 'password-create',
        'placeholder' => 'Password'
      ),
    ];
    $form['password_confirm'] = [
      '#type' => 'password',
      '#maxlength' => 64,
      '#size' => 64,
      '#attributes' => array(
        'class' => ['login-input',  'w-input'],
        'id' => 'password-create-confirm',
        'placeholder' => 'Confirm Password'
      ),
      '#suffix' => '<span class="email-valid-message"></span>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#ajax' => array(
        'callback' => array($this, 'validateConfirmPassword'),
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

  public function validateConfirmPassword(array &$form, FormStateInterface $form_state) {
    $password = $form_state->getValue('password');
    $password_confirm = $form_state->getValue('password_confirm');
    $response = new AjaxResponse();
    if($password === $password_confirm) {
      $current_user = \Drupal::currentUser();
      $uid = $current_user->id();
      $user = user_load($uid);
      $user->set('pass', $password);
      $user->save();
      $message = '<p>Password saved.</p><a class="btn-login w-inline-block" data-ix="modal-login-hide" href="#"><div>OK</div></a>';
      $response->addCommand(new HtmlCommand('.email-valid-message', $message));
      $response->addCommand(new ReplaceCommand('#update-display-name-and-password-form', $message));
    }else {
      $css = ['border' => '1px solid red'];
      $message = $this->t('Password does not match. Please try again.');
      $response->addCommand(new CssCommand('#password-create-confirm', $css));
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
