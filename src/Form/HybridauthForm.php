<?php

namespace Drupal\custom_social_login\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Url;

/**
 * Class HybridauthForm.
 *
 * @package Drupal\custom_social_login\Form
 */
class HybridauthForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_social_login.hybridauth',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hybridauth_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('custom_social_login.hybridauth');

    // get site url
    $url = Url::fromUri('internal:/', array('absolute' => TRUE));
    $site_url = $url->toString();
    $endpoint = $site_url . 'hybridauth/endpoint';
    $config->set('base_url', $endpoint)->save();
    $site_uri_parts = parse_url($site_url);

    $form['providers'] = array(
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-google',
    );

    // Google
    $form['google'] = array(
      '#type' => 'details',
      '#title' => $this->t('Google'),
      '#group' => 'providers',
    );
    $form['google']['google_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('providers.Google.keys.id'),
    );
    $form['google']['google_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('providers.Google.keys.secret'),
    );
    $form['google']['#description'] = t('<p>Enter your Client ID and Client secret. You can get these by creating a new application at <a href="@app_uri">@app_uri</a>.</p>'
      . '<p>You must set <strong>Authorized Redirect URIs</strong> to <strong>%redirect_uri</strong>.</p>'
      . '<p>You must set <strong>Authorized JavaScript Origins</strong> to <strong>%origin_uri</strong>.</p>'
      . '<p>You must enable <strong>Contacts API</strong> and <strong>Google+ API</strong> for your project at <a href="@app_uri">@app_uri</a> under APIs & auth -> APIs.</p>',
      array(
        '@app_uri' => 'https://console.developers.google.com',
        '%redirect_uri' => Url::fromUri($endpoint, array('absolute' => TRUE, 'query' => array('hauth.done' => 'Google')))->toString(),
        '%origin_uri' => $site_uri_parts['scheme'] . '://' . $site_uri_parts['host'],
      )
    );

    // Facebook
    $form['facebook'] = array(
      '#type' => 'details',
      '#title' => $this->t('Facebook'),
      '#group' => 'providers',
    );
    $form['facebook']['facebook_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('providers.Facebook.keys.id'),
    );
    $form['facebook']['facebook_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App Secret'),
      '#default_value' => $config->get('providers.Facebook.keys.secret'),
    );
    $form['facebook']['#description'] = t('<p>Enter your application ID and private key. You can get these by creating a new application at <a href="@app_uri">@app_uri</a>.</p>'
    . '<p>You must set <strong>App Domain</strong> to something like <strong>example.com</strong> to cover <strong>*.example.com</strong>.</p>'
    . '<p>You must set <strong>Site URL</strong> to <strong>%site_uri</strong>.</p>',
      array(
        '@app_uri' => 'https://developers.facebook.com/apps',
        '%site_uri' => $site_url,
      )
    );

    // Twitter
    $form['twitter'] = array(
      '#type' => 'details',
      '#title' => $this->t('Twitter'),
      '#group' => 'providers',
    );
    $form['twitter']['twitter_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('providers.Twitter.keys.key'),
    );
    $form['twitter']['twitter_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App Secret'),
      '#default_value' => $config->get('providers.Twitter.keys.secret'),
    );
    $form['twitter']['#description'] = t('<p>Enter your consumer key and private key. You can get these by creating a new application at <a href="@app_uri">@app_uri</a>.</p>'
    . '<p>You must set <strong>Call back URL</strong> to <strong>%redirect_uri</strong>.</p>',
      array(
        '@app_uri' => 'https://dev.twitter.com/apps',
        '%redirect_uri' => Url::fromUri($endpoint, array('absolute' => TRUE, 'query' => array('hauth.done' => 'Twitter')))->toString(),
      )
    );
    return parent::buildForm($form, $form_state);
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
    parent::submitForm($form, $form_state);
    $config = \Drupal::service('config.factory')->getEditable('custom_social_login.hybridauth');
    $google_key = $form_state->getValue('google_key');
    $google_secret = $form_state->getValue('google_secret');
    $facebook_key = $form_state->getValue('facebook_key');
    $facebook_secret = $form_state->getValue('facebook_secret');
    $twitter_key = $form_state->getValue('twitter_key');
    $twitter_secret = $form_state->getValue('twitter_secret');
    $config->set('providers.Google.keys.id', $google_key)
           ->set('providers.Google.keys.secret', $google_secret)
           ->set('providers.Facebook.keys.id', $facebook_key)
           ->set('providers.Facebook.keys.secret', $facebook_secret);
           ->set('providers.Twitter.keys.key', $twitter_key)
           ->set('providers.Twitter.keys.secret', $twitter_secret);
    $config->save();
  }
}
