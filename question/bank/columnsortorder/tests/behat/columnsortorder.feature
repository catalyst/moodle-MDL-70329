@core @qbank_columnsortorder @javascript
Feature: An plugin column can be reordered and displayed in the question bank view.
  In order to reorganise the question bank view columns
  As an admin
  I need to be able to reorder them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | weeks  |
    And the following "activity" exists:
      | activity | quiz           |
      | course   | C1             |
      | name     | Test quiz Q001 |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |
    And the following "question category" exist:
      | contextlevel    | reference      | name                |
      | Activity module | Test quiz Q001 | Question category 1 |
    And the following "questions" exist:
      | questioncategory    | qtype | name                     | user     | questiontext                  | idnumber  |
      | Question category 1 | essay | Test question to be seen | teacher1 | Write about whatever you want | idnumber1 |

  @javascript
  Scenario: Teacher can see proper view
    Given I am on the "Test quiz Q001" "quiz activity" page logged in as "teacher1"
    When I navigate to "Question bank > Questions" in current page administration
    And I click on "category" "select"
    And I click on "Question category 1" "option"
    And I should see "Test question to be seen"
    Then I should see "Teacher 1"

  @javascript
  Scenario: Reordering question bank columns
    Given I log in as "admin"
    When I navigate to "Plugins > Question bank plugins > Column sort order" in site administration
    And I drag "Creatorname (creator_name_column)" "text" and I drop it in "Qtype (question_type_column)" "text"
    And I am on the "Test quiz Q001" "quiz activity" page logged in as "teacher1"
    And I navigate to "Question bank > Questions" in current page administration
    And I click on "category" "select"
    And I click on "Question category 1" "option"
    Then ".creatorname" "css_element" should appear before ".qtype" "css_element"

  @javascript
  Scenario: Disabling a column removes column from sortcolumn page
    Given I log in as "admin"
    When I navigate to "Plugins > Question bank plugins > Column sort order" in site administration
    And I should see "Creatorname (creator_name_column)"
    And I click on "Manage question bank plugins" "link"
    And I click on "Disable" "link" in the "View creator" "table_row"
    And I click on "Column sort order" "link"
    Then I should not see "Creatorname (creator_name_column)"
