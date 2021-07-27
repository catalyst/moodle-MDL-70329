@core @core_question @javascript
Feature: An plugi column can be reordered and displayed in the question bank view.
  In order to reorganise the question bank view columns
  As a teacher
  I need to be able to reorder them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | weeks |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name                        | user  | questiontext                  | idnumber  |
      | Test questions   | essay | Test question to be deleted | admin | Write about whatever you want | numidnum</1 |
    And I log in as "admin"
    And I navigate to "Plugins > Question bank plugins > Column sort order" in site administration
    And I drag ".item" "css_element" and I drop it in ".list" "css_element"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank > Questions" in current page administration

  @javascript
  Scenario: Reordering question bank columns
    Then ".creatorname" "css_element" should appear before ".modifiername" "css_element"
