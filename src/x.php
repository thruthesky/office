<?php
namespace Drupal\office;

use Drupal\office\Entity\Group;
use Drupal\office\Entity\Member;
use Drupal\office\Entity\Process;
use Drupal\office\Entity\Task;
use Drupal\user\Entity\User;
use Drupal\user\UserAuth;
use Symfony\Component\HttpFoundation\RedirectResponse;



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
	private static $input = [];

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

	/**
	 * Returns TRUE if the user is accessing office module.
	 *
	 * @return bool
	 *
	 */
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
		return self::getInput();
	}

	/**
	 *
	 * 입력 값을 임의로 지정한다.
	 *
	 * x::getInput() 과 x::in() 함수는 입력 값을 리턴한다.
	 *
	 * 하지만 이 함수를 통해서 입력 값을 임의로 지정하여 해당 함수들이 임의로 지정한 값을 사용 하게 할 수 있다.
	 *
	 * 예를 들면, 쿠키에 마지막 검색(폼 전송) 값을 저장해 놓고 다음에 접속 할 때 마지막에 지정한 검색 옵션을 그대로 적용하는 것이다.
	 *
	 *
	 * @param $array
	 */
	public static function setInput($array) {
		self::$input = $array;
	}

	/**
	 * self::$input 의 값을 리턴한다.
	 *
	 * @note 주의 할 점은 이 값은 꼭 HTTP 입력 값이 아닐 수 있다.
	 *
	 *      기본 적으로 HTTP 입력 값을 리턴하지만,
	 *
	 *      프로그램 적으로 임의로 이 값을 다르게 지정 할 수도 있다.
	 *
	 *      이 함수는 x::in() 에 영향을 미친다.
	 *
	 * @return array
	 */
	public static function getInput() {

		if ( empty(self::$input) ) {
			$request = \Drupal::request();
			$get = $request->query->all();
			$post = $request->request->all();
			self::$input = array_merge( $get, $post );
		}

		return self::$input;
	}

	/**
	 * 사용자 입력 값을 리턴한다.
	 *
	 * 만약 입력된 $parameter 에 해당하는 사용자 입력 값이 없다면 $default 를 리턴한다.
	 *
	 * 주의: 사용자 입력 값을 isset() 으로 검사한다. 따라서 변수가 지정되지 않았거나 null 인 경우 만 $default 값을 리턴한다.
	 *
	 * 입력 값이 0 인 경우는 0 을 리턴한다.
	 *
	 * @note this is a handy wrapper of \Drupal::request()
	 * @param $parameter - 사용자(웹브라우저) 입력 값 중에서 추출을 할 변수
	 * @param null|string $default - 사용자 입력 값이 없으면 리턴 할 기본 값
	 * @return mixed|null - 입력 값
	 */
	public static function in($parameter, $default='') {
		$re = self::getInput();
		if ( ! isset($re[$parameter]) ) return $default;
		return $re[$parameter];
	}




	public static function g($k, $default=null) {
		return \Drupal::request()->get($k, $default);
	}


	/**
	 *
	 * user_id 를 입력받아서 해당 entity 를 리턴한다.
	 *
	 * Entity 에 user_id 필드가 있으면 이 메소드를 사용 할 수 있다.
	 *
	 * @param $type
	 * @param $uid
	 * @return mixed|null
	 */
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
	 * - $code 에만 값이 있는 경우,
	 *     ==> 모든 $code 에 해당하는 값을 리턴한다.
	 * - $code 와 $category 에만 값이 있는 경우,
	 *     ==> $code 와 $category 에 해당하는 값을 모두 리턴한다.
	 * - $code 와 $category, $v 에 값이 있는 경우,
	 *      ==>> 추가를 하거나 업데이트를 한다.
	 *
	 *
	 *
	 *
	 * @param $code - 첫번째 키(키) 값
	 * @param $category - 두번째 키(카테고리) 값
	 * @param null $v - 값
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

		return null;//??
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
	 * 그룹 설정 값을 리턴한다.
	 *
	 * 그룹의 설정 값을 연관 배열로 통째로 모두 다 얻고 싶을 때 사용한다.
	 *
	 * 키값이 group_[그룹번호] 인 것만 리턴한다.group_[그룹번호]***** 와 같은 것은 리턴하지 않는다.
	 *
	 *
	 * @param $id - 그룹 번호
	 * @param null $category - 그룹 설정 키
	 * @return array - 그룹 설정의 키와 값의 연관 배열로 리턴한다.
	 *
	 *      -- 만약 $category 값이 null 이면, 전체 그룹 키 값을 리턴한다>
	 *
	 * @code 해당 그룹의 모든 설정 값을 얻어서 리턴한다.
	 *      $data['group_option'] = x::group_config($office_group->id());
	 * @endcode
	 */
	public static function group_config($id, $category=null) {
		$configs = self::config("group_{$id}", $category);
		$ret = [];
		if ( $configs ) {
			foreach ( $configs as $cfg ) {
				$ret[$cfg['category']] = $cfg['value'];
			}
		}
		return $ret;
		/*
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
		*/
	}

	/**
	 *
	 * 그룹의 설정을 저장한다.
	 *
	 *
	 *
	 * @param $id - 그룹 번호
	 * @param $category - 그룹 설정 키값
	 * @param $value - 값
	 * @return null
	 */
	public static function group_config_set($id, $category, $value) {
		return self::config("group_{$id}", $category, $value);
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
		$code = 'individual_work_hour_' . $group_id . '_';

		$rows = db_select('office_config', 'c')
			->fields('c')
			->condition('code', $code . '%', 'LIKE')
			->execute()
			->fetchAllAssoc('idx');

		$ret = [];
		if ( $rows ) {
			foreach( $rows as $code => $row ) {
				$e = self::processIndividualWorkSchedule($row);
				$ret[] = $e;
			}
		}
		return $ret;
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
			->fetchAllAssoc('idx');
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
		$ol = Language::load();
		$data['message'] = $ol['login_first'];
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


	public static function messageNotGroupMemberToAddIntoGroup(array & $data=null) {
		$data['code'] = 'error not-a-member-for-req';
		$data['message'] = 'The user is not office member. Please tell the user to register in office first.';
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


	/**
	 * @param $code
	 * @return array
	 */
	public static function getSuccessMessage($code) {
		switch( $code ) {
			case 'attend' : $message = 'You have attended'; break;
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
	 * @param $variables
	 * @return array
	 */
	public static function officeInformation(&$variables) {

		$office = [];
		$office['member'] = Member::loadByUserID(x::myUid());
		$office['now'] = date('r');


		// 나의 그룹. 내가 소속된 그룹. 내가 관리 가능한 그룹이 아니라, 나의 회사(그룹)을 말한다.
		// {{ office.group }} 을 지정하고 {{ office.is_member }} 를 지정한다.
		$office['group'] = Member::group(x::myUid());

		// 내가 TASK 관리 가능한 그룹
		$groups = entity_load_multiple_by_properties('office_groupmember', ['user_id' => x::myUid()]);
		if ( $groups ) {
			$office['is_member'] = 1;
			foreach( $groups as $group ) {
				$group->option = x::group_config($group->get('group_id')->target_id);
				$office['group_ids'][] = $group->get('group_id')->target_id;
				$office['groups'][] = $group;
			}
		}



		// 내가 관리자로 되어져 있는 그룹
		$my_own_group = Member::myOwnGroup(x::myUid()); $office['my_own_group'] = $my_own_group;

		/**
		 * {{ office.is_group_admin }} 을 지정한다.
		 */
		if ( isset($variables['groups']) ) {
			foreach( $variables['groups'] as $group ) {
				if ( $group->get('user_id')->target_id == x::myUid() ) $office['is_group_admin'] = 1;
			}
		}

		// 나만의 그룹 설정
		$rows = db_select('office_config', 'c')
			->fields('c')
			->condition('code', 'search')
			->condition('value', x::myUid() )
			->orderBy('idx', 'DESC')
			->range(0, 5)
			->execute()
			->fetchAllAssoc('category', \PDO::FETCH_ASSOC);

		$office['last_searches'] = array_keys($rows);

		$variables['office'] = $office;
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

	/**
	 * @param $k
	 * @param $v
	 * @refer the definition of user_cookie_save() and you will know.
	 */
	public static function set_cookie($k, $v) {
		user_cookie_save([$k=>$v]);
	}
	/**
	 * @param $k - is the key of the cookie.
	 * @return mixed
	 */
	public static function get_cookie($k) {
		return \Drupal::request()->cookies->get("Drupal_visitor_$k");
	}
	/**
	 * @param $k
	 */
	public static function delete_cookie($k) {
		user_cookie_delete($k);
	}

	public static function markupProcess($id) {
		$process = Process::load($id);
		if ( empty($process) ) return null;
		$summary = $process->get('summary')->value;
		$description = $process->get('description')->value;
		$workflow = $process->get('workflow')->value;
		$requirement = $process->get('requirement')->value;
		$html = "
				$summary
				$description
				$workflow
				$requirement
			";
		return $html;
	}


	/**
	 * Returns TRUE if the user is accessing office task page.
	 *
	 * @return bool
	 *
	 */
	public static function isOfficeTaskPage() {
		$request = \Drupal::request();
		$uri = $request->getRequestUri();
		if ( strpos( $uri, '/office/task') !== FALSE ) {
			return TRUE;
		}
		else return FALSE;
	}

	/**
	 * Returns TRUE if the user is accessing member page inside office module.
	 * @return bool
	 */
	public static function isOfficeMemberPage() {
		$request = \Drupal::request();
		$uri = $request->getRequestUri();
		if ( strpos( $uri, '/office/member') !== FALSE ) {
			return TRUE;
		}
		else return FALSE;
	}

	public static function isOfficeDocumentationPage() {
		$request = \Drupal::request();
		$uri = $request->getRequestUri();
		if ( strpos( $uri, '/office/documentation') !== FALSE ) {
			return TRUE;
		}
		else return FALSE;
	}

	public static function messageUserNotExist(&$data) {
		$data['code'] = 'error user-not-exist';
		$data['message'] = 'User is not exist by that name. Please check the user name. Wrong Username(ID)';
		return ['code'=>$data['code'], 'message'=>$data['message']];
	}

	public static function messageGroupUserAdded(array &$data) {
		$data['code'] = 'group-member-added';
		$data['message'] = 'Group member added';
	}

	public static function messageGroupUserAlreadyAdded(array &$data) {
		$data['code'] = 'error group-member-already-added';
		$data['message'] = 'Group member already added !';
	}

	public static function messageNotYourGroupMember(array &$data) {
		$data['code'] = 'error not-group-member';
		$data['message'] = 'The Group member is not your group member.';
	}

	public static function messageNotGroupMemberToDelete(array & $data=null) {
		$data['code'] = 'error not-a-member-to-delete';
		$data['message'] = 'The user is not group member.';
	}

	public static function messageGroupMemberDeleted(array & $data=null) {
		$data['code'] = 'group-member-deleted';
		$data['message'] = 'The member is removed from your group.';
	}

	public static function messageGroupUserDeleted(array &$data) {
		$data['code'] = 'group-user-deleted';
		$data['message'] = 'Group user deleted..';
	}

	public static function messageTaskFailedPrepareDirectory(array &$data) {
		$data['code'] = 'error task-file-upload-repository-creation-error';
		$data['message'] = 'Failed on creating file upload repository.';
		return -1;
	}

	public static function messageTaskFailedToUploadFile(array &$data) {
		$data['code'] = 'error task-failed-to-upload-file';
		$data['message'] = 'Failed to upload file.';
		return -1;
	}

	public static function myOfficeInformation(&$variables) {
		$my = Member::loadByUserID(x::myUid());
		$my->count_task = \Drupal::entityQuery('office_task')
			->condition('worker_id',x::myUid())
			->condition('status','closed', '<>')
			->count()
			->execute();

		$status = Task::$config_priority['immediate']['value'];
		$my->count_immediate_task = \Drupal::entityQuery('office_task')
			->condition('worker_id',x::myUid())
			->condition('status','closed', '<>')
			->condition('priority',$status)
			->count()
			->execute();
		$status = Task::$config_priority['urgent']['value'];
		$my->count_urgent_task = \Drupal::entityQuery('office_task')
			->condition('worker_id',x::myUid())
			->condition('status','closed', '<>')
			->condition('priority',$status)
			->count()
			->execute();
		$date = date('Y-m-d', strtotime('1 week ago'));
		$my->count_deadline_task = \Drupal::entityQuery('office_task')
			->condition('worker_id',x::myUid())
			->condition('status','closed', '<>')
			->condition('deadline',$date, '>')
			->count()
			->execute();

		$my->count_rejected_task = \Drupal::entityQuery('office_task')
			->condition('worker_id',x::myUid())
			->condition('status','rejected')
			->count()
			->execute();


		$db = \Drupal::entityQuery('office_task');
		$or = $db->orConditionGroup();
		$or->condition('worker_id', x::myUid());
		$or->condition('creator_id', x::myUid());
		$or->condition('in_charge_id', x::myUid());
		$my->count_recent_task = $db->condition($or)
			->condition('status','closed', '<>')
			->condition('changed', time()-24*60*60, '>')
			->count()
			->execute();


		$variables['my'] = $my;
	}


}