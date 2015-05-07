<?php
namespace Drupal\office;

use Drupal\office\Entity\Group;
use Drupal\office\Entity\Member;
use Drupal\office\Entity\Task;
use Drupal\user\Entity\User;
use Drupal\user\UserAuth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Yaml\Yaml;

/**
 * @property  count_log
 */
class x {
	const error_login_first = 'login first';
	const error_logged_in_already = 'logged in already';
	const error_wrong_password = 'wrong password';
	const error_wrong_id = 'wrong id';
	const error_select_group = 'select group';
	const error_input_address = 'input address';
	const error_input_first_name = 'input first name';
	const error_input_last_name = 'input last name';
	const error_input_middle_name = 'input middle name';
	const error_input_mobile = 'input mobile';
	const error_input_landline = 'input landline';
	const error_input_name = 'input name';
	const error_wrong_owner = 'wrong owner name';
	const error_input_title = 'input title';
	const error_wrong_creator = 'wrong creator';
	const error_wrong_worker = 'wrong worker';
	const error_group_name_exist = 'Group name already exists.';
	const error_no_work = 'no work';
	const error_not_attendable = 'not attendable';
	const error_work_ended = 'working hour ended';
	const error_already_atended = 'already attended';
	const error_not_offfice_member = 'not offfice member';
	const error_not_group_member = 'not group member';
	private static $count_log = 0;

	public static function getThemeName() {
		$uri = \Drupal::request()->getRequestUri();
		if ( $uri == '/office' ) return 'office';
		$uri = str_replace('/office/', '', $uri);
		$uri = str_replace('/', '.', $uri);
		$uri = strtolower($uri);
		return $uri;
	}

	public static function getThemeFileName() {
		return self::getThemeName() . '.html.twig';
	}

	public static function isFromSubmit() {
		return \Drupal::request()->get('mode') == 'submit';
	}

	/**
	 * Gets an error code and returns error information array.
	 *
	 * @param $re - error code
	 * @param array $data - is the array of render array variable.
	 * @return int - error code
	 * - error code
	 */
	public static function errorInfoArray($re, array &$data)
	{
		if ($re) {
			$data['code'] = $re;
			$data['message'] = x::getErrorMessage($re);
		}
		return $re;
	}


	public static function g($k, $default=null) {
		return \Drupal::request()->get($k, $default);
	}

	/**
	 *
	 * Returns the $default if the input is *empty*
	 *
	 * if 0 is the value, then it's empty. In this case it returns $default. the default is empty string.
	 *
	 *
	 * @note this is a handy wrapper of \Drupal::request()
	 * @param $parameter
	 * @param null|string $default
	 * @return mixed|null
	 */
	public static function in($parameter, $default='') {
		$re = self::g($parameter, $default);
		if ( empty($re) ) $re = $default;
		return $re;
	}


	/**
	 * @param $username
	 * @return int
	 *
	 *
	 * @code if ( ! x::getUserID(x::in('owner')) ) return x::errorInfoArray(x::error_wrong_owner, $data);
	 */
	public static function getUserID($username) {
		if ( $username ) {
			$user = user_load_by_name($username);
			if ( $user ) {
				return $user->id();
			}
		}
		return 0;
	}


	/**
	 * @param $id
	 * @return array|mixed|null|string
	 * @code $task->worker = x::getUsernameByID( $task->get('worker_id')->value );
	 */
	public static function getUsernameByID($id) {
		if ( $id ) {
			$user = User::load($id);
			if ( $user ) {
				return $user->getUsername();
			}
		}
		return '';
	}


	public static function myUid() {
		return \Drupal::currentUser()->getAccount()->id();
	}

	public static function login() {
		return self::myUid();
	}
	public static function admin()
	{
		return x::myUid() == 1;
	}

	public static function getErrorMessage($code) {
		switch($code) {
			case self::error_login_first : return "Please, login first";
			case self::error_logged_in_already : return 'You are logged in already';
			case self::error_wrong_password : return 'You input wrong password';
			case self::error_wrong_id : return 'You input wrong id';
			case self::error_select_group : return "Please select a group";
			case self::error_no_work : return "You do not have work today! @NOTE: Ask your group admin if he forgot to: (1). put work schedules. (2). put work hours.";
			case self::error_not_attendable : return "Failed BUT Recorded: Your attend is recorded. Is it too early to attend? Try to attend again in TIME.";
			case self::error_work_ended : return "Working hour is over. You cannot attend anymore.";
			default: return "$code";
		}
	}

