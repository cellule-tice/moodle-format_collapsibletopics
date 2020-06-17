@format @format_collapsibletopics
Feature: Sections can be expanded in collapsibletopics format
  In order to view/hide topics content in courses
  As a user
  I can expand/collapse topics

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | numsections |
      | Course 1 | C1        | collapsibletopics | 5           |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course | idnumber    | section |
      | assign     | Test assignment name   | Test assignment description   | C1     | assign1     | 0       |
      | book       | Test book name         | Test book description         | C1     | book1       | 1       |
      | chat       | Test chat name         | Test chat description         | C1     | chat1       | 4       |
      | choice     | Test choice name       | Test choice description       | C1     | choice1     | 5       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Expand/collapse section 1 with clicks on toggle icon when editing mode is off
    When I click on "Topic 1" "link" in the "li#section-1" "css_element"
    Then I should see "Test book name" in the "div#collapse-1" "css_element"
    And I should not see "Book: 1" in the "li#section-1 .content" "css_element"
    And I should not see "Test chat name" in the "div#collapse-4" "css_element"
    And I click on "Topic 1" "link" in the "li#section-1" "css_element"
    Then I should not see "Test book name" in the "div#collapse-1" "css_element"
    And I should see "Book: 1" in the "li#section-1 .content" "css_element"
    And I should not see "Test chat name" in the "div#collapse-4" "css_element"

  @javascript
  Scenario: Expand/collapse section 1 with clicks on toggle icon when editing mode is on
    Given I turn editing mode on
    When I click on ".sectiontoggle" "css_element" in the "li#section-1" "css_element"
    Then I should see "Test book name" in the "div#collapse-1" "css_element"
    And I should not see "Book: 1" in the "li#section-1 .content" "css_element"
    And I should not see "Test chat name" in the "div#collapse-4" "css_element"
    And I should see "Chat: 1" in the "li#section-4 .content" "css_element"
    And I click on ".sectiontoggle" "css_element" in the "li#section-1" "css_element"
    Then I should not see "Test book name" in the "div#collapse-1" "css_element"
    And I should see "Book: 1" in the "li#section-1 .content" "css_element"
    And I should see "Chat: 1" in the "li#section-4 .content" "css_element"
    And I should not see "Test chat name" in the "div#collapse-4" "css_element"

  @javascript
  Scenario: Expand/collapse all sections when editing mode is off
    When I click on "Expand all" "link" in the "div#collapse-0" "css_element"
    Then I should see "Test book name" in the "div#collapse-1" "css_element"
    And I should not see "Book: 1" in the "li#section-1 .content" "css_element"
    And I should see "Test chat name" in the "div#collapse-4" "css_element"
    And I should not see "Chat: 1" in the "li#section-4 .content" "css_element"
    And I should see "Test choice name" in the "li#section-5" "css_element"
    And I should not see "Choice: 1" in the "li#section-5 .content" "css_element"
    And I click on "Collapse all" "link" in the "div#collapse-0" "css_element"
    Then I should not see "Test book name" in the "div#collapse-1" "css_element"
    And I should see "Book: 1" in the "li#section-1 .content" "css_element"
    And I should not see "Test chat name" in the "div#collapse-2" "css_element"
    And I should see "Chat: 1" in the "li#section-4 .content" "css_element"
    And I should not see "Test choice name" in the "li#section-5" "css_element"
    And I should see "Choice: 1" in the "li#section-5 .content" "css_element"
