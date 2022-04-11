@qbank @qbank_managecategories @question_categories @javascript
Feature: A teacher can put questions in categories in the question bank
  In order to organize my questions
  As a teacher
  I create and edit categories and move questions between them

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
      | contextlevel | reference | questioncategory | name           |
      | Course       | C1        | Top              | top            |
      | Course       | C1        | top              | Default for C1 |
      | Course       | C1        | Default for C1   | Subcategory    |
      | Course       | C1        | top              | Used category  |
    And the following "questions" exist:
      | questioncategory | qtype | name                      | questiontext                  |
      | Used category    | essay | Test question to be moved | Write about whatever you want |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I select "Categories" from the "Question bank tertiary navigation" singleselect

  Scenario: A new question category can be created
    And I press "Add category"
    And I set the following fields to these values:
      | Name            | New Category 1    |
      | Parent category | Top               |
      | Category info   | Created as a test |
      | ID number       | newcatidnumber    |
    And I click on "Add category" "button" in the "Add category" modal
    And I click on "Show descriptions" "checkbox"
    Then I should see "New Category 1"
    And I should see "ID number"
    And I should see "newcatidnumber"
    And I should see "(0)"
    And I should see "Created as a test"
    And "New Category 1" "list_item" should exist in the "Question categories for 'Course: Course 1'" "fieldset"

  Scenario: A question category can be edited
    Then I click on "Edit" "text" in the "Default for C1" "list_item"
    And I choose "Edit settings" in the open action menu
    And I set the field "Name" to "New name"
    And I set the field "Category info" to "I was edited"
    And I click on "Edit category" "button" in the "Edit category" modal
    And I click on "Show descriptions" "checkbox"
    Then I should see "New name"
    And I should see "I was edited"

  Scenario: An empty question category can be deleted
    Then I click on "Edit" "text" in the "Default for C1" "list_item"
    And I choose "Delete" in the open action menu
    Then I should not see "Default for C1"

  Scenario: An non-empty question category can be deleted if you move the contents elsewhere
    When I click on "Edit" "text" in the "Used category" "list_item"
    And I choose "Delete" in the open action menu
    And I should see "The category 'Used category' contains 1 questions"
    And I press "Save in category"
    Then I should not see "Used category"
    And I should see "Default for C1"
    And I should see "(1)"

  @_file_upload
  Scenario: Multi answer questions with their child questions can be moved to another category when the current category is deleted
    When I navigate to "Question bank" in current page administration
    And I select "Import" from the "Question bank tertiary navigation" singleselect
    And I set the field "id_format_xml" to "1"
    And I upload "question/format/xml/tests/fixtures/multianswer.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    And I press "Continue"
    And I select "Categories" from the "Question bank tertiary navigation" singleselect
    And I click on "Delete" "link" in the "Default for Test images in backup" "list_item"
    And I should see "The category 'Default for Test images in backup' contains 1 questions"
    And I select "Used category" from the "Category" singleselect
    And I press "Save in category"
    Then I should not see "Default for Test images in backup"
    And I follow "Add category"
    And I should see "Used category (2)"