	public static function isOfficePage() {
		$request = \Drupal::request();
		$uri = $request->getRequestUri();
		if ( strpos( $uri, '/office') !== FALSE ) {
			return true;
		}
		else if ( strpos( $uri, '/admin/office') !== FALSE ) {
			return true;
		}
		else return FALSE;
	}

	/**
	 * User Login Form Submit
	 * @param $data
	 * @return bool
	 * - If there is no error, return false.
	 * - if there is error, error code is return with the error information in $data variable.
	 */
	public static function loginFormSubmit(array &$data) {

		if ( x::login() ) return self::errorInfoArray(self::error_logged_in_already, $data);

		$request = \Drupal::request();
		$name = $request->get('id');
		$password = $request->get('password');


		// check - if the login is okay
		$userStorage = \Drupal::entityManager();
		$passwordChecker = \Drupal::service('password');
		$auth = new UserAuth($userStorage, $passwordChecker);
		$uid = $auth->authenticate($name, $password);
		$user = user_load_by_name($name);

		if ( $uid ) {
			user_login_finalize($user);
		}
		else {
			if ( $user ) return self::errorInfoArray(self::error_wrong_password, $data);
			else return self::errorInfoArray(self::error_wrong_id, $data);
		}
		return false;
	}

	public static function input() {
		$request = \Drupal::request();
		$get = $request->query->all();
		$post = $request->request->all();
		return array_merge( $get, $post );
	}


	public static function loadEntityByUserID($type,$uid) {
		$entities = \Drupal::entityManager()->getStorage($type)->loadByProperties(['user_id'=>$uid]);
		if ( $entities ) $entity = reset($entities);
		else $entity = NULL;
		return $entity;
	}


	public static function log ( $str )
	{
		$path_log = "./x8.log";
		$fp_log = fopen($path_log, 'a+');

		if ( ! is_string($str) ) {
			$str = print_r( $str, true );
		}
		self::$count_log ++;
		fwrite($fp_log, self::$count_log . " : $str\n");

		fclose( $fp_log );
	}

	public static function loginResponse() {
		return new RedirectResponse('/office/login');
	}


	/**
	 *
	 * Save/Update/Get config.
	 *
	 * If the code/category exsits, it updates.
	 *
	 * @param $code
	 * @param $category
	 * @param null $v
	 * @return null
	 * @throws \Exception
	 * @code
	 *
	 * $data['config']['work'] = x::config( 'group_' . $office_group->id() . '_work' );
	 *
	 * @endcode
	 *
	 * @code
	 *
	$rets = [];
	$rows = self::config( 'group_' . $id . '_work' );
	if ( $rows ) {
	foreach ( $rows as $row ) {
	$rets[$row['category']] = $row['value'];
	}
	}
	return $rets;
	 *
	 * @endcode
	 */
	public static function config( $code, $category=null, $v=null )
	{
		if ( empty($code) ) return null;


		$db = db_select('office_config', 'c')
			->fields('c')
			->condition('code', $code);
		if ( $category ) $db->condition('category', $category);
		$res = $db->execute();


		if ( $v === null ) {
			$rets = [];
			while ( $row = $res->fetchAssoc() ) {
				$rets[] = $row;
			}
			return $rets;
		}
		else {
			$row = $res->fetchAssoc();
			if ( empty($row) ) {
				db_insert('office_config')
					->fields(['code'=>$code, 'category'=>$category, 'value'=>$v])
					->execute();
			}
			else {
				db_update('office_config')
					->fields(['value'=>$v])
					->condition('code', $code)
					->condition('category', $category)
					->execute();
			}
		}
	}

	public static function config_delete( $code, $category )
	{
		db_delete('office_config')
			->condition('code', $code)
			->condition('category', $category)
			->execute();
	}

	public static function config_delete_by_idx( $idx )
	{
		db_delete('office_config')
			->condition('idx', $idx)
			->execute();
	}


	public static function config_group_set_from_input($id, $category, $value) {
		return self::config("group_{$id}", $category, $value);
	}

	/**
	 *
	 * Group inputs
	 *
	 * @param $id
	 * @param $category
	 * @param $form_name
	 * @return null
	 *
	 */
	public static function gin($id, $category, $form_name) {
		return self::config_group_set_from_input($id, $category, self::in($form_name));
	}

	/**
	 * Returns Group Configs
	 *
	 * @code $data['config'] = x::gs( $office_group->id() );
	 */
	public static function group_config($id, $category=null) {
		$db = db_select('office_config','c')
			->fields('c')
			->condition('code', "group_{$id}");

		if ( $category ) $db->condition('category', $category);

		$res = $db->execute();


		$rows = $res->fetchAllAssoc('code');

		$rets=[];
		if ( $rows ) {
			foreach ( $rows as $code => $v ) {
				$code = str_replace("group_{$id}", '', $code);
				$rets[$code] = $v;
			}
		}
		return $rets;
	}

