<?php
namespace Drupal\office\Entity;
use Drupal\office\ProcessInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\x;
use Drupal\user\UserInterface;

/**
 * Defines the Process entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_process",
 *   label = @Translation("Process entity"),
 *   base_table = "office_process",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Process extends ContentEntityBase implements ProcessInterface {


	public static function loadByUserID($uid) {
		if ( empty($uid) ) return null;
		return x::loadEntityByUserID('office_group', $uid);
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
			->setDescription(t('The ID of the Process entity.'))
			->setReadOnly(TRUE);
		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Process entity.'))
			->setReadOnly(TRUE);

		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of Process entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Process Creator'))
			->setDescription(t('The person who created this Process.'))
			->setSetting('target_type', 'user');



		$fields['name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Name'))
			->setDescription(t('The name of the Process.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
			));


		$fields['summary'] = BaseFieldDefinition::create('string')
			->setLabel(t('Summary'))
			->setDescription(t('The summary of the Workflow.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 8192,
			));


		$fields['description'] = BaseFieldDefinition::create('string')
			->setLabel(t('Description'))
			->setDescription(t('The description of the Workflow.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 8192,
			));

		return $fields;
	}

	public static function formSubmit(array & $data) {
		x::log(__METHOD__);


		if ( x::in('process_id') ) {
			$process = self::load(x::in('process_id'));
		}
		else {
			$process = self::create();
			$process->set('user_id', x::myUid());
		}

		$process->set('name', x::in('name'));
		$process->set('summary', x::in('summary'));
		$process->set('description', x::in('description'));
		$process->save();
		return $process->id();

	}

	private static function validateFormSubmit(array &$data) {


		return false;
	}

}
