<?php
namespace Drupal\office;
use Drupal\office\Entity\Employee;
use Symfony\Component\Yaml\Yaml;

class x {
	const error_login_first = -1234;

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

	public static function employeeFormSubmit() {
		if ( ! self::login() ) {
			return self::error_login_first;
		}
		$eid = self::g('eid');
		if ( $eid ) {
			$employee = Employee::load($eid);
		}
		else {
			$employee = Employee::create();
		}
		$employee->set('name', self::g('name'));
		$employee->set('address', self::g('address'));
		$employee->set('user_id', x::myUid());
		$employee->save();
		return NULL;
	}

	private static function g($k) {
		return \Drupal::request()->get($k);
	}

	public static function myUid() {
		return \Drupal::currentUser()->getAccount()->id();
	}

	public static function login() {
		return self::myUid();
	}

	public static function errorMessage($re) {
		switch($re) {
			case self::error_login_first : return "Login ($re) : Please, login first";
			default: return "Unknown error ($re): ...";
		}
	}
}