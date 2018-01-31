<?php

namespace Drupal\ebsco\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a 'EBSCO' Block.
 *
 * @Block(
 *   id = "ebsco_block",
 *   admin_label = @Translation("EBSCO Discovery Service"),
 *   category = @Translation("EBSCO Discovery Service"),
 * )
 */
class EbscoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#theme' => "ebsco_basic_search",
    );
  }

}
