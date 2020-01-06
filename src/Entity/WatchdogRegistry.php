<?php

namespace Drupal\watchdog_registry\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Watchdog registry entity.
 *
 * @ConfigEntityType(
 *   id = "watchdog_registry",
 *   label = @Translation("Watchdog registry"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\watchdog_registry\WatchdogRegistryListBuilder",
 *     "form" = {
 *       "add" = "Drupal\watchdog_registry\Form\WatchdogRegistryForm",
 *       "edit" = "Drupal\watchdog_registry\Form\WatchdogRegistryForm",
 *       "delete" = "Drupal\watchdog_registry\Form\WatchdogRegistryDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\watchdog_registry\WatchdogRegistryHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "watchdog_registry",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/reports/dblog/watchdog_registry/{watchdog_registry}",
 *     "add-form" = "/admin/reports/dblog/watchdog_registry/add",
 *     "edit-form" = "/admin/reports/dblog/watchdog_registry/{watchdog_registry}/edit",
 *     "delete-form" = "/admin/reports/dblog/watchdog_registry/{watchdog_registry}/delete",
 *     "collection" = "/admin/reports/dblog/watchdog_registry"
 *   }
 * )
 */
class WatchdogRegistry extends ConfigEntityBase implements WatchdogRegistryInterface {

  /**
   * The Watchdog registry ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Watchdog registry label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Watchdog registry type.
   *
   * @var string
   */
  protected $type;

  /**
   * The Watchdog registry message.
   *
   * @var string
   */
  protected $message;

  /**
   * The Watchdog registry function.
   *
   * @var string
   */
  protected $function;

  /**
   * The Watchdog registry file.
   *
   * @var string
   */
  protected $file;

  /**
   * The Watchdog registry line.
   *
   * @var int
   */
  protected $line;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->type . ': ' . $this->message;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }
 
  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunction() {
    return $this->function;
  }
 
  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->file;
  }

  /**
   * {@inheritdoc}
   */
  public function getLine() {
    return $this->line;
  }

}
