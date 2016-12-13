<?php

namespace Drupal\content_lock\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\block_content\Entity\BlockContent;

/**
 * Class BreakLockBlockContentForm.
 *
 * @package Drupal\content_lock\Form
 */
class BreakLockBlockContentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'break_lock_block_content';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BlockContent $block_content = NULL) {
    $form['#title'] = t('Break Lock for Block @label', ['@label' => $block_content->label()]);
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#value' => $block_content->id(),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Confirm break lock'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_id = $form_state->getValue('entity_id');
    /** @var \Drupal\content_lock\ContentLock\ContentLock $lock_service */
    $lock_service = \Drupal::service('content_lock');
    $lock_service->release($entity_id, NULL, 'block_content');
    drupal_set_message($this->t('Lock broken. Anyone can now edit this content.'));

    // Redirect URL to the request destination or the canonical taxonomy term view.
    if ($destination = \Drupal::request()->query->get('destination')) {
      $url = Url::fromUserInput($destination);
      $form_state->setRedirectUrl($url);
    }
    else {
      $this->redirect('entity.block_content.canonical', array('block_content' => $entity_id))->send();
    }
  }

}
