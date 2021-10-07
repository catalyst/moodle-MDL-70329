@mod @mod_quiz
Feature: Quiz question versioning
  In order to manage question versions
  As a teacher
  I need to be able to choose which versions can be displayed in a quiz

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "activities" exist:
      | activity   | name   | course | idnumber |
      | quiz       | Quiz 1 | C1     | quiz1    |
    And the following "questions" exist:
      | questioncategory | qtype     | name           | questiontext              | answer 1 |
      | Test questions   | truefalse | First question | Answer the first question | True     |
    And quiz "Quiz 1" contains the following questions:
      | question          | page |
      | First question    | 1    |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Selecting approriate question versions
    When I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And I navigate to "Edit quiz" in current page administration
    And I should see "First question"
    And I should see "Answer the first question"
    And I should see "v1 (latest)"
    And I click on "Preview question" "link"
    And I switch to "questionpreview" window
    And I should see "Answer the first question"
    And I set the field "id_feedback" to "Not shown"
    And I set the field "id_generalfeedback" to "Not shown"
    And I set the field "id_rightanswer" to "Shown"
    And I press "id_saveupdate"
    And I click on "finish" "button"
    And I should see "The correct answer is 'True'."
    And I switch to the main window
    And I click on "Edit question First question" "link"
    And I set the field "id_name" to "Second question"
    And I set the field "id_questiontext" to "This is the second question text"
    And I set the field "id_correctanswer" to "False"
    And I press "id_submitbutton"
    And I set the field "version" to "v2"
    And I should see "Second question"
    And I should see "This is the second question text"
    And I click on "Preview question" "link"
    And I switch to "questionpreview" window
    And I should see "This is the second question text"
    And I set the field "id_feedback" to "Not shown"
    And I set the field "id_generalfeedback" to "Not shown"
    And I set the field "id_rightanswer" to "Shown"
    And I press "id_saveupdate"
    And I click on "finish" "button"
    Then I should see "The correct answer is 'False'."
