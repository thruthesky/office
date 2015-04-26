<?php

/**
 * @file
 * Contains Drupal\office\Entity\Form\GroupForm.
 */

namespace Drupal\office\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;

/**
 * Form controller for the Group entity edit forms.
 *
 * @ingroup office
 */
class GroupForm extends ContentEntityForm {
	/**
	 * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		/* @var $entity \Drupal\office\Entity\Group */
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
		$id = $this->entity->id();
		$name = $form_state->getValue('name');
		$name = $name[0]['value'];
		$groups = \Drupal::entityManager()->getStorage('group')->loadByProperties(['name'=>$name]);
		if ($groups ) {
			$group = reset($groups);
			if ( $group->id() == $id ) return; // 그룹을 수정하는 경우, 그룹 이름이 같은 것이 하나 존재 함.
			// 그룹을 수정하는데, 같은 이름의 다른 그룹이 존재하거나,
			// 추가를 하는데, 같은 이름의 그룹이 이미 존재하는 경우,
			$form_state->setErrorByName(
				'name',
				$this->t("ERROR: The group name is already exists. Please choose another")
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
			drupal_set_message($this->t('Saved the %label Group.', array(
				'%label' => $entity->label(),
			)));
		}
		else {
			drupal_set_message($this->t('The %label Group was not saved.', array(
				'%label' => $entity->label(),
			)));
		}
		$form_state->setRedirect('entity.group.edit_form', ['group' => $entity->id()]);
	}

}
