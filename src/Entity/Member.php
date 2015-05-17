<?php
namespace Drupal\office\Entity;
use Drupal\file\Entity\File;
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

	/**
	 * 사용자 번호를 입력받아서 그룹 회원 정보를 리턴한다.
	 *
	 * @param $uid
	 * @return mixed|null
	 */
	public static function loadByUserID($uid) {
		if ( empty($uid) ) return null;
		$member = x::loadEntityByUserID('office_member', $uid);
		if ( $member ) {
			$ids = db_select('file_usage','f')
				->fields('f',['fid'])
				->condition('module','office')
				->condition('type', 'member')
				->condition('id',$member->id())
				->execute()
				->fetchAllKeyed();
			if ( $ids ) {
				foreach($member->files = File::loadMultiple(array_keys($ids)) as $file) {
					$file->name_url_decoded = urldecode($file->getFilename());
				}
			}
		}
		return $member;
	}

	/**
	 * 사용자 이름(=아이디)를 입력 받아서 그룹 회원 정보를 리턴한다.
	 *
	 * @param $name
	 * @return mixed|null
	 */
	public static function loadByUsername($name) {
		if ( $user = user_load_by_name($name) ) return Member::loadByUserID($user->id());
		return null;
	}

	/**
	 *
	 * 내가 가입한 그룹 정보를 리턴한다.
	 *
	 * @param $uid
	 * @return null
	 *
	 */
	public static function group($uid) {
		$member = self::loadByUserID($uid);
		if ( empty($member) ) return null;
		else return $member->get('group_id')->entity;
	}

	/**
	 *
	 * 내가 그룹장으로 있으며 내가 소유한 그룹 정보를 리턴한다.
	 *
	 * @param $myUid
	 *
	 * @return mixed|null
	 */
	public static function myOwnGroup($myUid) {
		return x::loadEntityByUserID('office_group', $myUid);
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
		//$entity->set('group_id', $in['group_id']);// // @NOTE 회원이 자기 그룹을 선택하지 못한다.
		$entity->save();
		$id = $entity->id();

		$max_uploaded_files = count($_FILES['files']['name']);
		if ( $max_uploaded_files ) {// 파일 업로드가 되었는가?
			$dir_repo = 'public://file-upload/office/';
			$ret = file_prepare_directory($dir_repo, FILE_CREATE_DIRECTORY);			// 파일이 업로드 된 경우, 디렉토리 생성. 파일 업로드 될 때마 체크하지만 속도나 성능에 영향을 미치지 않음.
			if ( ! $ret ) $error_code = x::messageTaskFailedPrepareDirectory($data);
			for ( $j = 0; $j < $max_uploaded_files; $j ++ ) {
				$name = $_FILES['files']['name'][$j];
				$type = $_FILES['files']['type'][$j];
				$tmp_name = $_FILES['files']['tmp_name'][$j];
				$error = $_FILES['files']['error'][$j];
				$size = $_FILES['files']['size'][$j];
				if ( $error ) x::log("File upload error: $error");
				else {
					$name = urlencode($name);
					if ( strlen($name) > 150 ) {
						$pi = pathinfo($name);
						$name = substr($name, 0, 150);
						$name = trim($name, ' \t\n\r\0\x0B%');
						$name .= '.' . $pi['extension'];
					}
					$file = file_save_data(file_get_contents($tmp_name), $dir_repo . $name);
					if ( $file ) {
						\Drupal::service('file.usage')->add($file, 'office', 'member', $id);
					}
					else {
						x::log("error: uploading file. file name:$name");
						$error_code = x::messageTaskFailedToUploadFile($data);
					}
				}
			}
		}

		return NULL;
	}

	private static function validateFormSubmit(array &$data) {
		$in = x::input();
		//if ( empty($in['group_id']) ) return x::errorInfoArray(x::error_select_group, $data);
		if ( empty($in['first_name']) ) return x::errorInfoArray(x::error_input_first_name, $data);
		if ( empty($in['last_name']) ) return x::errorInfoArray(x::error_input_last_name, $data);
		if ( empty($in['middle_name']) ) return x::errorInfoArray(x::error_input_middle_name, $data);
		if ( empty($in['mobile']) ) return x::errorInfoArray(x::error_input_mobile, $data);
		if ( empty($in['landline']) ) return x::errorInfoArray(x::error_input_landline, $data);
		if ( empty($in['address']) ) return x::errorInfoArray(x::error_input_address, $data);
		return false;
	}
}
