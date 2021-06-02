@qbank @qbank_comment @javascript
Feature: A Teacher can comment in a question

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
      | contextlevel | reference | name           |
      | Course         | C1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name           | questiontext              |
      | Test questions   | truefalse | First question | Answer the first question |

  @javascript
  Scenario: Add a comment in question
    Given I log in as "teacher1"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank > Questions" in current page administration
    And I set the field "Select a category" to "Test questions"
    And I should see "0" on the "Comments" column
    When I click "0" on the row containing "Test questions"
    And I add "Super test comment 01" comment to question
    And I should see "Super test comment 01"
    And I click on "OK" "button" in the ".modal-dialog" "css_element"
    Then I should see "1" on the "Comments" column

  @javascript
  Scenario: Delete a comment from question
    Given I log in as "teacher1"
    And I am on the "Test quiz" "quiz activity" page
    And I navigate to "Question bank > Questions" in current page administration
    And I set the field "Select a category" to "Test questions"
    And I should see "0" on the "Comments" column
    When I click "0" on the row containing "Test questions"
    And I add "Super test comment 01 to be deleted" comment to question
    And I should see "Super test comment 01 to be deleted"
    And I click on "OK" "button" in the ".modal-dialog" "css_element"
    Then I should see "1" on the "Comments" column
    And I click "1" on the row containing "Test questions"
    And I delete "Super test comment 01 to be deleted" comment from question
    And I should not see "Super test comment 01 to be deleted"
    And I click on "OK" "button" in the ".modal-dialog" "css_element"
    But I should see "0" on the "Comments" column
