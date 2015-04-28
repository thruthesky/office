<?php
namespace Drupal\office\Entity;
use Drupal\office\IssueInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Issue entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_issue",
 *   label = @Translation("Issue entity"),
 *   base_table = "office_issue",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Issue extends ContentEntityBase implements IssueInterface {

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
			->setDescription(t('The ID of the Issue entity.'))
			->setReadOnly(TRUE);
		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Issue entity.'))
			->setReadOnly(TRUE);
		$fields['client_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Client User ID'))
			->setDescription(t('The client user ID of the Issue entity author.'))
			->setSetting('target_type', 'user');

		$fields['name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Name'))
			->setDescription(t('The name(subject) of the Issue entity.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
				'text_processing' => 0,
			));
		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of Issue entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));
		$fields['description'] = BaseFieldDefinition::create('string')
			->setLabel(t('Description'))
			->setDescription(t('The description of the Issuee.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
				'text_processing' => 0,
			));

		$fields['group_id'] = BaseFieldDefinition::create('integer')
			->setLabel(t('group_id'))
			->setDescription(t('The Group ID of the Employee'));
		return $fields;
	}
}
