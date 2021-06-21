@core @core_question @javascript
Feature: A teacher can put questions with idnumbers in categories in the question bank
  In order to organize my questions
  As a teacher
  I move questions between categories (now with idnumbers)

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
    And the following "activities" exist:
      | activity   | name             | intro                   | course | idnumber |
      | qbank      | Test qbank name  | Test qbank description  | C1     | qbank1   |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  Scenario: A new question category can only be created with a unique idnumber for a context
    # Note need to create the top category each time.
    When the following "question categories" exist:
      | contextlevel    | reference | questioncategory | name           | idnumber |
      | Activity module | qbank1    | Top              | top            |          |
      | Activity module | qbank1    | top              | Used category  | c1used   |
    And I follow "Test qbank name"
    And I click on "jump" "select"
    And I click on "Categories" "option"
    And I set the following fields to these values:
      | Name            | Sub used category |
      | Parent category | Used category     |
      | Category info   | Created as a test |
      | ID number       | c1used            |
    And I click on "id_submitbutton" "button"
    # Standard warning.
    Then I should see "This ID number is already in use"
    # Correction to a unique idnumber for the context.
    And I set the field "ID number" to "c1unused"
    And I click on "id_submitbutton" "button"
    Then I should see "Sub used category"
    And I should see "c1unused"
    And I should see "(0)"
    And I should see "Created as a test" in the "Sub used category" "list_item"

  Scenario: A question category can be edited and saved without changing the idnumber
    When the following "question categories" exist:
      | contextlevel    | reference | questioncategory | name           | idnumber |
      | Activity module | qbank1    | Top              | top            |          |
      | Activity module | qbank1    | top              | Used category  | c1used   |
    And I follow "Test qbank name"
    And I click on "jump" "select"
    And I click on "Categories" "option"
    And I click on "Edit this category" "link" in the "Used category" "list_item"
    And I press "Save changes"
    Then I should not see "This ID number is already in use"

  @javascript
  Scenario: A question can only have a unique idnumber within a category
    When the following "question categories" exist:
      | contextlevel    | reference | questioncategory | name           | idnumber |
      | Activity module | qbank1    | Top              | top            |          |
      | Activity module | qbank1    | top              | Used category  | c1used   |
    And the following "questions" exist:
      | questioncategory | qtype | name            | questiontext                  | idnumber |
      | Used category    | essay | Test question 1 | Write about whatever you want | q1       |
      | Used category    | essay | Test question 2 | Write about whatever you want | q2       |
    And I navigate to "Question bank" in current page administration
    And I follow "Test qbank name"
    And I set the field "Select a category" to "Used category"
    And I choose "Edit question" action for "Test question 2" in the question bank
    And I set the field "ID number" to "q1"
    And I press "submitbutton"
    # This is the standard form warning reminding the user that the idnumber needs to be unique for a category.
    Then I should see "This ID number is already in use"

  @javascript
  Scenario: A question can be edited and saved without changing the idnumber
    When the following "question categories" exist:
      | contextlevel    | reference | questioncategory | name           | idnumber |
      | Activity module | qbank1    | Top              | top            |          |
      | Activity module | qbank1    | top              | Used category  | c1used   |
    And the following "questions" exist:
      | questioncategory | qtype | name            | questiontext                  | idnumber |
      | Used category    | essay | Test question 1 | Write about whatever you want | q1       |
    And I navigate to "Question bank" in current page administration
    And I follow "Test qbank name"
    And I set the field "Select a category" to "Used category"
    And I choose "Edit question" action for "Test question 1" in the question bank
    And I press "Save changes"
    Then I should not see "This ID number is already in use"

  @javascript
  Scenario: Question idnumber conflicts found when saving to a different category.
    When the following "question categories" exist:
      | contextlevel    | reference | questioncategory | name       |
      | Activity module | qbank1    | Top              | top        |
      | Activity module | qbank1    | top              | Category 2 |
    And the following "questions" exist:
      | questioncategory | qtype | name             | questiontext                  | idnumber |
      | Category 2       | essay | Question to edit | Write about whatever you want | q1       |
      | Category 2       | essay | Other question   | Write about whatever you want | q2       |
    And I navigate to "Question bank" in current page administration
    And I follow "Test qbank name"
    And I set the field "Select a category" to "Category 2"
    And I choose "Edit question" action for "Question to edit" in the question bank
    And I set the field "ID number" to "q2"
    And I press "Save changes"
    Then I should see "This ID number is already in use"

  @javascript
  Scenario: Moving a question between categories can force a change to the idnumber
    And the following "question categories" exist:
      | contextlevel    | reference | questioncategory | name           | idnumber |
      | Activity module | qbank1    | Top              | top            |          |
      | Activity module | qbank1    | top              | Subcategory    | c1sub    |
      | Activity module | qbank1    | top              | Used category  | c1used   |
    And the following "questions" exist:
      | questioncategory | qtype | name            | questiontext                  | idnumber |
      | Used category    | essay | Test question 1 | Write about whatever you want | q1       |
      | Used category    | essay | Test question 2 | Write about whatever you want | q2       |
      | Subcategory      | essay | Test question 3 | Write about whatever you want | q3       |
    When I navigate to "Question bank" in current page administration
    When I follow "Test qbank name"
    And I set the field "Select a category" to "Subcategory"
    And I choose "Edit question" action for "Test question 3" in the question bank
    # The q1 idnumber is allowed for this question while it is in the Subcategory.
    And I set the field "ID number" to "q1"
    And I press "submitbutton"
    # Javascript is required for the next step.
    And I click on "Test question 3" "checkbox" in the "Test question 3" "table_row"
    And I click on "With selected" "button"
    And I click on question bulk action "move"
    And I set the field "Question category" to "Subcategory"
    And I press "Move to"
    And I choose "Edit question" action for "Test question 3" in the question bank
    # The question just moved into this category needs to have a unique idnumber, so a number is appended.
    Then the field "ID number" matches value "q1_1"
