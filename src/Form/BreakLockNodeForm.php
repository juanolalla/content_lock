<?php

namespace Drupal\content_lock\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Class BreakLockNodeForm.
 *
 * @package Drupal\content_lock\Form
 */
class BreakLockNodeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'break_lock_node';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {
    $form['#title'] = t('Break Lock for content @label', ['@label' => $node->label()]);
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
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
    $lock_service->release($entity_id, NULL, 'node');
    drupal_set_message($this->t('Lock broken. Anyone can now edit this content.'));

    // Redirect URL to the request destination or the canonical node view.
    if ($destination = \Drupal::request()->query->get('destination')) {
      $url = Url::fromUserInput($destination);
      $form_state->setRedirectUrl($url);
    }
    else {
      $this->redirect('entity.node.canonical', array('node' => $entity_id))->send();
    }
  }

}
