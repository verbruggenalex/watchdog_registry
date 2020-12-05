<?php

namespace Drupal\watchdog_registry\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Example configuration override.
 */
class ConfigOverrides implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'ConfigExampleOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];

    // @todo Replace fetching config with entityquery? But has a nesting level
    // problem.
    // @todo Replace files within message with absolute path.
    foreach ($names as $name) {
      if (mb_strpos($name, 'watchdog_registry.watchdog_registry.') !== FALSE) {
        // @todo Replace with dependency injection.
        // phpcs:disable DrupalPractice.Objects.GlobalDrupal.GlobalDrupal
        $watchdogRegistryFile = \Drupal::configFactory()->getEditable($name)->get('file');

        if (file_exists($watchdogRegistryFile)) {
          $overrides[$name] = ['file' => realpath($watchdogRegistryFile)];
        }
      }
    }

    return $overrides;
  }

}
