@core @core_quiz @core_customfield @question_customfield @javascript
Feature: The visibility of question custom fields control where they are displayed
  In order to display custom fields in a quiz

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | weeks  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "custom field categories" exist:
      | name              | component     | area     | itemid |
      | Category for test | core_question | question | 0      |
    And the following "custom fields" exist:
      | name    | category          | type | shortname | configdata                                    |
      | Field 1 | Category for test | text | f1        | {"visibility":"2"}                            |
      | Field 2 | Category for test | text | f2        | {"visibility":"1"}                            |
      | Field 3 | Category for test | text | f3        | {"visibility":"0","defaultvalue":"secret"}    |
    And the following "activity" exists:
      | activity | quiz                  |
      | course   | C1                    |
      | idnumber | 00001                 |
      | name     | Test quiz name        |
      | intro    | Test quiz description |
      | section  | 1                     |
      | grade    | 10                    |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "True/False" question to the "Test quiz name" quiz with:
      | Question name                      | First question                          |
      | Question text                      | Answer the first question               |
      | General feedback                   | Thank you, this is the general feedback |
      | Correct answer                     | False                                   |
      | Feedback for the response 'True'.  | So you think it is true                 |
      | Feedback for the response 'False'. | So you think it is false                |
      | Field 1                            | Test custom field text                  |
      | Field 2                            | Test custom field2 text                 |
    Then I log out

  Scenario: Display custom question fields to students and teachers in a quiz
    When I am on the "Test quiz name" "mod_quiz > View" page logged in as teacher1
    And I press "Preview quiz now"
    Then I should see "Question 1"
    And I should see "Field 1"
    And I should see "Test custom field text"
    Then I log out
    When I am on the "Test quiz name" "mod_quiz > View" page logged in as student1
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I should see "Field 1"
    And I should see "Test custom field text"

  Scenario: Do not display question fields to students but show to teachers in a quiz
    When I am on the "Test quiz name" "mod_quiz > View" page logged in as teacher1
    And I press "Preview quiz now"
    Then I should see "Question 1"
    And I should see "Field 2"
    And I should see "Test custom field2 text"
    Then I log out
    When I am on the "Test quiz name" "mod_quiz > View" page logged in as student1
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I should not see "Field 2"
    And I should not see "Test custom field2 text"

  Scenario: Do not display question fields to students and teachers in a quiz
    When I am on the "Test quiz name" "mod_quiz > View" page logged in as teacher1
    And I press "Preview quiz now"
    Then I should see "Question 1"
    And I should not see "Field 3"
    And I should not see "secret"
    Then I log out
    When I am on the "Test quiz name" "mod_quiz > View" page logged in as student1
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I should not see "Field 3"
    And I should not see "secret"
