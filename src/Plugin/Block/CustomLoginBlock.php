<?php

namespace Drupal\custom_social_login\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'CustomLoginBlock' block.
 *
 * @Block(
 *  id = "custom_login_block",
 *  admin_label = @Translation("Custom login block"),
 * )
 */
class CustomLoginBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $email_login = \Drupal::formBuilder()->getForm('Drupal\custom_social_login\Form\EmailLoginForm');
    $create_account = \Drupal::formBuilder()->getForm('Drupal\custom_social_login\Form\CreateAccountForm');

  	return [
      '#theme' => 'login',
  	  '#attached' => array(
        'library' =>  array(      
          'custom_social_login/imperial',
        ),
      ),
      '#login_form' => drupal_render($email_login),
      '#create_account' => drupal_render($create_account),
    ];
  }

}