	/**
	 * @param $id
	 * @return array
	 *
	 *  - monday ~ sunday 에 Y 또는 빈문자열 표시로 리턴한다.
	 */
	public static function getGroupWorkingDays($id) {
		$rets = [];
		$rows = self::config( 'group_' . $id . '_work' );
		if ( $rows ) {
			foreach ( $rows as $row ) {
				$rets[$row['category']] = $row['value'];
			}
		}
		return $rets;
	}


	/**
	 *
	 * 그룹만의 쉬는 날. 만약 월~금 일하는데, 특별히 5월 5일(화)요일만 쉬게 한다는 등.
	 *
	 * @param $id
	 * @return array
	 *
	 * @todo 날짜 값을 입력 받아서, 해당 날짜만 리턴 할 것.
	 */
	public static function getGroupDayoffs($id) {
		$rets = [];
		$rows = self::config( 'group_' . $id . '_dayoff' );
		if ( $rows ) {
			foreach ( $rows as $row ) {
				$rets[$row['category']] = $row['value'];
			}
		}
		return $rets;
	}


	/**
	 * Return true if the configuration exists.
	 * @param $code
	 * @param $category
	 * @return bool
	 */
	public static function config_exist($code, $category) {
		$rows = self::config( $code, $category );
		if ( $rows ) return true;
		else return false;
	}

	public static function setGroupTime($group_id, $begin, $end) {
		$code = 'group_work_hour_' . $group_id;
		$time = $begin.'-'.$end;
		x::config($code, '', $time);
	}

	/**
	 *
	 * Returns group working hour.
	 *
	 * @param $group_id
	 * @return array
	 *  - group work begin hour in first element.
	 *  - group work end hour in second element.
	 */
	public static function getGroupWorkingHours($group_id) {
		$code = 'group_work_hour_' . $group_id;
		$rows = x::config($code, '');
		if ( $rows ) {
			$row = reset($rows);
			if ( $row ) {
				return explode('-', $row['value']);
			}
		}
		return [];
	}




	/**
	 * @param $group_id
	 * @param $name
	 * @param $date
	 * @param $begin
	 * @param $end
	 * @param $reason
	 */
	public static function setIndividualTime($group_id, $name, $date, $begin, $end, $reason) {
		$id = self::getUserID($name);
		$code = 'individual_work_hour_' . $group_id . '_' . $id;
		$time = $begin.'-'.$end . ' ' . $reason;
		$date = str_replace("-", '', $date); //
		x::config($code, $date, $time);
	}

	/**
	 *
	 * Returns all working hours of group members.
	 *
	 * @param $group_id
	 * @return array
	 */
	public static function getIndividualTime($group_id) {
		$code = 'individual_work_hour_' . $group_id . '_%';
		$rows = db_select('office_config', 'c')
			->fields('c')
			->condition('code', $code, 'LIKE')
			->execute()
			->fetchAllAssoc('code');

		$rets = [];
		if ( $rows ) {
			foreach( $rows as $code => $row ) {
				$e = self::processIndividualWorkSchedule($row);
				$rets[] = $e;
			}
		}
		return $rets;
	}

	/**
	 * @param $row - * fetch Object *
	 * @return array
	 *
	 */
	public static function processIndividualWorkSchedule($row) {
		$code = $row->code;
		$arr = explode('_', $code);
		$id = array_pop($arr);
		$user = User::load($id);
		$e = [];
		$e['idx'] = $row->idx;
		$e['code'] = $code;
		$e['user_id'] = $id;
		$e['user'] = $user;
		$e['date'] = $row->category;
		$e['value'] = $row->value;
		list($e['time'], $e['reason']) = explode(' ',$row->value, 2);
		list($e['begin'],$e['end']) = explode('-', $e['time']);
		return $e;
	}


