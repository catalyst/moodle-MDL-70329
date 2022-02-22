@mod @mod_qbank @javascript
Feature: Access qbank
  In order to use question banks
  As a teacher
  I need to be able to access question banks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name           | questiontext              |
      | Test questions   | truefalse | First question | Answer the first question |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activity" exists:
      | activity | quiz                  |
      | course   | C1                    |
      | name     | Test quiz name        |
    And the following "activity" exists:
      | activity | qbank                 |
      | course   | C1                    |
      | name     | Test qbank name       |

  Scenario: Access course question bank from quiz
    When I am on the "Test qbank name" "Activity" page logged in as "teacher1"
    And I click on "id_selectacategory" "select"
    And I should see "Default for Test qbank name"
    And I press "Create a new question ..."
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TFQ"
    And I set the field "Question text" to "This is a test question"
    And I press "id_submitbutton"
    And I am on the "Test quiz name" "Activity" page logged in as "teacher1"
    And I navigate to "Question bank" in current page administration
    And I should not see "TFQ"
    And I should see "First question"
    And I click on "id_selectacategory" "select"
    And I should see "Test qbank name"
    And I click on "Test qbank name" "option"
    And I should see "Test qbank name"
