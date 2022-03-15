@qbank @qbank_managecategories @javascript
Feature: The questions in the question bank can be filtered by categories
  In order to find questions I need
  As a teacher
  I must be able to filter questions categories

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | weeks  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name                |
      | Course       | C1        | Test questions      |
      | Course       | C1        | Some other category |
      | Course       | C1        | Subcategory         |
    And the following "questions" exist:
      | questioncategory      | qtype     | name            | user     | questiontext    |
      | Test questions        | essay     | question 1 name | admin    | Question 1 text |
      | Some other category   | essay     | question 2 name | teacher1 | Question 2 text |
      | Subcategory           | essay     | question 3 name | teacher1 | Question 3 text |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  Scenario: The questions can be filtered by category filter
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "Any"
    And I set the field "type" in the "Filter 1" "fieldset" to "Category"
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Test questions"
    And I click on "Apply filters" "button"
    And I should see "question 1 name" in the "categoryquestions" "table"
    And I should not see "question 2 name" in the "categoryquestions" "table"
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "All"
    And I set the field "type" in the "Filter 1" "fieldset" to "Category"
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Some other category"
    And I click on "Apply filters" "button"
    And I should see "question 2 name" in the "categoryquestions" "table"
    And I should not see "question 1 name" in the "categoryquestions" "table"
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "None"
    And I set the field "type" in the "Filter 1" "fieldset" to "Category"
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Some other category"
    And I click on "Apply filters" "button"
    And I should see "question 1 name" in the "categoryquestions" "table"
    And I should not see "question 2 name" in the "categoryquestions" "table"

  Scenario: URL parameters are sharable and category filter can be retrieved
    And I add "Test questions" and "Course 1" with "Any" join type parameters to url and visit it
    And I should see "question 1 name" in the "categoryquestions" "table"
    And I should not see "question 2 name" in the "categoryquestions" "table"
    And I add "Some other category" and "Course 1" with "None" join type parameters to url and visit it
    And I should see "question 1 name" in the "categoryquestions" "table"
    And I should not see "question 2 name" in the "categoryquestions" "table"

  Scenario: Question subcategories can be properly filtered
    When I click on "jump" "select"
    And I click on "Categories" "option"
    And I click on "Make child of 'Some other category'" "link"
    And I click on "jump" "select"
    And I click on "Questions" "option"
    And I click on "Clear filters" "button"
    And I set the field "type" in the "Filter 1" "fieldset" to "Category"
    And I set the field "Type or select..." in the "Filter 1" "fieldset" to "Some other category"
    And I click on "Apply filters" "button"
    And I should see "question 2 name"
    And I should not see "question 3 name"
    And I click on "Add condition" "button"
    And I set the field "type" in the "Filter 2" "fieldset" to "Show questions subcategories"
    And I set the field "subcategories" in the "Filter 2" "fieldset" to "Yes"
    And I click on "Apply filters" "button"
    And I should see "question 2 name"
    And I should see "question 3 name"
