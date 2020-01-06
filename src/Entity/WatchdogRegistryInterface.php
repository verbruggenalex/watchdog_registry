<?php

namespace Drupal\watchdog_registry\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Watchdog registry entities.
 */
interface WatchdogRegistryInterface extends ConfigEntityInterface {

  public function getType();
  public function getMessage();
  public function getFunction();
  public function getFile();
  public function getLine();
}
