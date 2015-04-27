<?php

/**
 * @file
 * Contains Drupal\office\Entity\Client.
 */

namespace Drupal\office\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\ClientInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Client entity.
 *
 * @ingroup office
 *
 * @ContentEntityType(
 *   id = "office_client",
 *   label = @Translation("Client entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\office\Entity\Controller\ClientListController",
 *     "views_data" = "Drupal\office\Entity\ClientViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\office\Entity\Form\ClientForm",
 *       "add" = "Drupal\office\Entity\Form\ClientForm",
 *       "edit" = "Drupal\office\Entity\Form\ClientForm",
 *       "delete" = "Drupal\office\Entity\Form\ClientDeleteForm",
 *     },
 *   },
 *   base_table = "office_client",
 *   admin_permission = "administer Client entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "entity.office_client.canonical",
 *     "edit-form" = "entity.office_client.edit_form",
 *     "delete-form" = "entity.office_client.delete_form",
 *     "collection" = "entity.office_client.collection"
 *   },
 *   field_ui_base_route = "office_client.settings"
 * )
 */
class Client extends ContentEntityBase implements ClientInterface {
	/**
	 * {@inheritdoc}
	 */
	public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
		parent::preCreate($storage_controller, $values);
		$values += array(
			'user_id' => \Drupal::currentUser()->id(),
		);
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

	/**
	 * {@inheritdoc}
	 */
	public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
		$fields['id'] = BaseFieldDefinition::create('integer')
			->setLabel(t('ID'))
			->setDescription(t('The ID of the Client entity.'))
			->setReadOnly(TRUE);

		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Client entity.'))
			->setReadOnly(TRUE);

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Authored by'))
			->setDescription(t('The user ID of the Client entity author.'))
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
			->setDescription(t('The name of the Client entity.'))
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
			->setDescription(t('The language code of Client entity.'));

		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));

		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));

		return $fields;
	}

}
