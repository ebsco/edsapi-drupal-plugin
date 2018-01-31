<?php

namespace Drupal\ebsco\Plugin\eds;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Example: configurable text string' block.
 *
 * Drupal\Core\Block\BlockBase gives us a very useful set of basic functionality
 * for this configurable block. We can just fill in a few of the blanks with
 * defaultConfiguration(), blockForm(), blockSubmit(), and build().
 *
 * @Block(
 *   id = "example_configurable_text",
 *   admin_label = @Translation("Title of first block (example_configurable_text)")
 * )
 */
class ebscoEDSForm extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'block_example_string' => $this->t('A default value. This block was created at %time', array('%time' => date('c'))),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function edsForm($form, FormStateInterface $form_state) {
    $form['block_example_string_text'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Block contents'),
      '#description' => $this->t('This text will appear in the example block.'),
      '#default_value' => $this->configuration['block_example_string'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function edsSubmit($form, FormStateInterface $form_state) {
    $this->configuration['block_example_string']
      = $form_state->getValue('block_example_string_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => $this->configuration['block_example_string'],
    );
  }

}