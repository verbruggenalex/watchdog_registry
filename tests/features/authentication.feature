Feature: User authentication
  In order to protect the integrity of the website
  As a product owner
  I want to make sure users with various roles can only access pages they are authorized to

  Scenario Outline: Anonymous user cannot access restricted pages
    Given I am not logged in
    When I go to "<path>"
    Then I should get an access denied error

    Examples:
      | path                                  |
      | admin/reports/dblog/watchdog_registry |

  @api
  Scenario Outline: Administrators can access pages they are authorized to
    Given I am logged in as a user with the "administrator" role
    Then I visit "<path>"

    Examples:
      | path                                  |
      | admin/reports/dblog/watchdog_registry |

  @api
  Scenario Outline: Authenticated user cannot access site administration
    Given I am logged in as a user with the "authenticated" role
    When I go to "<path>"
    Then I should get an access denied error

    Examples:
      | path                                  |
      | admin/reports/dblog/watchdog_registry |
