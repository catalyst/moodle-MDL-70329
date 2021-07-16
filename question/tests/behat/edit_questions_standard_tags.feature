@core @core_question @javascript
Feature: The questions can be tagged
  In order to tag questions
  As  a teacher
  I want to see the standard tags in the tags field

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | weeks  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name             | intro                   | course | idnumber |
      | qbank      | Test qbank name  | Test qbank description  | C1     | qbank1   |
    And the following "question categories" exist:
      | contextlevel          | reference | name           |
      | Activity module       | qbank1    | Test questions |
    And the following "tags" exist:
      | name | isstandard |
      | foo  | 1          |
      | bar  | 1          |

  Scenario: The tags autocomplete should include standard tags
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test qbank name"
    And I select "Test questions" from the "category" singleselect
    And I press "Create a new question ..."
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I expand all fieldsets
    And I open the autocomplete suggestions list
    Then "foo" "autocomplete_suggestions" should exist
    And "bar" "autocomplete_suggestions" should exist
