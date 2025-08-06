/* global tinyMCE, tinymce */

import jQuery from 'jquery';
import escapeRegExp from 'lodash.escaperegexp';

const ss = typeof window.ss !== 'undefined' ? window.ss : {};

/**
 * See admin/client/src/legacy/HtmlEditorField.js
 */
ss.editorWrappers.tinyMCE = (function () {
  // ID of editor this is assigned to
  let editorID;

  return {
    /**
     * Initialise the editor
     *
     * @param {String} ID of parent textarea domID
     */
    init(ID) {
      editorID = ID;

      this.create();
    },

    /**
     * Remove the editor and cleanup
     */
    destroy() {
      tinymce.EditorManager.execCommand('mceRemoveEditor', false, editorID);
    },

    /**
     * Get TinyMCE Editor instance
     *
     * @returns Editor
     */
    getInstance() {
      return tinymce.EditorManager.get(editorID);
    },

    /** (
     * Invoked when a content-modifying UI is opened.
     */
    onopen() {
      // NOOP
    },

    /** (
     * Invoked when a content-modifying UI is closed.
     */
    onclose() {
      // NOOP
    },

    setHeight(event, height) {
      if (typeof height === 'undefined') {
        return;
      }
      if (event.target && event.target.iframeElement) {
        event.target.iframeElement.height = height !== '' ? 'auto' : height;
        const parentDiv = event.target.iframeElement.closest('.tox-sidebar-wrap');
        if (parentDiv) {
          parentDiv.style.height = height;
        }
      }
    },

    /**
     * Get the raw config on the text area for this editor
     * @returns object
     */
    getRawConfig() {
      const selector = `#${editorID}`;
      return jQuery(selector).data('config');
    },

    /**
     * Get config for this data
     *
     * @returns object
     */
    getConfig() {
      const config = this.getRawConfig();
      const height = config.row_height ? config.row_height : undefined;
      const self = this;

      // Add instance specific data to config
      config.selector = `#${editorID}`;

      // Ensure save events write back to textarea
      config.setup = function (ed) {
        ed.on('dirty', () => {
          self.save();
        });
        ed.on('change', () => {
          self.save();
        });
        ed.on('init', (event) => {
          self.setHeight(event, height);
        });
        ed.on('ResizeEditor', (event) => {
          self.setHeight(event, '');
        });
      };
      return config;
    },

    /**
     * Write the HTML back to the original text area field.
     *
     * @param {object} options
     * @param {boolean} options.silent - suppress change event on the textarea
     */
    save(options = {}) {
      const instance = this.getInstance();
      instance.save();

      // Update change detection
      if (!options.silent) {
        jQuery(instance.getElement()).trigger('change');
        instance.getElement().dispatchEvent(new Event('input', { bubbles: true }));
      }
    },

    /**
     * Create a new instance based on a textarea field.
     */
    create() {
      let timeLastScrolled;
      let showAfterScrollFunc;
      let initialOffset;

      // Recalculate floatpanels (ie. image tools) absolute position then show it
      function showAfterScroll(panel, offset) {
        // The position scrolled to
        const finalOffset = jQuery(panel).scrollTop();

        // Reposition the floatpanels by length scrolled
        jQuery('.mce-floatpanel').each((i, el) => {
          const oldPosition = parseFloat(el.style.top);
          jQuery(el).css('top', `${oldPosition - (finalOffset - offset)}px`);
        });
        jQuery('.mce-floatpanel').css('opacity', '1');

        // Allow the floatpanels to be hidden again
        timeLastScrolled = undefined;
      }

      // Hide floatpanels (ie. image tools) while scrolling forms and
      // then recalculate position
      function hideOnScroll(e) {
        // Which element is being scrolled
        const panel = e.target;

        // If this is the first scroll event or the first one after 500ms
        if (!timeLastScrolled || ((new Date() - timeLastScrolled) / 100) > 500) {
          // Get the starting scroll position
          initialOffset = jQuery(panel).scrollTop();
          jQuery('.mce-floatpanel').css('opacity', '0');
        } else {
          // If this event is triggered before the 500ms timout reset the timeout
          window.clearTimeout(showAfterScrollFunc);
        }
        timeLastScrolled = new Date();
        // Set a function to trigger if no scrolling occurs within 500ms
        showAfterScrollFunc = window.setTimeout(() => showAfterScroll(panel, initialOffset), 500);
      }

      const config = this.getConfig();
      // hack to set baseURL safely
      if (typeof config.baseURL !== 'undefined') {
        tinymce.EditorManager.baseURL = config.baseURL;
      }

      config.skin = config.skin || 'silverstripe';

      // Bind the floatpanel hide and reposition listener to the closest scrollable panel
      tinymce.init(config).then((editors) => {
        if (editors.length > 0 && editors[0].container) {
          const scrollPanel = jQuery(editors[0].container).closest('.panel--scrollable');
          scrollPanel.on('scroll', (e) => hideOnScroll(e));
        }
      });
    },

    /**
     * Request an update to editor content
     */
    repaint() {
      // NOOP
    },

    /**
     * @return boolean
     */
    isDirty() {
      return this.getInstance().isDirty();
    },

    /**
     * Prepare a value for use in the change tracker
     */
    prepValueForChangeTracker(value) {
      const config = jQuery.extend({ forced_root_block: 'p' }, this.getRawConfig());
      const serializer = tinymce.html.Serializer(config);
      const parser = tinymce.html.DomParser(config);
      return serializer.serialize(parser.parse(value));
    },

    /**
     * HTML representation of the edited content.
     *
     * Returns: {String}
     */
    getContent() {
      return this.getInstance().getContent();
    },

    getSelection() {
      const instance = this.getInstance();
      const selection = instance.selection.getSel().toString();
      return selection || '';
    },

    /**
     * Make a selection based on a CSS selector
     */
    selectByCssSelector(cssSelector) {
      const doc = this.getInstance().getDoc();
      const sel = doc.getSelection();
      const rng = new Range();
      let matched = false;

      jQuery(doc).find(cssSelector).each(function () {
        if (matched) {
          return;
        }
        rng.selectNode(this);
        sel.removeAllRanges();
        sel.addRange(rng);
        matched = true;
      });
    },

    /**
     * DOM tree of the edited content
     *
     * Returns: DOMElement
     */
    getDOM() {
      return this.getInstance().getElement();
    },

    /**
     * Returns: DOMElement
     */
    getContainer() {
      return this.getInstance().getContainer();
    },

    /**
     * Get the closest node matching the current selection.
     *
     * Returns: {jQuery} DOMElement
     */
    getSelectedNode() {
      return this.getInstance().selection.getNode();
    },

    /**
     * Select the given node within the editor DOM
     *
     * Parameters: {DOMElement}
     */
    selectNode(node) {
      this.getInstance().selection.select(node);
    },

    /**
     * Replace entire content
     *
     * @param {String} html
     * @param {Object} opts
     */
    setContent(html, opts) {
      this.getInstance().setContent(html, opts);
    },

    /**
     * Insert content at the current caret position
     *
     * @param {String} html
     * @param {Object} opts
     */
    insertContent(html, opts) {
      this.getInstance().insertContent(html, opts);
    },

    /**
     * Replace currently selected content
     *
     * @param {String} html
     */
    replaceContent(html, opts) {
      this.getInstance().execCommand('mceReplaceContent', false, html, opts);
    },

    /**
     * Insert or update a link in the content area (based on current editor selection)
     *
     * Parameters: {Object} attrs
     */
    insertLink(attrs, opts, linkText) {
      if (linkText) {
        linkText = linkText.replaceAll('<', '&lt;').replaceAll('>', '&gt;');
        const linkEl = this.getInstance().dom.create('a', attrs, linkText);
        this.getInstance().selection.setNode(linkEl);
      } else {
        this.getInstance().execCommand('mceInsertLink', false, attrs, opts);
      }
    },

    /**
     * Remove the link from the currently selected node (if any).
     */
    removeLink() {
      this.getInstance().execCommand('unlink', false);
    },

    /**
     * Strip any editor-specific notation from link in order to make it presentable in the UI.
     *
     * Parameters:
     *  {Object}
     *  {DOMElement}
     */
    // eslint-disable-next-line no-unused-vars
    cleanLink(href, node) {
      const settings = this.getConfig;
      const cb = settings.urlconverter_callback;
      const cu = tinyMCE.settings.convert_urls;
      // eslint-disable-next-line no-eval
      if (cb) href = eval(`${cb}(href, node, true);`);

      // Turn into relative, if set in TinyMCE config
      if (cu && href.match(new RegExp(`^${escapeRegExp(tinyMCE.settings.document_base_url)}(.*)$`))) {
        href = RegExp.$1;
      }

      // Get rid of TinyMCE's temporary URLs
      if (href.match(/^javascript:\s*mctmp/)) href = '';

      return href;
    },

    /**
     * Creates a bookmark for the currently selected range,
     * which can be used to reselect this range at a later point.
     * @return {mixed}
     */
    createBookmark() {
      return this.getInstance().selection.getBookmark();
    },

    /**
     * Selects a bookmarked range previously saved through createBookmark().
     * @param  {mixed} bookmark
     */
    moveToBookmark(bookmark) {
      this.getInstance().selection.moveToBookmark(bookmark);
      this.getInstance().focus();
    },

    /**
     * Removes any selection & de-focuses this editor
     */
    blur() {
      this.getInstance().selection.collapse();
    },

    /**
     * Add new undo point with the current DOM content.
     */
    addUndo() {
      this.getInstance().undoManager.add();
    }
  };
});
// Override this to switch editor wrappers
ss.editorWrappers.default = ss.editorWrappers.tinyMCE; // @TODO need to do this per field, not globally
