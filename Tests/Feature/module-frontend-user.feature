Feature: Frontend User Backend Module
  In order to ensure the Frontend User module works as expected
  As a BE User
  I want to have the following scenario succeeding
  Notice: to skip the login step, the auto-login is provided by an extension such as "cc_iplogin_be"

  @javascript @load
  Scenario: Load the Frontend User module
    Given I click the Frontend User icon
    Then I should get a "200" status code
    And I should see the Grid