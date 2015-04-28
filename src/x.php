<?php
namespace Drupal\office;
use Drupal\office\Entity\Employee;
use Drupal\user\UserAuth;
use Symfony\Component\Yaml\Yaml;

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
		$error = [];
		if ($re) {
			$error['code'] = $re;
			$error['message'] = x::errorMessage($re);
		}
		$data['error'] = $error;
		return $re;
	}

	/**
	 *
	 * Creates/Updates Employee Form Submit
	 *
	 * @param array $data
	 * @return null|void
	 * - if there is no error, null is returned
	 * - if there is error, error code is return.
	 */
	public static function employeeFormSubmit(array &$data) {
		if ( ! self::login() ) return self::errorInfoArray(self::error_login_first, $data);

		/**
		 * @note do not return here, so the data will be saved.
		 */
		if ($re = self::validateEmployeeFormSubmit($data)) {
			// error
		}

		$eid = self::g('eid');
		if ( $eid ) {
			$employee = Employee::load($eid);
		}
		else {
			$employee = Employee::create();
		}
		$employee->set('first_name', self::g('first_name'));
		$employee->set('middle_name', self::g('middle_name'));
		$employee->set('last_name', self::g('last_name'));
		$employee->set('mobile', self::g('mobile'));
		$employee->set('landline', self::g('landline'));
		$employee->set('address', self::g('address'));
		$employee->set('user_id', self::myUid());
		$employee->set('group_id', self::g('group_id'));
		$employee->save();
		return NULL;
	}

	public static function g($k) {
		return \Drupal::request()->get($k);
	}

	public static function myUid() {
		return \Drupal::currentUser()->getAccount()->id();
	}

	public static function login() {
		return self::myUid();
	}

	public static function errorMessage($code) {
		$msg = self::getErrorMessageOnly($code);
		return "ERROR($code) : $msg";
	}
	public static function getErrorMessageOnly($code) {
		switch($code) {
			case self::error_login_first : return "Please, login first";
			case self::error_logged_in_already : return 'You are logged in already';
			case self::error_wrong_password : return 'You input wrong password';
			case self::error_wrong_id : return 'You input wrong id';
			case self::error_select_group : return "Please select a group";
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

	private static function input() {
		$request = \Drupal::request();
		$get = $request->query->all();
		$post = $request->request->all();
		return array_merge( $get, $post );
	}

	private static function validateEmployeeFormSubmit(array &$data) {
		$in = self::input();
		if ( empty($in['group_id']) ) return self::errorInfoArray(self::error_select_group, $data);
		if ( empty($in['first_name']) ) return self::errorInfoArray(self::error_input_first_name, $data);
		if ( empty($in['last_name']) ) return self::errorInfoArray(self::error_input_last_name, $data);
		if ( empty($in['middle_name']) ) return self::errorInfoArray(self::error_input_middle_name, $data);
		if ( empty($in['mobile']) ) return self::errorInfoArray(self::error_input_mobile, $data);
		if ( empty($in['landline']) ) return self::errorInfoArray(self::error_input_landline, $data);
		if ( empty($in['address']) ) return self::errorInfoArray(self::error_input_address, $data);
		return false;
	}
}