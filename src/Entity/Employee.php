<?php
namespace Drupal\office\Entity;
use Drupal\office\EmployeeInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Employee entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_employee",
 *   label = @Translation("Employee entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\office\Entity\Controller\EmployeeListController",
 *     "form" = {
 *       "default" = "Drupal\office\Entity\Form\EmployeeForm",
 *       "add" = "Drupal\office\Entity\Form\EmployeeForm",
 *       "edit" = "Drupal\office\Entity\Form\EmployeeForm",
 *       "delete" = "Drupal\office\Entity\Form\EmployeeDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer employee",
 *   base_table = "office_employee",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "entity.office_employee.canonical",
 *     "delete-form" = "entity.office_employee.delete_form",
 *     "edit-form" = "entity.office_employee.edit_form",
 *     "collection" = "entity.office_employee.collection"
 *   },
 *   field_ui_base_route = "employee.settings",
 * )
 */
class Employee extends ContentEntityBase implements EmployeeInterface {

	public static function loadByUserID($uid) {
		$employees = \Drupal::entityManager()->getStorage('office_employee')->loadByProperties(['user_id'=>$uid]);
		if ( $employees ) $employee = reset($employees);
		else $employee = NULL;
		return $employee;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getCreatedTime() {
		return $this->get('created')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChangedTime() {
		return $this->get('changed')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOwner() {
		return $this->get('user_id')->entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOwnerId() {
		return $this->get('user_id')->target_id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOwnerId($uid) {
		$this->set('user_id', $uid);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOwner(UserInterface $account) {
		$this->set('user_id', $account->id());
		return $this;
	}


	public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
		$fields['id'] = BaseFieldDefinition::create('integer')
			->setLabel(t('ID'))
			->setDescription(t('The ID of the Employee entity.'))
			->setReadOnly(TRUE);
		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Employee entity.'))
			->setReadOnly(TRUE);

		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of Employee entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Employee Username'))
			->setDescription(t('The user ID of the Employee entity author.'))
			->setRevisionable(TRUE)
			->setSetting('target_type', 'user')
			->setSetting('handler', 'default')
			->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
			->setTranslatable(TRUE)
			->setDisplayOptions('view', array(
				'label' => 'hidden',
				'type' => 'author',
				'weight' => 0,
			))
			->setDisplayOptions('form', array(
				'type' => 'entity_reference_autocomplete',
				'weight' => 5,
				'settings' => array(
					'match_operator' => 'CONTAINS',
					'size' => '60',
					'autocomplete_type' => 'tags',
					'placeholder' => '',
				),
			))
			->setDisplayConfigurable('form', TRUE)
			->setDisplayConfigurable('view', TRUE);


		$fields['group_id'] = BaseFieldDefinition::create('integer')
			->setLabel(t('group_id'))
			->setDescription(t('The Group ID of the Employee'));


		$fields['first_name'] = BaseFieldDefinition::create('string')
			->setLabel(t('First Name'))
			->setDescription(t('The first name of the Employee entity.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 50,
				'text_processing' => 0,
			));

		$fields['last_name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Last Name'))
			->setDescription(t('The last name of the Employee entity.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 50,
				'text_processing' => 0,
			));

		$fields['middle_name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Middle Name'))
			->setDescription(t('The middle name of the Employee entity.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 50,
				'text_processing' => 0,
			));


		$fields['address'] = BaseFieldDefinition::create('string')
			->setLabel(t('Address'))
			->setDescription(t('The address of the Employee.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
				'text_processing' => 0,
			));


		$fields['mobile'] = BaseFieldDefinition::create('string')
			->setLabel(t('Mobile Number'))
			->setDescription(t('The mobile number of the Employee.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 32,
				'text_processing' => 0,
			));

		$fields['landline'] = BaseFieldDefinition::create('string')
			->setLabel(t('Landline Number'))
			->setDescription(t('The landline number of the Employee.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 32,
				'text_processing' => 0,
			));

		return $fields;
	}
}
