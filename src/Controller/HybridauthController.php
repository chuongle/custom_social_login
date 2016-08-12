<?php

namespace Drupal\custom_social_login\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\custom_social_login\HybridauthInstance;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class HybridauthController.
 *
 * @package Drupal\custom_social_login\Controller
 */
class HybridauthController extends ControllerBase {
  /**
   * Router.
   * This is where we handle redirect logic
   *
   */
  public function router() {
    $uid = $_GET['uid'];
    $user = user_load($uid);
    if($user->get('access')->value == 0) {
      user_login_finalize($user);
      if($_COOKIE['current_page']) {
        return new RedirectResponse($_COOKIE['current_page'].'?setpassword=true');
      }
      return new RedirectResponse('/login?setpassword=true');
    }
  }

  /**
   * Endpoint.
   *
   *
   */  
  public function endpoint() {
    custom_social_login_hybridauth_session_start();
    require_once drupal_get_path('module', 'custom_social_login') . '/vendor/hybridauth/hybridauth/hybridauth/index.php';
  }

  /**
   * Process Auth.
   *
   * @param $identifier
   *
   */
  public function processAuth($provider) {
    if($provider == 'email') {
      $request = \Drupal::request();
      if($query = $request->getQueryString()) {
        $uid = $_GET['uid'];
        if($user = user_load($uid)) {
          $timestamp = $_GET['time'];
          $current = REQUEST_TIME;
          if($current - $timestamp > 86400) {
            user_login_finalize($user);
            return new RedirectResponse(\Drupal::url('user.page'));
          }else {
            return new RedirectResponse('/router?uid='.$uid);
          }
        }
      }
    }else {
      custom_social_login_hybridauth_session_start();
      $hybridauth = HybridauthInstance::getHybridauthInstance();

      $error = NULL;
      $user_profile = NULL;
      try {
        $authentication = $hybridauth->authenticate(ucwords($provider));
        $user_profile = (array) ($authentication->getUserProfile());
        $user_profile['provider'] = $provider;
      }
      catch(Exception $e) {
        \Drupal::logger('custom_social_login')->error('Error when requesting User Profile from ' . $provider);
        $error = $e->getCode();
      }
      
      if (!is_null($error)) {
        $this->handleError($error); 
      } else {
        $this->startProcessAuth($user_profile);
        drupal_set_message('User is login');
      }

      return new RedirectResponse('/router');
    }
  }

  public function handleError($error) {
    if (\Drupal::currentUser()->isAnonymous()) {
      // Delete session only if it contains just HybridAuth data.
      $delete_session = TRUE;
      foreach ($_SESSION as $key => $value) {
        if (substr($key, 0, 4) != 'HA::') {
          $delete_session = FALSE;
        }
      }
      if ($delete_session) {
        session_destroy();
      }
    }
    switch ($error) {
      case 5:
        // Authentication failed. The user has canceled the authentication or
        // the provider refused the connection.
        break;
      case 0:
        // Unspecified error.
      case 1:
        // Hybridauth configuration error.
      case 2:
        // Provider not properly configured.
      case 3:
        // Unknown or disabled provider.
      case 4:
        // Missing provider application credentials (your application id, key
        // or secret).
      case 6:
        // User profile request failed.
      case 7:
        // User not connected to the provider.
      case 8:
        // Provider does not support this feature.
      default:
        // Report the error - this message is not shown to anonymous users as
        // we destroy the session - see below.
        drupal_set_message(t('There was an error processing your request.'), 'error');
    }
    throw new ServiceUnavailableHttpException();
  }
  public function startProcessAuth($data) {
    $user = \Drupal::currentUser();

    // // Check if user is already logged in, tries to add new identity
    if(\Drupal::currentUser()->isAuthenticated()) {
      // Identity is already registered.
      $identity = custom_social_login_hybridauth_identity_load($data);
      if ($identity = custom_social_login_hybridauth_identity_load($data)) {
        // Registered to this user.
        if ($user->id() == $identity['uid']) {
          drupal_set_message(t('You have already registered this identity.'));
        }
        // Registered to another user.
        else {
          drupal_set_message(t('This identity is registered to another user.'), 'error');
          // _hybridauth_window_close();
        }
      }
      else {
        custom_social_login_hybridauth_identity_save($data);
        drupal_set_message(t('New identity added.'));
      }
    }

    if ($identity = custom_social_login_hybridauth_identity_load($data)) {
      // Check if user is blocked.
      if ($account = custome_social_login_hybridauth_user_is_blocked_by_uid($identity['uid'])) {
        drupal_set_message(t('The username %name has not been activated or is blocked.', array('%name' => $account->name)), 'error');
      }
      // Check for email verification timestamp.
      elseif (!custom_social_login_hybridauth_user_login_access_by_uid($identity['uid'])) {
        $data = unserialize($identity['data']);
        drupal_set_message(t('You need to verify your e-mail address - !email.', array('!email' => $data['email'])), 'error');
        drupal_set_message(t('A welcome message with further instructions has been sent to your e-mail address.'));
        // _hybridauth_mail_notify('hybridauth_email_verification', user_load($identity['uid']));
      }
      else {
        $user_id = $identity['uid'];
        $user = user_load($user_id);
        user_login_finalize($user);
      }
    }
    elseif($account = user_load_by_mail($data['email'])) {
      if($data['email'] == $data['emailVerified']) {
        custom_social_login_hybridauth_identity_save($data, $account->id());
        drupal_set_message(t('New identity added.'));
        user_login_finalize($account);
      }
    }
    else {
      $user = $this->registerNewUser($data);
      custom_social_login_hybridauth_identity_save($data, $user->id());
      user_login_finalize($user);
    }
  }

  /**
   * Create user account
   * @param array $data
   *
   * @return $user object
   *
   */
  public function registerNewUser($data) {
    $domain_negotiator = \Drupal::service('domain.negatiator');
    $name = custom_social_login_hybridauth_make_username($data);
    $user_info = array(
      'name' => $name,
      'pass' => user_password(),
      'mail' => $data['email'],
      'status' => 1,
      'init' => $data['email'],
      'roles' => ['authenticated'],
      'field_domain_access' => array('target_id' => $domain_negotiator->getActiveId()),
    );
    $user = User::create($user_info);
    $user->enforceIsNew();
    $user->activate();
    $user->save();
    drupal_set_message('User is created');
    return $user;
  }
}
