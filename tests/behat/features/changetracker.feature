@retry
Feature: Changetracker correctly identifies changes
  As a content author
  I want to know when unsaved changes have been made
  So that I know when I need to save my work

  Background:
    Given a "page" "About Us" has the "Content" "<p>My awesome content</p>"
      And the "group" "EDITOR" has permissions "Access to 'Pages' section"
      And I am logged in as a member of "EDITOR" group
      And I go to "/admin/pages"
      And I click on "About Us" in the tree

  Scenario: Changing values in HTMLEditorField
    # Should have "tick" icon, not "save" icon (i.e. form is NOT dirty)
    Given I should see the "button#Form_EditForm_action_save .font-icon-tick" element
      And I should not see the "button#Form_EditForm_action_save .font-icon-save" element
      And I should not see the "form#Form_EditForm.changed" element
    When I click on the "iframe#Form_EditForm_Content_ifr" element
      And I press the "A" key globally
    # Should have "save" icon, not "tick" icon (i.e. form is dirty)
    Then I should not see the "button#Form_EditForm_action_save .font-icon-tick" element
      And I should see the "button#Form_EditForm_action_save .font-icon-save" element
      And I should see the "form#Form_EditForm.changed" element
    # Save so the driver can reset without having to deal with the popup alert.
    Then I press the "Save" button
