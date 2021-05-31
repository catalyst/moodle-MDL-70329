@qbank @qbank_viewquestiontype
Feature: Use the qbank plugin manager page
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

  @javascript
  Scenario: Enable/disable viewquestiontype column from the base view
    When I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    Then I should see "View question type question bank feature"
    And I click on "Disable" "link" in the "View question type question bank feature" "table_row"
    And I am on "Course 1" course homepage
    And I follow "Test quiz"
    And I navigate to "Question bank > Questions" in current page administration
    Then "#categoryquestions .header.qtype" "css_element" should not be visible
    And I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    And I click on "Enable" "link" in the "View question type question bank feature" "table_row"
    And I am on "Course 1" course homepage
    And I follow "Test quiz"
    And I navigate to "Question bank > Questions" in current page administration
    Then "#categoryquestions .header.qtype" "css_element" should be visible
