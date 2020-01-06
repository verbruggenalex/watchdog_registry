<?php

namespace Drupal\watchdog_registry\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Watchdog registry entities.
 */
interface WatchdogRegistryInterface extends ConfigEntityInterface {

  /**
   * Get the file in which the log message occurs.
   */
  public function getFile();

  /**
   * Get the function in which the log message occurs.
   */
  public function getFunction();

  /**
   * Get the line in the file in which the log message occurs.
   */
  public function getLine();

  /**
   * Get the message of the log message.
   */
  public function getMessage();

  /**
   * Get the type of the message.
   */
  public function getType();

}
