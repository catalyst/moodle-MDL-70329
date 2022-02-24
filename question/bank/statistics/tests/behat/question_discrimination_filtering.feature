@qbank @qbank_statistics
Feature: The questions in the question bank can be filtered by discrimination index
  In order to find questions that need revision
  As a teacher
  I must be able to filter questions by discrimination index

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | Student   | 3        | student3@example.com |
      | student4 | Student   | 4        | student4@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext    |
      | Test questions   | truefalse   | TF1   | First question  |
      | Test questions   | truefalse   | TF2   | Second question |
      | Test questions   | truefalse   | TF3   | Third question  |
      | Test questions   | truefalse   | TF4   | Fourth question |
    And the following "activities" exist:
      | activity   | name   | course | idnumber |
      | quiz       | Quiz 1 | C1     | quiz1    |
      | quiz       | Quiz 2 | C1     | quiz2    |
    And quiz "Quiz 1" contains the following questions:
      | question | page | maxmark |
      | TF1      | 1    | 1.0     |
      | TF2      | 1    | 1.0     |
      | TF3      | 1    | 1.0     |
      | TF4      | 1    | 1.0     |
    And quiz "Quiz 2" contains the following questions:
      | question | page | maxmark |
      | TF1      | 1    | 1.0     |
      | TF2      | 1    | 1.0     |
      | TF3      | 1    | 1.0     |
      | TF4      | 1    | 1.0     |
    And user "student1" has attempted "Quiz 1" with responses:
      | slot | response |
      |   1  | False    |
      |   2  | False    |
      |   3  | False    |
      |   4  | False    |
    And user "student2" has attempted "Quiz 1" with responses:
      | slot | response |
      |   1  | True     |
      |   2  | True     |
      |   3  | True     |
      |   4  | True     |
    And user "student3" has attempted "Quiz 1" with responses:
      | slot | response |
      |   1  | True     |
      |   2  | False    |
      |   3  | False    |
      |   4  | True     |
    And user "student4" has attempted "Quiz 1" with responses:
      | slot | response |
      |   1  | False    |
      |   2  | True     |
      |   3  | True     |
      |   4  | False    |
    And user "student1" has attempted "Quiz 2" with responses:
      | slot | response |
      |   1  | True     |
      |   2  | True     |
      |   3  | False    |
      |   4  | True     |
    And user "student2" has attempted "Quiz 2" with responses:
      | slot | response |
      |   1  | False    |
      |   2  | True     |
      |   3  | True     |
      |   4  | True     |
    And user "student3" has attempted "Quiz 2" with responses:
      | slot | response |
      |   1  | False    |
      |   2  | False    |
      |   3  | False    |
      |   4  | True     |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript
  Scenario: The questions can be filtered by discrimination index filter
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "Any"
    And I set the field "type" in the "Filter 1" "fieldset" to "Discrimination index"
    And I click on "discriminationselect" "select"
    And I click on "Index" "option"
    And I set the field with xpath "//select[@data-filterfield='range']" to "After"
    And I set the field "rangeValue1" in the "Filter 1" "fieldset" to "49"
    And I click on "Apply filters" "button"
    And I should see "TF2" in the "categoryquestions" "table"
    And I should not see "TF1" in the "categoryquestions" "table"
    And I should not see "TF3" in the "categoryquestions" "table"
    And I should not see "TF4" in the "categoryquestions" "table"
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "Any"
    And I set the field "type" in the "Filter 1" "fieldset" to "Discrimination index"
    And I click on "discriminationselect" "select"
    And I click on "Index" "option"
    And I set the field with xpath "//select[@data-filterfield='range']" to "Before"
    And I set the field "rangeValue1" in the "Filter 1" "fieldset" to "49"
    And I click on "Apply filters" "button"
    And I should not see "TF2" in the "categoryquestions" "table"
    And I should see "TF1" in the "categoryquestions" "table"
    And I should see "TF3" in the "categoryquestions" "table"
    And I should see "TF4" in the "categoryquestions" "table"
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "Any"
    And I set the field "type" in the "Filter 1" "fieldset" to "Discrimination index"
    And I click on "discriminationselect" "select"
    And I click on "Index" "option"
    And I set the field with xpath "//select[@data-filterfield='range']" to "Between"
    And I set the field "rangeValue1" in the "Filter 1" "fieldset" to "29"
    And I set the field "rangeValue2" in the "Filter 1" "fieldset" to "50"
    And I click on "Apply filters" "button"
    And I should not see "TF1" in the "categoryquestions" "table"
    And I should not see "TF2" in the "categoryquestions" "table"
    And I should not see "TF3" in the "categoryquestions" "table"
    And I should see "TF4" in the "categoryquestions" "table"
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "Any"
    And I set the field "type" in the "Filter 1" "fieldset" to "Discrimination index"
    And I click on "discriminationselect" "select"
    And I click on "Interpretation" "option"
    And I set the field "Discrimination interpretation" in the "Filter 1" "fieldset" to "Very good discrimination"
    And I click on "Apply filters" "button"
    And I should see "TF2" in the "categoryquestions" "table"
    And I should not see "TF1" in the "categoryquestions" "table"
    And I should not see "TF3" in the "categoryquestions" "table"
    And I should not see "TF4" in the "categoryquestions" "table"
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "Any"
    And I set the field "type" in the "Filter 1" "fieldset" to "Discrimination index"
    And I click on "discriminationselect" "select"
    And I click on "Interpretation" "option"
    And I set the field "Discrimination interpretation" in the "Filter 1" "fieldset" to "Weak discrimination"
    And I click on "Apply filters" "button"
    And I should see "TF1" in the "categoryquestions" "table"
    And I should see "TF3" in the "categoryquestions" "table"
    And I should not see "TF2" in the "categoryquestions" "table"
    And I should not see "TF4" in the "categoryquestions" "table"
    And I press "Clear filters"
    And I set the field "Match" in the "Filter 1" "fieldset" to "Any"
    And I set the field "type" in the "Filter 1" "fieldset" to "Discrimination index"
    And I click on "discriminationselect" "select"
    And I click on "Interpretation" "option"
    And I set the field "Discrimination interpretation" in the "Filter 1" "fieldset" to "Adequate discrimination"
    And I click on "Apply filters" "button"
    And I should not see "TF1" in the "categoryquestions" "table"
    And I should not see "TF2" in the "categoryquestions" "table"
    And I should not see "TF3" in the "categoryquestions" "table"
    And I should see "TF4" in the "categoryquestions" "table"

  @javascript
  Scenario: URL parameters are sharable and discrimination index filter can be retrieved
    And I add "49" discrimination values and "Course 1" with "Any" join type and "After" range type and parameters to url and visit it
    And I should see "Index" in the "Filter 1" "fieldset"
    And I should see "After" in the "Filter 1" "fieldset"
    And the field "rangeValue1" matches value "49"
    And I should see "TF2" in the "categoryquestions" "table"
    And I should not see "TF1" in the "categoryquestions" "table"
    And I should not see "TF3" in the "categoryquestions" "table"
    And I should not see "TF4" in the "categoryquestions" "table"
    And I add "29-50" discrimination values and "Course 1" with "Any" join type and "Between" range type and parameters to url and visit it
    And I should see "Index" in the "Filter 1" "fieldset"
    And I should see "Between" in the "Filter 1" "fieldset"
    And the field "rangeValue1" matches value "29"
    And the field "rangeValue2" matches value "50"
    And I should not see "TF1" in the "categoryquestions" "table"
    And I should not see "TF2" in the "categoryquestions" "table"
    And I should not see "TF3" in the "categoryquestions" "table"
    And I should see "TF4" in the "categoryquestions" "table"
