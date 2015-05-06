<?php
namespace Drupal\office\Entity;
use Drupal\office\MemberInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\x;
use Drupal\user\UserInterface;

/**
 * Defines the Member entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_member",
 *   label = @Translation("Member entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\office\Entity\Controller\MemberListController",
 *     "form" = {
 *       "default" = "Drupal\office\Entity\Form\MemberForm",
 *       "add" = "Drupal\office\Entity\Form\MemberForm",
 *       "edit" = "Drupal\office\Entity\Form\MemberForm",
 *       "delete" = "Drupal\office\Entity\Form\MemberDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer member",
 *   base_table = "office_member",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "entity.office_member.canonical",
 *     "delete-form" = "entity.office_member.delete_form",
 *     "edit-form" = "entity.office_member.edit_form",
 *     "collection" = "entity.office_member.collection"
 *   },
 *   field_ui_base_route = "member.settings",
 * )
 */
class Member extends ContentEntityBase implements MemberInterface {

	public static function loadByUserID($uid) {
		return x::loadEntityByUserID('office_member', $uid);
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
			->setDescription(t('The ID of the Member.'))
			->setReadOnly(TRUE);
		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Member.'))
			->setReadOnly(TRUE);

		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of Member.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Member Username'))
			->setDescription(t('The user ID of the Member.'))
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


		$fields['group_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('group_id'))
			->setDescription(t('The Group ID of the Member'))
			->setSetting('target_type', 'office_group');




		$fields['type'] = BaseFieldDefinition::create('string')
			->setLabel(t('Member Type'))
			->setDescription(t('The type the Member.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 1,
			));


		$fields['first_name'] = BaseFieldDefinition::create('string')
			->setLabel(t('First Name'))
			->setDescription(t('The first name of the Member.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 32,
			));

		$fields['last_name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Last Name'))
			->setDescription(t('The last name of the Member.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 32,
			));

		$fields['middle_name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Middle Name'))
			->setDescription(t('The middle name of the Member.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 32,
			));


		$fields['address'] = BaseFieldDefinition::create('string')
			->setLabel(t('Address'))
			->setDescription(t('The address of the Member.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
				'text_processing' => 0,
			));


		$fields['mobile'] = BaseFieldDefinition::create('string')
			->setLabel(t('Mobile Number'))
			->setDescription(t('The mobile number of the Member.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 32,
				'text_processing' => 0,
			));

		$fields['landline'] = BaseFieldDefinition::create('string')
			->setLabel(t('Landline Number'))
			->setDescription(t('The landline number of the Member.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 32,
				'text_processing' => 0,
			));

		return $fields;
	}



	/**
	 *
	 * Creates/Updates Member Form Submit
	 *
	 * @param array $data
	 * @return null|void
	 * - if there is no error, null is returned
	 * - if there is error, error code is return.
	 */
	public static function formSubmit(array &$data) {
		if ( ! x::login() ) return x::errorInfoArray(x::error_login_first, $data);
		$in = x::input();


		/**
		 * @note do not return here, so the data will be saved.
		 */
		if ($re = self::validateFormSubmit($data)) {
			// error
		}


		$entity = self::loadByUserID(x::myUid());
		if ( $entity ) {
			// loaded
		}
		else {
			$entity = Member::create();
		}
		$entity->set('first_name', $in['first_name']);
		$entity->set('middle_name', $in['middle_name']);
		$entity->set('last_name', $in['last_name']);
		$entity->set('mobile', $in['mobile']);
		$entity->set('landline', $in['landline']);
		$entity->set('address', $in['address']);
		$entity->set('user_id', x::myUid());
		$entity->set('group_id', $in['group_id']);
		$entity->save();
		return NULL;
	}

	private static function validateFormSubmit(array &$data) {
		$in = x::input();
		if ( empty($in['group_id']) ) return x::errorInfoArray(x::error_select_group, $data);
		if ( empty($in['first_name']) ) return x::errorInfoArray(x::error_input_first_name, $data);
		if ( empty($in['last_name']) ) return x::errorInfoArray(x::error_input_last_name, $data);
		if ( empty($in['middle_name']) ) return x::errorInfoArray(x::error_input_middle_name, $data);
		if ( empty($in['mobile']) ) return x::errorInfoArray(x::error_input_mobile, $data);
		if ( empty($in['landline']) ) return x::errorInfoArray(x::error_input_landline, $data);
		if ( empty($in['address']) ) return x::errorInfoArray(x::error_input_address, $data);
		return false;
	}
}
