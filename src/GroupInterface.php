<?php

/**
 * @file
 * Contains Drupal\office\GroupInterface.
 */

namespace Drupal\office;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Group entity.
 *
 * @ingroup office
 */
interface GroupInterface extends ContentEntityInterface, EntityOwnerInterface {
	// Add get/set methods for your configuration properties here.

}
