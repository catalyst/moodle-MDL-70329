@qbank @qbank_editquestion @javascript
Feature: Filter questions by status
  In order to choose question by status I see
  As a teacher
  I should be able to filter questions by status

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
      | questioncategory | qtype     | name            | questiontext              |
      | Test questions   | truefalse | First question  | Answer the first question |
      | Test questions   | truefalse | Second question | Answer the first question |

  Scenario: Questions can be filtered by status
    Given I log in as "admin"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank" in current page administration
    And I should see "Test questions"
    And I should see "Ready" in the "First question" "table_row"
    And I should see "Ready" in the "Second question" "table_row"
    And I click on "question_status_dropdown" "select" in the "First question" "table_row"
    And I click on "Draft" "option"
    And I reload the page
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Question status"
    And I set the field "questionstatus" in the "Filter 1" "fieldset" to "Draft"
    And I click on "Apply filters" "button"
    And I should see "First question"
    And I should not see "Second question"
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Question status"
    And I set the field "questionstatus" in the "Filter 1" "fieldset" to "Ready"
    And I click on "Apply filters" "button"
    Then I should see "Second question"
    And I should not see "First question"
