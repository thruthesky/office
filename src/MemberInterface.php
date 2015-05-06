<?php
namespace Drupal\office;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
interface MemberInterface extends ContentEntityInterface {
	public static function loadByUserID($uid);
}
