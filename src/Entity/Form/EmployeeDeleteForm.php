<?php
namespace Drupal\office\Entity\Form;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
class EmployeeDeleteForm extends ContentEntityConfirmFormBase {
	public function getQuestion() {
		return t('Are you sure you want to delete entity %name?', array('%name' => $this->entity->label()));
	}
	public function getCancelUrl() {
		return new Url('entity.employee.collection');
	}
	public function getConfirmText() {
		return t('Delete');
	}
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$this->entity->delete();
		drupal_set_message(
			$this->t('content @type: deleted @label.',
				[
					'@type' => $this->entity->bundle(),
					'@label' => $this->entity->label()
				]
			)
		);
		$form_state->setRedirectUrl($this->getCancelUrl());
	}
}