	/**
	 * 그룹 개별 인원의 근무 시간을 리턴한다.
	 *
	 * 만약 $uid 가 0 이라면, 전체가 근무하는 시간이다.
	 *
	 *  - 예를 들어 일요일은 그룹이 원래 쉬는 날인데, 특별히 전체 인원이 근무를 하는 경우 등이다.
	 *
	 * @param $group_id
	 * @param $uid
	 * @param $date
	 * @return array
	 *
	 * - 리턴 값 예제
	 * array(
	[idx] => 21
	[code] => individual_work_hour_2_2
	[user_id] => 2
	[user] => Drupal\user\Entity\User Object // <============== 사용자 객체 정보를 리턴한다.
	[date] => 20150506
	[value] => 1500-2300 Evening shift
	[reason] => Evening shift
	[time] => 1500-2300
	[end] => 2300
	[begin] => 1500
	)
	 */
	public static function getMemberWorkSchedule($group_id, $uid, $date) {
		$code = "individual_work_hour_{$group_id}_$uid";

		$rows = db_select('office_config', 'c')
			->fields('c')
			->condition('code', $code)
			->condition('category', $date)
			->execute()
			->fetchAllAssoc('code');
		$row = [];
		if ( $rows ) {
			list($k, $v) = each($rows);
			$row = self::processIndividualWorkSchedule($v);
		}
		return $row;
	}

	public static function deleteIndividualTime($idx) {
		self::config_delete_by_idx($idx);
	}

