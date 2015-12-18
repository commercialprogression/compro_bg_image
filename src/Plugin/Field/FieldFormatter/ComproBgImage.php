<?php

/**
 * @file
 * Contains \Drupal\compro_bg_image\Plugin\Field\FieldFormatter\ComproBgImage.
 */

namespace Drupal\compro_bg_image\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'compro_bg_image' formatter.
 *
 * @FieldFormatter(
 *   id = "compro_bg_image",
 *   label = @Translation("Background image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ComproBgImage extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'compro_bg_image_style' => '',
      'compro_bg_image_selector' => '',
      'compro_bg_image_repeat' => '',
      'compro_bg_image_position_x' => '',
      'compro_bg_image_position_y' => '',
      'compro_bg_image_size' => '',
      'compro_bg_image_extra_background' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $image_styles = image_style_options();

    $form['compro_bg_image_style'] = array(
      '#type' => 'select',
      '#title' => t('Image Style'),
      '#default_value' => $this->getSetting('compro_bg_image_style'),
      '#options' => $image_styles,
      '#required' => TRUE,
    );

    $form['compro_bg_image_selector'] = array(
      '#type' => 'textfield',
      '#title' => t('Selector'),
      '#default_value' => $this->getSetting('compro_bg_image_selector'),
      '#required' => TRUE,
    );

    $form['compro_bg_image_repeat'] = array(
      '#type' => 'select',
      '#title' => t('Background repeat'),
      '#options' => array(
        'repeat' => t('repeat'),
        'repeat-x' => t('repeat-x'),
        'repeat-y' => t('repeat-y'),
        'space' => t('space'),
        'round' => t('round'),
        'no-repeat' => t('no-repeat'),
      ),
      '#required' => TRUE,
      '#default_value' => $this->getSetting('compro_bg_image_repeat'),
    );

    $form['compro_bg_image_position_x'] = array(
      '#type' => 'textfield',
      '#title' => t('Background x position'),
      '#default_value' => $this->getSetting('compro_bg_image_position_x'),
      '#required' => TRUE,
      '#description' => t('Enter a valid background-position value: top, bottom, left, right, center, 25%, 0px, etc'),
    );

    $form['compro_bg_image_position_y'] = array(
      '#type' => 'textfield',
      '#title' => t('Background y position'),
      '#default_value' => $this->getSetting('compro_bg_image_position_y'),
      '#required' => TRUE,
      '#description' => t('Enter a valid background-position value: top, bottom, left, right, center, 25%, 0px, etc'),
    );

    $form['compro_bg_image_size'] = array(
      '#type' => 'select',
      '#title' => t('Background size'),
      '#options' => array(
        'auto' => t('auto'),
        'cover' => t('cover'),
        'contain' => t('contain'),
      ),
      '#required' => TRUE,
      '#default_value' => $this->getSetting('compro_bg_image_size'),
    );

    $form['compro_bg_image_extra_background'] = array(
      '#type' => 'textfield',
      '#title' => t('Extra background'),
      '#description' => t('Provide an additional background you would like to apply. This will use the shorthand background property so specify your repeats, positions, etc: linear-gradient(to right, rgba(30, 75, 115, 1),  rgba(255, 255, 255, 0)) no-repeat (no semi-colon)'),
      '#default_value' => $this->getSetting('compro_bg_image_extra_background'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $summary[] = t('Image style: @image_style / Selector: @selector',
      array(
        '@image_style' => $this->getSetting('compro_bg_image_style'),
        '@selector' => $this->getSetting('compro_bg_image_selector'),
      )
    );

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    // Loop through items.
    foreach ($items as $delta => $item) {
      // Grab image url.
      $file = File::load($item->getValue()['target_id']);
      $image = ImageStyle::load($this->getSetting('compro_bg_image_style'));
      $image = $image->buildUrl($file->getFileUri());

      // Check for additional backgrounds.
      if ($this->getSetting('compro_bg_image_extra_background')) {
        $background_css = 'background: ' . $this->getSetting('compro_bg_image_extra_background') . ', url(' . $image . ') ' . $this->getSetting('compro_bg_image_repeat') . ' ' . $this->getSetting('compro_bg_image_position_x') . ' ' . $this->getSetting('compro_bg_image_position_y') . '/' . $this->getSetting('compro_bg_image_size') . ';';
      }
      else {
        $background_css = 'background: url(' . $image . ') ' . $this->getSetting('compro_bg_image_repeat') . ' ' . $this->getSetting('compro_bg_image_position_x') . ' ' . $this->getSetting('compro_bg_image_position_y') . '/' . $this->getSetting('compro_bg_image_size') . ';';
      }

      // We can dynamically attach CSS to the html head.
      $elements['#attached']['html_head'][]
        = array(
          // The Inline CSS tag and value.
          array(
            '#tag' => 'style',
            '#value' => ' ' . $this->getSetting('compro_bg_image_selector') . ' { ' . $background_css . ' }',
          ),
        );
    }

    return $elements;
  }

}
