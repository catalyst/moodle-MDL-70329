@qbank @qbank_deletequestion @javascript
Feature: Use the qbank plugin manager page for deletequestion
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
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name                  | questiontext              |
      | Test questions   | truefalse | First question        | Answer the first question |
      | Test questions   | truefalse | Second question       | Answer the first question |
    Given I log in as "admin"

  Scenario: Enable/disable delete question column from the base view
    When I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    And I should see "Delete question"
    And I click on "Disable" "link" in the "Delete question" "table_row"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank > Questions" in current page administration
    And I click on ".dropdown-toggle" "css_element" in the "First question" "table_row"
    Then I should not see "Delete" in the "region-main" "region"
    And I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    And I click on "Enable" "link" in the "Delete question" "table_row"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank > Questions" in current page administration
    And I click on ".dropdown-toggle" "css_element" in the "First question" "table_row"
    And I should see "Delete" in the "region-main" "region"

  Scenario: Enable/disable delete questions bulk action from the base view
    When I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    And I should see "Delete question"
    And I click on "Disable" "link" in the "Delete question" "table_row"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank > Questions" in current page administration
    And I click on "With selected" "button"
    Then I should not see question bulk action "deleteselected"
    And I navigate to "Plugins > Question bank plugins > Manage question bank plugins" in site administration
    And I click on "Enable" "link" in the "Delete question" "table_row"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank > Questions" in current page administration
    And I click on "With selected" "button"
    And I should see question bulk action "deleteselected"

  Scenario: I should not see the deleted questions in the base view
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank > Questions" in current page administration
    And I click on "Select all" "checkbox"
    And I click on "With selected" "button"
    And I click on question bulk action "deleteselected"
    And I click on "Delete" "button" in the "Confirm" "dialogue"
    Then I should not see "First question"
    And I should not see "Second question"

  Scenario: Questions bank can display and filter delete/hidden questions
    Given quiz "Test quiz" contains the following questions:
      | question       | page |
      | First question | 1    |
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Category"
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Test questions"
    And I click on "Apply filters" "button"
    And I choose "Delete" action for "First question" in the question bank
    And I press "Delete"
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Category"
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Test questions"
    And I click on "Apply filters" "button"
    And I should not see "First question"
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Category"
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Test questions"
    And I click on "Add condition" "button"
    And I set the field "type" in the "Filter 2" "fieldset" to "Show old questions"
    And I set the field "hidden" in the "Filter 2" "fieldset" to "Yes"
    And I click on "Apply filters" "button"
    And I should see "First question"
