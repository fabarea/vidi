Feature: Frontend Group Backend Module
  In order to ensure the Frontend Group module works as expected
  As a BE Group
  I want to have the following scenario succeeding
  Notice: to skip the login step, the auto-login is provided by an extension such as "cc_iplogin_be"

  @javascript @load
  Scenario: Load the Frontend Group module
    Given I click the Frontend Group icon
    Then I should get a "200" status code
    And I should see the Grid