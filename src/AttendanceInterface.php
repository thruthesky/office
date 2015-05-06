<?php

/**
 * @file
 * Contains Drupal\office\AttendanceInterface.
 */

namespace Drupal\office;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Attendance entity.
 *
 * @ingroup office
 */
interface AttendanceInterface extends ContentEntityInterface, EntityOwnerInterface {
	// Add get/set methods for your configuration properties here.

}
