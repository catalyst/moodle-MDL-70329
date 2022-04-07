@qbank @qbank_viewquestionname @javascript
Feature: Use the qbank view page to edit question title
  using in place edit feature

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name          |
      | Course         | C1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name           | questiontext              |
      | Test questions   | truefalse | First question | Answer the first question |

  @javascript
  Scenario: Question title can be changed from the question bank view
    Given I log in as "teacher1"
    And I am on the "Test quiz" "quiz activity" page
    When I navigate to "Question bank" in current page administration
    And I set the field "Select a category" to "Test questions"
    And I set the field "Edit question name" in the "First question" "table_row" to "Edited question"
    Then I should not see "First question"
    And I should see "Edited question"

  @javascript
  Scenario: Teacher without permission can not change the title from question bank view
    Given I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability                  | permission |
      | moodle/question:editall     | Prevent    |
    And I log out
    Then I log in as "teacher1"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank" in current page administration
    And I set the field "Select a category" to "Test questions"
    And I should see "First question"
    And "Edit question name" "field" should not exist
