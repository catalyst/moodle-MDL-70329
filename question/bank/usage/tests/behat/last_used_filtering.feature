@qbank @qbank_usage @javascript
Feature: Filter questions by usage
  In order to choose question by usage I see
  As a teacher
  I should be able to filter questions by use

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
      | questioncategory | qtype     | name            | questiontext               |
      | Test questions   | truefalse | First question  | Answer the first question  |
      | Test questions   | truefalse | Second question | Answer the second question |
      | Test questions   | essay     | Third question  | Answer the third question  |
    And the following "activities" exist:
      | activity | name           | course | idnumber |
      | quiz     | Test quiz name | C1     | quiz1    |
      | quiz     | Quiz 2         | C1     | quiz2    |
    And quiz "Test quiz name" contains the following questions:
      | question        | page |
      | First question  | 1    |
      | Second question | 1    |
    And quiz "Quiz 2" contains the following questions:
      | question        | page |
      | First question  | 1    |

  Scenario: Questions can be filtered by usage
    Given I log in as "admin"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank" in current page administration
    And I should see "Test questions"
    And I should see "2" in the "First question" "table_row"
    And I should see "1" in the "Second question" "table_row"
    And I should see "0" in the "Third question" "table_row"
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Usage"
    And I set the field "lastused" in the "Filter 1" "fieldset" to "Last used"
    And I click on "Apply filters" "button"
    And I should see "First question"
    And I should see "Second question"
    And I should not see "Third question"
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Usage"
    And I set the field "lastused" in the "Filter 1" "fieldset" to "Not used"
    And I click on "Apply filters" "button"
    Then I should see "Third question"
    And I should not see "First question"
    And I should not see "Second question"
