<?php
namespace Drupal\office\Entity;
use Drupal\office\TaskLogInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\x;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Defines the TaskLog entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_tasklog",
 *   label = @Translation("TaskLog entity"),
 *   base_table = "office_tasklog",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class TaskLog extends ContentEntityBase implements TaskLogInterface {
	public static function loadByTaskId($id) {
		$db = \Drupal::entityQuery('office_tasklog');
		$db->condition('task_id', $id);
		$ids = $db->execute();
		$logs = [];
		if ( $ids ) {
			$logs = TaskLog::loadMultiple($ids);
			foreach( $logs as $log ) {
				$log->data = unserialize($log->get('data')->value);
				$log->user = User::load($log->get('user_id')->target_id);
			}
		}
		return $logs;
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
			->setDescription(t('The ID of the Task entity.'))
			->setReadOnly(TRUE);
		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Task entity.'))
			->setReadOnly(TRUE);

		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('User ID'))
			->setDescription(t('The person who created this task.'))
			->setSetting('target_type', 'user');

		$fields['task_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Task ID'))
			->setDescription(t('The task id this log belongs to.'))
			->setSetting('target_type', 'office_task');

		$fields['data'] = BaseFieldDefinition::create('string')
			->setLabel(t('Task Log Data'))
			->setDescription(t('The task log data of the entity.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 19000,
			));

		return $fields;
	}


}