	public static function messageLoginFirst(array & $data=null) {
		$data['code'] = 'error login-first';
		$data['message'] = 'Please Login First!';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	public static function messageSelectDate(array & $data=null) {
		$data['code'] = 'error select-date';
		$data['message'] = 'Select a day to display attendance list';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	public static function messageNotOfficeMember(array & $data=null) {
		$data['code'] = 'error not-a-member';
		$data['message'] = 'You (or the user) are not a member of office. Please join the office.';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	public static function messageNotGroupMember(array & $data=null) {
		$data['code'] = 'error not-a-member';
		$data['message'] = 'You (or the user) are not a member of any group. Please select a group first.';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	/**
	 *
	 * 참고 : 그룹 개별 근무
	 *
	 * 이 메소드는
	 *
	 *  - 그룹의 근무 시간 정보를 알고자 할 때,
	 *  - 그룹이 근무하는 날인지 안하는 날인지 체크를 할 때 사용 할 수 있다.
	 *  - 개인 별 근무 하는 날, 근무시간, 근무 하지 않는 날 정보는 이 함수를 통해서 얻을 수 없다.
	 *
	 *
	 *      1. 그룹의 개별 근무 시간 정보가 존재하는지 먼저 확인하고,
	 *          존재하면 그룹 개별 근무 시간 정보를 먼저 리턴한다.
	 *          참고: 그룹 개별 근무
	 *
	 *      2. 그룹 근무 하는 날이면 그룹 근무 시간 정보를 리턴한다.
	 *
	 *      3. 그룹이 근무하는 날이 아니면, 빈 배열을 리턴한다.
	 *
	 * - 예를 들어 아래와 같은 경우, 근무 시간 정보를 얻을 수 있다.
	 *
	 *      -- 일요일 쉬는데, 이번주 일요일은 특별히 전체 인원이 일요일 일하는지, 일하지 않는지,
	 *      -- 근무를 한다면, 근무 시간.
	 *
	 *      -- 수요일 일하는데, 이번주 수요일은 특별히 쉬는지.
	 *      -- 또는 근무 시간이 바뀌었는지.
	 *
	 *
	 * @param $group_id
	 * @param $date
	 *
	 *
	 *
	 * @return array
	 *
	 *      - 개인별 또는 그룹이 전체 쉬는 날이면, 빈 array 를 리턴한다.
	 *
	 *      - 리턴 예
	 *
	 * Array
	(
	[0] => 0900
	[1] => 1800
	)
	 */
	public static function getGroupWorkSchedule($group_id, $date) {

		// #3-3
		$work_schedule = self::getMemberWorkSchedule($group_id, 0, $date);

		if ( $work_schedule ) {
			return [
				$work_schedule['begin'],
				$work_schedule['end']
			];
		}


		// 그룹만의 dayoff 인가?
		// 그룹 정보 설정에서 쉬는 날로 지정 한 경우,
		$dayoff = self::getGroupDayoffs($group_id);
		if ( isset($dayoff[$date]) ) {
			// 그룹 쉬는 날이다.
			return [];
		}

		// 그룹이 원래 쉬는 날인가?
		//
		//
		$day = strtolower(date('l', strtotime($date)));
		$days = self::getGroupWorkingDays($group_id);
		if ( $days[$day] == 'Y' ) {
			// 그룹 근무 하는 날
			//
			$work_hours = self::getGroupWorkingHours($group_id);
			if ( empty($work_hours) ) {
				return [];
			}
			else return $work_hours; // 정상 근무 시간.
		}
		else {
			// 그룹 쉬는 날
		}
		return [];
	}



	/** @short return true if the stamp - $now is between the stamps of $begin and $end
	 *
	 *
	 *
	 *
	 */
	static function between( $now, $begin, $end ) {
		if ( $now < $begin ) return false;
		else if ( $now > $end ) return false;
		else return true;
	}



	/** @short returns stamp of date-time which is formatted by YYYYMMDDHHIISS.
	 *
	 * @note it must be 14 digits.
	 * @return unix timestamp
	 * YYYYMMDDHHIISS 값을 받아서 stamp 로 리턴한다.
	 *  converts human readable date to UNIX Timestamp which is formatted by YYYYMMDDHHIISS.
	 *
	 * 스케쥴에서 수업 시간의 stamp 를 편하게 구할 수 있다.
	 * @ex)
	$stamp = date_to_stamp("$row[date]$row[class_start]00");
	 */
	static function ymdhis($datetime)
	{
		$Y = substr($datetime, 0, 4);
		$m = substr($datetime, 4, 2);
		$d = substr($datetime, 6, 2);
		$h = substr($datetime, 8, 2);
		$i = substr($datetime, 10, 2);
		$s = substr($datetime, 12, 2);
		return mktime($h, $i, $s,$m,$d,$Y);
	}

	/**
	 *
	 *
	 * 사용자 정보 체크. 아래와 같은 저보를 체크한다.
	 *
	 *      - 로그인 했는지,
	 *
	 *      - office member 로 등록했는지,
	 *
	 *      - group 을 선택했는지
	 *
	 * @return array
	 *
	 *
	 *  - 에러가 없으면 빈 array
	 *  - 에러가 있으면, code 와 message 가 배열로 리턴된다.
	 *
	 * @code 예제코드: 아래와 같이 최대한 간단하게 사용한다.
	 *
			if ( $re = x::checkMember() ) {
				$data = array_merge($data, $re);
			}
	 *
	 * @endcode
	 */
	public static function checkMember($uid=0) {
		if ( ! x::login() ) {
			return x::messageLoginFirst();
		}
		if ( empty($uid) ) $uid = x::myUid();

		$member = Member::loadByUserID($uid);
		if ( empty($member) ) {
			return x::messageNotOfficeMember();
		}
		else if ( empty($member->get('group_id')->target_id) ) {
			return x::messageNotGroupMember();
		}

		$group = Group::load($member->get('group_id')->target_id);
		if ( empty($group) ) {
			return x::messageGroupNotExist();
		}

		return array();
	}

	private static function messageGroupNotExist() {
		return ['code'=>'error group-not-exist', 'message'=>'Your group does not exist. Maybe your group was deleted or maybe there is an error. Please go to Office member registration page and see if your group exists.'];
	}




	public static function getSuccessMessage($code) {
		switch( $code ) {
			case 'attend' : $message = 'You have attended successfully'; break;
		}
		return ['code'=>$code, 'message'=>$message];
	}

	/**
	 * 회원의 정보를 리턴한다.
	 *
	 *  - 그룹 등의 정보를 리턴한다.
	 *
	 * @todo 여기서 리턴하는 값은 모든 Page 에 적용되므로 문서화 한다.
	 *
	 * @return array
	 */
	public static function officeInformation() {
		$info = [];
		$info['member'] = Member::loadByUserID(x::myUid());
		$info['now'] = date('r');
		$group = Member::group(x::myUid());
		if ( $group ) {
			$info['group'] = $group;
			if ( $group->get('user_id')->target_id == x::myUid() ) $info['is_group_admin'] = 1;
		}
		return $info;
	}


	public static function messageNotYourGroup(array & $data=null) {
		$data['code'] = 'error not-your-group';
		$data['message'] = 'This is not your group. You are not the admin of the group.';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	public static function checkGroup($uid) {
		$member = Member::loadByUserID($uid);
		$group_id = $member->get('group_id')->target_id;
		if ( ! x::getGroupWorkingHours($group_id) ) return x::messageHasNoWorkingHours();
		$days = x::getGroupWorkingDays($group_id);
		foreach( $days as $day => $v ) {
			if ($v == 'Y') {
				return [];
			}
		}
		return x::messageHasNoWorkingDays();
	}

	private static function messageHasNoWorkingDays() {
		$data['code'] = 'error group-has-no-working-days';
		$data['message'] = 'The group has no working days through Sunday to Saturday.';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	private static function messageHasNoWorkingHours() {
		$data['code'] = 'error group-has-no-working-hours';
		$data['message'] = 'The group has no working hours. Please ask the group admin to set it.';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	public static function priority() {
		return Task::$config_priority;
	}


}