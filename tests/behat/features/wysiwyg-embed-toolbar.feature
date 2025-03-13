@assets @retry
Feature: Use the WYSIWYG embed toolbar
  As a cms author
  I want to edit and delete embeds using the context toolbar
  So that I can administer my content efficiently

  Background:
    Given an "image" "folder1/file1.jpg"
      And a "page" "About Us" has the "Content" "[embed url="https://www.example.com/watch?v=4Eyiz8wxC8E" thumbnail="/assets/folder1/file1.jpg" class="embed" width="200" height="113"]https://www.example.com/watch?v=4Eyiz8wxC8E[/embed]"
      And the "group" "EDITOR" has permissions "Access to 'Pages' section"
      And I am logged in as a member of "EDITOR" group
      And I go to "/admin/pages"
      And I click on "About Us" in the tree
    # Validate that everything is ready for the test scenarios
    Then I should not see a ".tox-pop__dialog .tox-toolbar" element
      And I should not see the ".insert-embed-react__dialog-wrapper" element

  Scenario: I can open the media edit modal from the context toolbar
    When I select the media "https://www.example.com/watch?v=4Eyiz8wxC8E" in the "Content" HTML field
    Then I should see a ".tox-pop__dialog .tox-toolbar button[title='Edit media']" element
    When I press the "Edit media" button
    Then I should see "Media from the web" in the ".insert-embed-react__dialog-wrapper" element

  Scenario: I can delete the media from the context toolbar
    When I select the media "https://www.example.com/watch?v=4Eyiz8wxC8E" in the "Content" HTML field
    Then I should see a ".tox-pop__dialog .tox-toolbar button[title='Delete media']" element
    When I press the "Delete media" button
    Then the "Content" HTML field should not contain "https://www.example.com/watch?v=4Eyiz8wxC8E"
    # Required to avoid "unsaved changes" browser dialog
    When I press the "Save" button

  Scenario: I can embed new media through the WYSIWYG
    # Start with no embed
    Given I fill in the "Content" HTML field with "<p>my new content</p>"
    When I press the "Insert media via URL" button
    And I fill in "Form_remoteCreateForm_Url" with "https://www.youtube.com/watch?v=ScMzIvxBSi4"
    And I press "Add media"
    And I wait for 15 seconds
    And I press "Insert media"
    And I press the "Publish" button
    And I go to "/about-us"
    Then I should see an "iframe[src='https://www.youtube.com/embed/ScMzIvxBSi4?feature=oembed']" element
