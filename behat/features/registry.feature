Feature: Watchdog Registry

  @api @javascript
  Scenario: Log a php message

    Given I log php message:
      | %type          | RuntimeException                                                                                                                                                 |
      | @message       | Failed to start the session because headers have already been sent by "/home/project/web/modules/contrib/watchdog_registry/watchdog_registry.module" at line 19. |
      | %function      | Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage->start()                                                                                   |
      | %file          | /home/project/vendor/symfony/http-foundation/Session/Storage/NativeSessionStorage.php                                                                            |
      | %line          | 150                                                                                                                                                              |
      | severity_level | 3                                                                                                                                                                |
    And I am logged in as a user with the "administrator" role
    When I visit "/admin/reports/dblog"
    Then I should see "RuntimeException: Failed to start the session" in the "php" row
    And I should see "Register" in the "RuntimeException: Failed to start the session" row
    When I click "Register" in the "RuntimeException: Failed to start the session" row
    And I fill in "id" with "bdd_testing_runtime_exeption"
    And I press "Save"
    Then I should see the success message "Created the : Watchdog registry."
    And I clean up watchdog registry items from behat
