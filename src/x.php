<?php
namespace Drupal\office;
use Symfony\Component\Yaml\Yaml;

class x {
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

}