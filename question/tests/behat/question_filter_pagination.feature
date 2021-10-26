@core @core_question @qbank_filter
Feature: A teacher can pagimate through question bank questions
  In order to paginate questions
  As a teacher
  I must be able to paginate

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
      | contextlevel | reference | questioncategory | name           |
      | Course       | C1        | Top              | Used category  |
    And the following "questions" exist:
      | questioncategory | qtype | name                        | questiontext       |
      | Used category    | essay | Tests question 1 | Write about whatever you want |
      | Used category    | essay | Tests question 2 | Write about whatever you want |
      | Used category    | essay | Tests question 3 | Write about whatever you want |
      | Used category    | essay | Tests question 4 | Write about whatever you want |
      | Used category    | essay | Tests question 5 | Write about whatever you want |
      | Used category    | essay | Tests question 6 | Write about whatever you want |
      | Used category    | essay | Tests question 7 | Write about whatever you want |
      | Used category    | essay | Tests question 8 | Write about whatever you want |
      | Used category    | essay | Tests question 9 | Write about whatever you want |
      | Used category    | essay | Tests question 10 | Write about whatever you want |
      | Used category    | essay | Tests question 11 | Write about whatever you want |
      | Used category    | essay | Tests question 12 | Write about whatever you want |
      | Used category    | essay | Tests question 13 | Write about whatever you want |
      | Used category    | essay | Tests question 14 | Write about whatever you want |
      | Used category    | essay | Tests question 15 | Write about whatever you want |
      | Used category    | essay | Tests question 16 | Write about whatever you want |
      | Used category    | essay | Tests question 17 | Write about whatever you want |
      | Used category    | essay | Tests question 18 | Write about whatever you want |
      | Used category    | essay | Tests question 19 | Write about whatever you want |
      | Used category    | essay | Tests question 20 | Write about whatever you want |
      | Used category    | essay | Not on first page | Write about whatever you want |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Questions can be paginated
    When I navigate to "Question bank" in current page administration
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Course 1"
    And I click on "Apply filters" "button"
    And I should see "Tests question 1"
    And I should not see "Not on first page"
    And I click on "2" "link"
    And I should see "Not on first page"
