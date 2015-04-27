<?php

/**
 * @file
 * Contains Drupal\office\ClientInterface.
 */

namespace Drupal\office;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Client entity.
 *
 * @ingroup office
 */
interface ClientInterface extends ContentEntityInterface, EntityOwnerInterface {
	// Add get/set methods for your configuration properties here.

}
