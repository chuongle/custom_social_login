<?php

namespace Drupal\custom_social_login\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SetPasswordBlock' block.
 *
 * @Block(
 *  id = "set_password_block",
 *  admin_label = @Translation("Set Password block"),
 * )
 */
class SetPasswordBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $set_password = \Drupal::formBuilder()->getForm('Drupal\custom_social_login\Form\SetPasswordForm');

  	return [
      '#theme' => 'setpassword',
      '#set_password_form' => drupal_render($set_password),
    ];
  }

}
