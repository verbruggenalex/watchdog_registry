<?php

namespace Drupal\watchdog_registry\Logger;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

class WatchdogRegistryLogger implements LoggerInterface {
  use RfcLoggerTrait;
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * Constructs a Watchdog Logger object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LogMessageParserInterface $parser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->parser = $parser;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {

    if (isset($context['channel']) && $context['channel'] === 'php') {
      $message_placeholders = $this->parser->parseMessagePlaceholders($message, $context);
  
      $wr = \Drupal::entityTypeManager()
        ->getStorage('watchdog_registry')
        ->loadByProperties([
          'type' => $message_placeholders['%type'],
          // 'message' => $message_placeholders['@message'],
          'function' => $message_placeholders['%function'],
          'file' => $message_placeholders['%file'],
          'line' => $message_placeholders['%line'],
        ]);
      if (empty($wr) === true) {
        // TODO: Some kind of action to notify developer of new watchdog entry.
      }
    }
  }

}