@core @core_question @core_customfield @question_customfield @javascript
Feature: A teacher can edit question with custom fields
  In order to improve my questions
  As a teacher
  I need to be able to edit questions and add extra metadata via custom fields

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | weeks  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name                       | questiontext                  |
      | Test questions   | essay | Test question to be edited | Write about whatever you want |
    And the following "custom field categories" exist:
      | name              | component     | area     | itemid |
      | Category for test | core_question | question | 0      |
    And the following "custom fields" exist:
      | name    | category          | type | shortname |
      | Field 1 | Category for test | text | f1        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank > Questions" in current page administration

  Scenario: Edit a previously created question and see the custom field in the preview.
    When I choose "Edit question" action for "Test question to be edited" in the question bank
    Then I should see "Category for test"
    And I click on "Expand all" "link"
    And I should see "Field 1"
    And I set the following fields to these values:
      | Field 1 | custom field text |
    And I press "id_submitbutton"
    Then I should see "Test question to be edited"
    And I choose "Preview" action for "Test question to be edited" in the question bank
    Then I should see "Field 1"
    And I should see "custom field text"

  Scenario: Preview a previously created question with custom fields set with empty values
    When I choose "Preview" action for "Test question to be edited" in the question bank
    Then I should not see "Field 1"
