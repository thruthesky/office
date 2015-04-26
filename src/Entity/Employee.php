<?php
namespace Drupal\office\Entity;
use Drupal\office\EmployeeInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
/**
 * Defines the Employee entity.
 *
 *
 * @ContentEntityType(
 *   id = "employee",
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
 *     "canonical" = "entity.employee.canonical",
 *     "delete-form" = "entity.employee.delete_form",
 *     "edit-form" = "entity.employee.edit_form",
 *     "collection" = "entity.employee.collection"
 *   },
 *   field_ui_base_route = "employee.settings",
 * )
 */
class Employee extends ContentEntityBase implements EmployeeInterface {

	public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
		$fields['id'] = BaseFieldDefinition::create('integer')
			->setLabel(t('ID'))
			->setDescription(t('The ID of the Employee entity.'))
			->setReadOnly(TRUE);
		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Employee entity.'))
			->setReadOnly(TRUE);
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
		$fields['name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Name'))
			->setDescription(t('The name of the Employee entity.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 50,
				'text_processing' => 0,
			))
			->setDisplayOptions('view', array(
				'label' => 'above',
				'type' => 'string',
				'weight' => -4,
			))
			->setDisplayOptions('form', array(
				'type' => 'string_textfield',
				'weight' => -4,
			))
			->setDisplayConfigurable('form', TRUE)
			->setDisplayConfigurable('view', TRUE);
		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of Employee entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));
		$fields['address'] = BaseFieldDefinition::create('string')
			->setLabel(t('Address'))
			->setDescription(t('The address of the Employee.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
				'text_processing' => 0,
			))
			->setDisplayOptions('view', array(
				'label' => 'above',
				'type' => 'string',
				'weight' => -4,
			))
			->setDisplayOptions('form', array(
				'type' => 'string_textfield',
				'weight' => -4,
			))
			->setDisplayConfigurable('form', TRUE)
			->setDisplayConfigurable('view', TRUE);
		return $fields;
	}

}
