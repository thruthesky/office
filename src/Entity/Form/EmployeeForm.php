<?php
namespace Drupal\office\Entity\Form;
use Drupal\office;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\office\Entity\Employee;

class EmployeeForm extends ContentEntityForm {
	/**
	 * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		/* @var $entity \Drupal\office\Entity\Employee */
		$form = parent::buildForm($form, $form_state);
		$entity = $this->entity;
		$form['langcode'] = array(
			'#title' => t('Language'),
			'#type' => 'language_select',
			'#default_value' => $entity->getUntranslated()->language()->getId(),
			'#languages' => Language::STATE_ALL,
		);
		return $form;
	}
	public function validateForm(array &$form, FormStateInterface $form_state) {
		$val = $form_state->getValue('user_id');
		$user_id = $val[0]['target_id'];
		$employees = \Drupal::entityManager()->getStorage('employee')->loadByProperties(['user_id'=>$user_id]);
		if ( $employees ) {
			$form_state->setErrorByName(
				'user_id',
				$this->t("ERROR: The user is already registered as Employee.")
			);
		}
	}

	/**
	 * Overrides \Drupal\Core\Entity\EntityFormController::submit().
	 */
	public function submit(array $form, FormStateInterface $form_state) {
		// Build the entity object from the submitted values.
		$entity = parent::submit($form, $form_state);

		return $entity;
	}
	/**
	 * Overrides Drupal\Core\Entity\EntityFormController::save().
	 */
	public function save(array $form, FormStateInterface $form_state) {
		$entity = $this->entity;
		$status = $entity->save();

		if ($status) {
			drupal_set_message($this->t('Saved the %label Employee.', array(
				'%label' => $entity->label(),
			)));
		}
		else {
			drupal_set_message($this->t('The %label Employee was not saved.', array(
				'%label' => $entity->label(),
			)));
		}
		$form_state->setRedirect('entity.employee.edit_form', ['employee' => $entity->id()]);
	}
}
