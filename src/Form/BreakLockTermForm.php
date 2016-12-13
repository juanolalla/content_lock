<?php

namespace Drupal\content_lock\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;

/**
 * Class BreakLockTermForm.
 *
 * @package Drupal\content_lock\Form
 */
class BreakLockTermForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'break_lock_term';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Term $taxonomy_term = NULL) {
    $form['#title'] = t('Break Lock for Taxonomy Term @label', ['@label' => $taxonomy_term->label()]);
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#value' => $taxonomy_term->id(),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Confirm break lock'),
    ];
    $input = $form_state->getUserInput();
    $referrer = (isset($input['referrer']) && !empty($input['referrer'])) ? $input['referrer'] : $_SERVER['HTTP_REFERER'];
    $form['referrer'] = [
      '#type' => 'hidden',
      '#value' => $referrer,
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
    $lock_service->release($entity_id, NULL, 'taxonomy_term');
    drupal_set_message($this->t('Lock broken. You can now edit this content.'));
    if ($referrer = $form_state->getValue('referrer')) {
      $url = Url::fromUri($referrer);
      $form_state->setRedirectUrl($url);
    }
    else {
      $this->redirect('entity.taxonomy_term.edit_form', array('taxonomy_term' => $entity_id))->send();
    }
  }

}
