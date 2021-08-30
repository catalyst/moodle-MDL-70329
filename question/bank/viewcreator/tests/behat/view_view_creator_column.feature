@qbank @qbank_viewcreator
Feature: Use the qbank plugin manager page to show viewcreator plugin
  In order to check the plugin behaviour with enable and disable

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course         | C1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name           | questiontext              |
      | Test questions   | truefalse | First question | Answer the first question |
    And I log in as "admin"

  Scenario: Enable/disable viewcreator column from the base view
    When I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    Then I should see "View creator question bank feature"
    And I click on "Disable" "link" in the "View creator question bank feature" "table_row"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank > Questions" in current page administration
    Then I should not see "Created by"
    And I should not see "Last modified by"
    And I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    And I click on "Enable" "link" in the "View creator question bank feature" "table_row"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank > Questions" in current page administration
    Then I should see "Created by"
    And I should see "Last modified by"
