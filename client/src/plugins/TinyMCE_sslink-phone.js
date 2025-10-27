/* global tinymce, window */
import i18n from 'i18n';
import TinyMCEActionRegistrar from 'lib/TinyMCEActionRegistrar';
import React from 'react';
import { createRoot } from 'react-dom/client';
import jQuery from 'jquery';
import { createInsertLinkModal } from 'containers/InsertLinkModal/InsertLinkModal';
import { loadComponent } from 'lib/Injector';

const commandName = 'sslinkphone';

const plugin = {
  init(editor) {
    // Add "Link to phone number" to link menu for this editor
    TinyMCEActionRegistrar.addAction(
      'sslink',
      {
        text: i18n._t('TinyMCE.LINKLABEL_PHONE', 'Link to phone number'),
        onAction: (editorInst) => editorInst.execCommand(commandName),
        priority: 41,
      },
      editor.getParam('editorIdentifier'),
    ).addCommandWithUrlTest(commandName, /^tel:/);

    // Add a command that corresponds with the above menu item
    editor.addCommand(commandName, () => {
      const field = window.jQuery(`#${editor.id}`).entwine('ss');

      field.openLinkPhoneDialog();
    });
  },
};

const modalId = 'insert-link__dialog-wrapper--tel';
const sectionConfigKey = 'SilverStripe\\Admin\\LeftAndMain';
const formName = 'EditorPhoneLink';
const InsertLinkPhoneModal = loadComponent(createInsertLinkModal(sectionConfigKey, formName));

jQuery.entwine('ss', ($) => {
  $('textarea.htmleditor').entwine({
    openLinkPhoneDialog() {
      let dialog = $(`#${modalId}`);

      if (!dialog.length) {
        dialog = $(`<div id="${modalId}" />`);
        $('body').append(dialog);
      }
      dialog.addClass('insert-link__dialog-wrapper');

      dialog.setElement(this);
      dialog.open();
    },
  });

  /**
   * Assumes that $('.insert-link__dialog-wrapper').entwine({}); is defined for shared functions
   */
  $(`#${modalId}`).entwine({
    ReactRoot: null,

    renderModal(isOpen) {
      const handleHide = () => this.close();
      const handleInsert = (...args) => this.handleInsert(...args);
      const attrs = this.getOriginalAttributes();
      const requireLinkText = this.getRequireLinkText();

      // create/update the react component
      let root = this.getReactRoot();
      if (!root) {
        root = createRoot(this[0]);
        this.setReactRoot(root);
      }
      root.render(
        <InsertLinkPhoneModal
          isOpen={isOpen}
          onInsert={handleInsert}
          onClosed={handleHide}
          title={i18n._t('TinyMCE.LINK_PHONE', 'Insert phone link')}
          bodyClassName="modal__dialog"
          className="insert-link__dialog-wrapper--phone"
          fileAttributes={attrs}
          identifier="Admin.InsertLinkPhoneModal"
          requireLinkText={requireLinkText}
        />
      );
    },

    getOriginalAttributes() {
      const editor = this.getElement().getEditor();
      const node = $(editor.getSelectedNode());

      const hrefParts = (node.attr('href') || '').split('?');

      const phone = hrefParts[0].replace(/^tel:/, '').split('?')[0];

      return {
        Link: phone,
        Description: node.attr('title'),
      };
    },

    buildAttributes(data) {
      const attributes = this._super(data);

      let href = '';

      const phone = attributes.href.replace(/^tel:/, '').split('?')[0];

      // Prefix the phone number with "tel:"
      if (phone) {
        href = `tel:${phone}`;
      }
      attributes.href = href;

      delete attributes.target;

      return attributes;
    },
  });
});

// Adds the plugin class to the list of available TinyMCE plugins
tinymce.PluginManager.add(commandName, (editor) => plugin.init(editor));
export default plugin;
