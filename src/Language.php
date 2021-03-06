<?php
namespace Drupal\office;
use Symfony\Component\Yaml\Yaml;
class Language {
	private static $language = [];
	private static $text = [];
	/**
	 * Returns the language.
	 * @return array - language code and text
	 * @NOTE It caches on memory. You can call this function as many times as you want.
	 * @code
	 *      $ol = Language::load();
	 * @endcode
	 */
	public static function load() {
		if ( empty(self::$language) ) {
			$path_language = drupal_get_path('module', 'office') . '/office.language.yml';
			self::$language = Yaml::parse(file_get_contents($path_language));
			$lns = \Drupal::request()->getLanguages();
			if ( in_array('ko',$lns) ) {
				$ln = 'ko';
			}
			else {
				$ln = 'en';
			}
			foreach( self::$language as $name => $value ) {
				self::$text[$name] = $value[$ln];
			}
		}
		return self::$text;
	}
}