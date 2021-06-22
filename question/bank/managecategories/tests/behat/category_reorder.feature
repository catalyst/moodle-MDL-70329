@qbank @qbank_managecategories @javascript
Feature: A Teacher can reorder question categories
  In order to change question category order
  As a Teacher
  I need to reorder them

  Background:
    Given the following "users" exist:
        | username | firstname | lastname | email                |
        | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
        | fullname | shortname | format |
        | Course 1 | C1        | weeks  |
    And the following "categories" exist:
        | name  | category | idnumber |
        | Cat 1 | 0        | CAT1     |
    And the following "activities" exist:
        | activity | course | name           |
        | quiz     | C1     | Test quiz Q001 |
    And the following "course enrolments" exist:
        | user      | course | role           |
        | teacher1  | C1     | editingteacher |
    And the following "question categories" exist:
        | contextlevel    | reference      | name              |
        | Activity module | Test quiz Q001 | Quiz category     |
        | Activity module | Test quiz Q001 | Quiz category 2   |
        | Course          | C1             | Course category   |
        | Course          | C1             | Course category 2 |
        | Category        | CAT1           | Category cat      |
        | System          | S1             | System category   |
    And I am on the "Test quiz Q001" "quiz activity" page logged in as "admin"
    And I navigate to "Question bank > Categories" in current page administration

  @javascript
  Scenario: Teacher cannot move or delete single category under context
    When I click on "Default for Category 1" edit menu in the question category list
    Then I should not see "Delete"

  @javascript
  Scenario: Teacher can see complete edit menu if multiples categories exist under context
    When I click on "Quiz category" edit menu in the question category list
    Then I should see "Edit settings"
    And I should see "Delete"
    And I should see "Export as Moodle XML"

  @javascript
  Scenario: Teacher can reorder categories
    When I drag "Quiz category" and I drop it in "Default for Category 1" in the question category list
    Then "Quiz category" "text" should appear before "Default for Category 1" "text"
    And I drag "Course category" and I drop it in "System category" in the question category list
    Then "Course category" "text" should appear before "System category" "text"

  @javascript
  Scenario: Teacher can display and hide category descriptions
    When I click on "qbshowdescr" "checkbox"
    Then I should see "The default category for questions shared in context 'Category 1'."
    And I click on "qbshowdescr" "checkbox"
    And I should not see "The default category for questions shared in context 'Category 1'."

  @javascript
  Scenario: Teacher can add a new category
    When I click on "Add category" "button"
    And I click on "Parent category" "select"
    And I click on "Top for Category 1" "option"
    And I click on "Name" "field"
    And I type "A brand new category"
    And I set the field "Category info" to "A brand new description for a brand new category"
    And I set the field "ID number" to "12345"
    And I click on "Add category" button
    And I should see "A brand new category"
    And I should see "12345"
    And I should not see "A brand new description for a brand new category"
    And I click on "qbshowdescr" "checkbox"
    Then I should see "A brand new description for a brand new category"

  @javascript
  Scenario: Teacher cannot submit form if proper input are not entered
    When I click on "Add category" "button"
    And I click on "Add category" button
    Then I should see "- The category name cannot be blank."

  @javascript
  Scenario: Teacher cannot drag and drop a used idnumber in context
    And I click on "System category" edit menu in the question category list
    And I choose "Edit settings" in the open action menu
    And I set the field "ID number" to "c1used"
    And I click on "Edit category" button
    And I should see "c1used"
    And I click on "Course category 2" edit menu in the question category list
    And I choose "Edit settings" in the open action menu
    And I set the field "ID number" to "c1used"
    And I click on "Edit category" button
    And I drag "Course category 2" and I drop it in "System category" in the question category list
    Then "Course category 2" "text" should appear before "System category" "text"
    And I should see "ID number already in use, please change it to move or update category"

  @javascript
  Scenario: Non editing teacher cannot drag and drop or see editing menus
    When I am on the "Test quiz Q001" "quiz activity" page logged in as "teacher1"
    And I click on "Actions menu" "link"
    And I click on "Categories" "link"
    And I should not see "Add category"
    And I click on "Quiz category 2" edit menu in the question category list
    Then I should not see "Edit settings"
    And I should not see "Delete"
