<?php

/**
 * Contains \WatchdogRegistrySubContext.
 */

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use Behat\Gherkin\Node\TableNode;

/**
 * Watchdog Registry subcontext.
 */
class WatchdogRegistrySubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * The Drupal Context within this subcontext.
   *
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * The Mink Context within this subcontext.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  const WATCHDOG_REGISTRY_FLAG_PHP_NOTICES_OFF = 0;
  const WATCHDOG_REGISTRY_FLAG_PHP_NOTICES_PRINT = 1;
  const WATCHDOG_REGISTRY_FLAG_PHP_NOTICES_FAIL = 2;

  /**
   * Set the contexts within the scope.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * If we will flag PHP errors, clear all of them before we run a suite.
   *
   * @BeforeSuite
   */
  public static function clearPhpBehatNoticeLogs() {
    if (!empty(getenv('WATCHDOG_REGISTRY_FLAG_PHP_NOTICES'))) {
      db_delete('watchdog')
        ->condition('type', ['php', 'behat'], 'IN')
        ->execute();
    }
  }

  /**
   * Output any PHP notices that were logged in the scenario.
   *
   * @AfterScenario
   */
  public function flagPhpScenarioErrors(AfterScenarioScope $scope) {
    $flagPhp = getenv('WATCHDOG_REGISTRY_FLAG_PHP_NOTICES');
    if (!empty($flagPhp)) {
      $scenarioName = $scope->getFeature()->getTitle();
      $result = db_select('watchdog', 'w')
        ->fields('w', [])
        ->condition('w.type', 'php', '=')
        ->execute();

      $errors = [];
      foreach ($result as $entry) {
        $variables = unserialize($entry->variables);
        $time = date('Ymd-Hi', $entry->timestamp);
        $message = new FormattableMarkup($entry->message, $variables);
        $errors[] = "$scenarioName|$time|" . strip_tags($message);
      }

      if (!empty($errors)) {
        $message = implode("\r\n", $errors);
        // Clear the log for the next scenario.
        db_update('watchdog')
          ->fields(['type' => 'behat'])
          ->condition('type', 'php')
          ->execute();
        if ($flagPhp == self::WATCHDOG_REGISTRY_FLAG_PHP_NOTICES_FAIL) {
          throw new \Exception("PHP errors were logged. See scenario output for details.");
        }
      }
    }
  }

  /**
   * Fail the suite if any PHP notices are logged.
   *
   * @AfterSuite
   */
  public static function flagPhpSuiteErrors() {
    if (getenv('WATCHDOG_REGISTRY_FLAG_PHP_NOTICES') != self::WATCHDOG_REGISTRY_FLAG_PHP_NOTICES_OFF) {
      $number_of_rows = db_select('watchdog', 'w')
        ->fields('w', [])
        ->condition('w.type', 'behat', '=')
        ->countQuery()
        ->execute()
        ->fetchField();

      if ($number_of_rows > 0) {
        print "PHP errors were logged. See scenario output for details.\n";
      }
    }
  }

  /**
   * Clean up created entities.
   *
   * @Then I clean up watchdog registry items from behat
   */
  public function cleanupWatchdogRegistry() {
    // Maybe still need to find the specific cache to clear...
    $this->drupalContext->assertCacheClear();
    // Delete any entities that are prefixed by bdd_testing.
    $ids = \Drupal::entityQuery('watchdog_registry')->condition('id', 'bdd_testing', 'STARTS_WITH')->execute();
    $storage_handler = \Drupal::entityTypeManager()
      ->getStorage('watchdog_registry');
    $entities = $storage_handler
      ->loadMultiple($ids);
    $storage_handler
      ->delete($entities);
  }

  /**
   * Creates a watchdog php message with the following context.
   *
   * @codingStandardsIgnoreStart
   * | %type          | RuntimeException                                                                                                                                                 |
   * | @message       | Failed to start the session because headers have already been sent by "/home/project/web/modules/contrib/watchdog_registry/watchdog_registry.module" at line 19. |
   * | %function      | Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage->start()                                                                                   |
   * | %file          | /home/project/vendor/symfony/http-foundation/Session/Storage/NativeSessionStorage.php                                                                            |
   * | %line          | 150                                                                                                                                                              |
   * | severity_level | 3                                                                                                                                                                |.
   * @codingStandardsIgnoreEnd
   * All fields required.
   *
   * @Given I log php message:
   */
  public function iLogPhpMessage(TableNode $phpMessage) {
    $level = 3;
    $message = '%type: @message in %function (line %line of %file) @backtrace_string.';
    $context = [
      '@backtrace_string' => '',
      'channel' => 'php',
    ];
    foreach ($phpMessage->getRowsHash() as $key => $value) {
      $context[$key] = $value;
    }

    \Drupal::logger('watchdog_registry')->log($level, $message, $context);
  }

  /**
   * Checks that a 403 Access Denied error occurred.
   *
   * @Then I should get an access denied error
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   Thrown when a different HTTP response code was returned.
   */
  public function assertAccessDenied(): void {
    $this->drupalContext->assertSession()->statusCodeEquals(403);
  }

  /**
   * Checks that a 200 OK response occurred.
   *
   * @Then I should get a valid web page
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   Thrown when a different HTTP response code was returned.
   */
  public function assertSuccessfulResponse(): void {
    $this->drupalContext->assertSession()->statusCodeEquals(200);
  }

}
