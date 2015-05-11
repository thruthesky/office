<?php
namespace Drupal\office\Entity;
use Drupal\office\TaskInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\x;
use Drupal\user\UserInterface;

/**
 * Defines the Task entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_task",
 *   label = @Translation("Task entity"),
 *   base_table = "office_task",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Task extends ContentEntityBase implements TaskInterface {

	public static $config_priority_value = [
		8 => "Very Important & Urgent",
		6 => "Normal",
		4 => "Low",
		2 => "None(Don't Care)",
	];

	public static $config_priority = [
		'urgent'    => ['value'=>8, 'text'=>"Very Important & Urgent"],
		'normal'    => ['value'=>6, 'text'=>"Normal"],
		'low'       => ['value'=>4, 'text'=>"Low"],
		'none'      => ['value'=>2, 'text'=>"None(Don't Care)"],
	];

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
			->setDescription(t('The language code of Task entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));



		$fields['creator_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Task Creator'))
			->setDescription(t('The person who created this task.'))
			->setSetting('target_type', 'user');

		$fields['client_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Service For( Client ID)'))
			->setDescription(t('The person whom this service for. The client.'))
			->setSetting('target_type', 'user');


		$fields['worker_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Assign To'))
			->setDescription(t('The person who performs this task.'))
			->setSetting('target_type', 'user');


		$fields['in_charge_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Responsible To'))
			->setDescription(t('The person in charge of the task.'))
			->setSetting('target_type', 'user');


		$fields['group_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('group_id'))
			->setDescription(t('The Group ID of the Task'))
			->setSetting('target_type', 'office_group');



		$fields['title'] = BaseFieldDefinition::create('string')
			->setLabel(t('Title'))
			->setDescription(t('The title of the Task.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
			));



		$fields['summary'] = BaseFieldDefinition::create('string')
			->setLabel(t('Summary'))
			->setDescription(t('The summary of the Task.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 8192,
			));





		$fields['description'] = BaseFieldDefinition::create('string')
			->setLabel(t('Description'))
			->setDescription(t('The description of the Task.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 8192,
			));


		$fields['deadline'] = BaseFieldDefinition::create('string')
			->setLabel(t('Deadline'))
			->setDescription(t('The deadline of the Task.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 16,
			));


		$fields['priority'] = BaseFieldDefinition::create('string')
			->setLabel(t('Priority'))
			->setDescription(t('The priority of the Task.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 1,
			));


		$fields['process_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Work Process'))
			->setDescription(t('The work process of the Task.'))
			->setSetting('target_type', 'office_process');





		$fields['roadmap'] = BaseFieldDefinition::create('integer')
			->setLabel(t('Roadmap Percentage'))
			->setDescription(t('The roadmap percentage of the Task.'));


		$fields['view_status'] = BaseFieldDefinition::create('string')
			->setLabel(t('View status'))
			->setDescription(t('The view status of the Task.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 1,
			));



		return $fields;
	}

	public static function formSubmit(array & $data) {
		x::log(__METHOD__);

		if ($re = self::validateFormSubmit($data)) {
			// error
		}


		if ( x::in('task_id') ) {
			$task = task::load(x::in('task_id'));
		}
		else {
			$task = task::create();
			$task->set('creator_id', x::myUid());
		}
		$task->set('group_id', x::in('group_id', 0));
		$task->set('title', x::in('title'));
		$task->set('summary', x::in('summary'));
		$task->set('description', x::in('description'));
		$task->set('client_id', x::getUserID(x::in('client')));
		$task->set('worker_id', x::getUserID(x::in('worker')));
		$task->set('in_charge_id', x::getUserID(x::in('in_charge')));

		$task->set('priority', x::in('priority', 2));
		$task->set('deadline', x::in('deadline'));

		$task->set('roadmap', x::in('roadmap', 0));

		$task->set('view_status', x::in('view_status', 'O'));

		$process = Process::loadByName(x::in('process'));
		if ( $process ) $task->set('process_id', $process->id());
		else $task->set('process_id', 0);



		$task->save();
		return $task->id();
	}

	private static function validateFormSubmit(array &$data) {

		if ( ! x::in('group_id')  ) return x::errorInfoArray(x::error_select_group, $data);
		if ( ! x::in('title') ) return x::errorInfoArray(x::error_input_title, $data);
		if ( x::in('worker') ) {
			$worker = user_load_by_name(x::in('worker'));
			if ( empty($worker) ) return x::errorInfoArray(x::error_wrong_worker, $data);
		}
		return false;
	}

	/**
	 *
	 * Get all the information of the task entity like worker name.
	 *
	 *
	 * @param $id
	 * @return null|static
	 */
	public static function loadFull($id) {
		if ( empty($id) ) return null;
		$task = task::load($id);
		$p = $task->get('priority')->value;
		if ( isset(self::$config_priority_value[$p]) ) {
			$task->priority_text = self::$config_priority_value[$p];
			$process_id = $task->get('process_id')->target_id;
			$task->process = x::markupProcess($process_id);
		}
		return $task;
	}

	public static function loadMultipleFull($ids) {
		$tasks = [];
		if ( empty($ids) ) return $tasks;
		foreach( $ids as $id ) {
			$task = self::loadFull($id);
			$tasks[] = $task;
		}
		return $tasks;
	}
}
