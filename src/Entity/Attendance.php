<?php

/**
 * @file
 * Contains Drupal\office\Entity\Attendance.
 */

namespace Drupal\office\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\AttendanceInterface;
use Drupal\office\x;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Defines the Attendance entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_attendance",
 *   label = @Translation("Attendance entity"),
 *   base_table = "office_attendance",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Attendance extends ContentEntityBase implements AttendanceInterface {
	const ATTENDABLE_TIME = 60; // minutes
	public static function checkAttend($uid, $date) {
		$row = self::getAttend($uid, $date);
		if ( empty($row) ) return 0;
		return $row['id'];
	}

	/**
	 * Gets attend information of a date of the UID
	 * @param $uid
	 * @param $date
	 *
	 * @return array -
	 *
	 *      - 리턴 예
	 *
	 *      Array
	(
	[id] => 2
	[uuid] => 4378a934-514b-4fba-8500-efd8b975fa56
	[user_id] => 2
	[langcode] => en
	[created] => 1430722273
	[changed] => 1430722273
	[status] => A
	[date] => 20150504
	[stamp] => 1430722273
	)
	 *
	 *
	 * @ATTENTION
	 */
	private static function getAttend($uid, $date) {
		$res = db_select('office_attendance','a')
			->fields('a')
			->condition('user_id', $uid)
			->condition('date', $date)
			->condition('status', 'A')
			->execute();
		return $res->fetchAssoc();
	}

	public static function getAttendanceOfMonth($uid, $Ym) {
		/**
		$db = \Drupal::entityQuery('office_attendance');
		$db->condition('date', "$Ym%", 'LIKE');
		$ids = $db->execute();
		$attends = self::loadMultiple($ids);
		 */

		$ret = [];
		$user = User::load($uid);
		$member = Member::loadByUserID($uid);
		$id = $member->get('group_id')->target_id;
		//di($id);
		$work_hour = x::getGroupWorkingHours($id);
		$ret['work_hour_begin'] = $work_hour[0];
		$ret['work_hour_end'] = $work_hour[1];

		$date_begin = $Ym . '01';
		$date_end = $Ym . date('t', strtotime($date_begin));


		$rows = [];
		$total_min_late = 0;
		$today = date("Ymd");
		$employeement_day = date('Ymd', $member->getCreatedTime() );
		for( $i = $date_begin; $i <= $date_end; $i ++ ) {
			if ( $i < $employeement_day ) continue; // 그룹에 가입한 날짜 이전.
			if ( $i > $today ) break; // 오늘 이후
			$row = [];
			$row['username'] = $user->getUsername();
			$row['date'] = date('M d, Y', strtotime($i));
			$row['day'] = date('D', strtotime($i));

			//
			$work = self::getWorkingHours($uid, $member->get('group_id')->target_id, $i );

			if ( is_array($work) ) { // 근무하는 날.
				$row['work_begin'] = $work['begin'];
				$row['work_end'] = $work['end'];
				$attend = self::getAttend($uid, $i);
				if ( empty($attend) ) {
					$row['begin_status'] = 'absent';
				}
				else {
					$row['attend_time'] = date("H:i", $attend['stamp']);
					//$row['begin_status'] = self::isLate("$i$row[work_begin]00", $attend['stamp']);
					$row['begin_status'] = self::isLate($row['work_begin'], $attend['stamp']);
					if ( $row['begin_status'] ) {
						$total_min_late += $row['begin_status'];
						$row['begin_status'] .= ' minutes late';
					}
					else {
						// 올바른 attend
					}
				}

			}
			else { // 근무 안하는 날.
				$row['begin_status'] = $work;
			}


			$rows[] = $row;
		}
		$ret['days'] = $rows;

		return $ret;
	}


	/**
	 *
	 * @param $uid - 회원 번호
	 * @param $date - 날짜
	 * @param $stamp - 로그인 하는 시간. 초.
	 *
	 * @code Attendance::attend(x::myUid(), date('Ymd'), time());
	 * @return bool|string
	 *
	 *      - 에러가 없으면 false 가 리턴된다.
	 *      - 에러가 있으면 에러코드가 리턴된다.
	 */
	public static function attend($uid, $date, $stamp) {
		x::log("Attendance::attend($uid, $date, $stamp) {...}");

		//
		if ( self::checkAttend($uid, $date) ) return x::error_already_atended;

		$member = Member::loadByUserID($uid);
		if ( empty($member) ) x::error_not_offfice_member;
		$group_id = $member->get('group_id')->target_id;
		if ( empty($group_id) ) return x::error_not_group_member;

		$work = self::getWorkingHours($uid, $group_id, $date);
		$code = 0;
		$status = 'A';
		if ( is_array($work) ) {
			//x::log($re);
			if ( self::isWorkEnded($work['end'], $stamp) ) {
				$code = x::error_work_ended;
				$status ='C';
			}
			else if ( ! self::isAttendable($work['begin'], $stamp) ) {
				$code = x::error_not_attendable;
				$status = 'B';
			}
			else { // no error. Attend 를 제 시간에 했음.
			}
		}
		else {
			$code = x::error_no_work;
			$status = 'D';
		}


		$attendance = Attendance::create();
		$attendance->set('user_id', $uid);
		$attendance->set('stamp', $stamp);
		$attendance->set('date', $date);
		$attendance->set('status', $status);
		$attendance->set('begin', $work['begin']);
		$attendance->set('end', $work['end']);
		$attendance->set('type', $work['type']);
		$attendance->set('reason', $work['reason']);
		$attendance->save();

		return $code;
	}

	/**
	 *
	 * 해당 사용자의 근무 시간 정보를 리턴한다. 개별 근무시간이 없으므로 해당 그룹의 해당 일에 대한 근무시간을 리턴한다.
	 *
	 * @param $uid - 사용자 번호
	 * @param $group_id - 그룹 번호
	 * @param $date - 날짜
	 * @return string
	 *      - 문자열로 리턴하면 업무가 없는 날이다.
	 *          -- Dayoff 이면, 개인 별 쉬는 날,
	 *          -- No work 이면, 그룹 전체가 쉬는 날.
	 *      - 배열로 리턴하면 근무 하는 날.
	 *          -- 첫번째 인자에 근무 시작 시간,
	 *          -- 두번째 인자에 근무 끝나는 시간이다.
	 *          -- 세번째는 indivisual 이면, 개별적 근무시간, group 이면, 그룹 근무시간.
	 *          -- 네번째는 사유. 개별적 근무시간이면, 그 이유를 리턴한다.
	 *
	 * @code
	 * 	 $member = Member::loadByUserID($uid);
	 * 	 $group_id = $member->get('group_id')->target_id;
	 * 	 $re = self::getWorkingHours($uid, $group_id, $date);
	 * @endcode
	 *
	 * @code
	 *
	 * 	$hours = Attendance::getWorkingHours(x::myUid(), $data['my']->get('group_id')->target_id, date("Ymd"));
	 * 	$data['work_begin'] = $hours[0];
	 * 	$data['work_end'] = $hours[1];
	 *
	 * @endcode
	 */
	public static function getWorkingHours($uid, $group_id, $date) {

		// 개별 근무일인가? 쉬는 날인가?
		$attend = self::getAttend($uid, $date);
		$member_work = x::getMemberWorkSchedule($group_id, $uid, $date);

		if ( $member_work ) {
			if ( empty($member_work['begin']) ) {
				// No work for the member.
				return "Personal Dayoff";
			}
			else {
				// Different working hours for the member.
				//di("member work");
				//di($member_work);
				$work['begin'] = $member_work['begin'];
				$work['end'] = $member_work['end'];
				$work['type'] = 'individual';
				$work['reason'] = $member_work['reason'];
			}
		}
		// 회사 근무일인가? 쉬는 날인가?
		else {
			// 그룹 전체 일하는날인가?
			$group_work = x::getGroupWorkSchedule($group_id, $date);
			if ( empty($group_work) ) {
				return 'Group has no work';
			}
			else {
				$work['begin'] = $group_work[0];
				$work['end'] = $group_work[1];
				$work['type'] = 'group';
			}
		}

		return $work;
		//return array($work_begin,$work_end, $work_schedule, $work_reason);
	}

	/**
	 * @param $Hi
	 * @param $stamp
	 * @return bool
	 *
	 *      - true if attendable
	 *      - false if too early to attend.
	 */
	private static function isAttendable($Hi, $stamp) {
		$begin = strtotime($Hi);
		$begin = $begin - 60 * self::ATTENDABLE_TIME;

		x::log(date('r', $stamp));
		x::log(date('r', $begin));

		if ( $stamp < $begin ) return false;
		return true;
	}

	/**
	 *
	 * 근무시간이 경과하였으면 참을 리턴한다.
	 *
	 * @param $Hi - 근무 마지막 시간.
	 * @param $stamp - 현재 시간.
	 * @return bool
	 *
	 */
	private static function isWorkEnded($Hi, $stamp) {
		$end = strtotime($Hi);
		//x::log(date('r', $end));
		//x::log(date('r', $stamp));
		if ( $stamp > $end ) return true;
		return false;
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
			->setDescription(t('The ID of the Attendance entity.'))
			->setReadOnly(TRUE);

		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Attendance entity.'))
			->setReadOnly(TRUE);

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Attendance User'))
			->setDescription(t('The user ID of the Attendance user.'))
			->setSetting('target_type', 'user');

		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of Group entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));

		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));



		$fields['status'] = BaseFieldDefinition::create('string')
			->setLabel(t('Status of Attend'))
			->setDescription(t('A for attend, L for leave.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 1,
			));

		$fields['date'] = BaseFieldDefinition::create('integer')
			->setLabel(t('Date'))
			->setDescription(t('The date of attend or leave.'));

		$fields['begin'] = BaseFieldDefinition::create('string')
			->setLabel(t('Work Begin Hour'))
			->setDescription(t('Work begin hour'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 4,
			));

		$fields['end'] = BaseFieldDefinition::create('string')
			->setLabel(t('Work End Hour'))
			->setDescription(t('Work end hour'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 4,
			));

		$fields['type'] = BaseFieldDefinition::create('string')
			->setLabel(t('Work schedule type'))
			->setDescription(t('Work schedule for the type. group or individual'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 16,
			));

		$fields['reason'] = BaseFieldDefinition::create('string')
			->setLabel(t('Work schedule reason'))
			->setDescription(t('Reason for the schedule. Why the schedule is like this?'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
			));

		$fields['stamp'] = BaseFieldDefinition::create('integer')
			->setLabel(t('Unix timestamp'))
			->setDescription(t('The unix timestamp of attend or leave.'));

		return $fields;
	}



	/**
	 * @param array $data
	 * @return mixed
	 *      - returns group id
	 *      - or error information if there is any error.
	 *
	 *
	 */
	public static function formSubmit(array & $data) {

	}

	private static function validateFormSubmit(array &$data) {
		return false;
	}

	/**
	 *
	 * 늦지 않았으면 0 을 리턴.
	 *
	 * 늦었으면 음수를 리턴.
	 *
	 * @param $YmdHis_work_begin - 근무 시작 시간
	 * @param $stamp - 실제 attend 한 unix timestamp 시간
	 * @return bool|string
	 *
	 */
	private static function isLate($Hi, $stamp) {
		$log_Hi = date('Hi', $stamp);
		$work_stamp = strtotime($Hi);
		$log_stamp = strtotime($log_Hi);
		if ( $work_stamp > $log_stamp ) return 0;
		$min = intval(($log_stamp - $work_stamp) / 60) + 1;
		return -$min;
	}

}
