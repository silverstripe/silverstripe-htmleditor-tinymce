/* global window */
import React, { useState, useRef, useEffect } from 'react';
import Script from 'react-load-script';
import { Component as TextField } from 'components/TextField/TextField';
import fieldHolder from 'components/FieldHolder/FieldHolder';

const TinyMceHtmlEditorField = ({
  id,
  name,
  className,
  extraClass,
  disabled,
  readOnly,
  value,
  placeholder,
  autoFocus,
  onBlur,
  onFocus,
  onChange,
  data,
  tip,
  type,
  attributes,
}) => {
  const [isReady, setIsReady] = useState(!data.editorjs);
  const inputRef = useRef(null);

  const mergedAttributes = {
    ...attributes,
    ...data.attributes,
    innerRef: ref => { inputRef.current = ref; },
  };

  const getEditorElement = () => document.getElementById(id);

  const getEditor = () => window.TinyMCE && window.TinyMCE.get(id);

  /**
   * Once the dependency script is loaded, updating the internal state
   * will trigger a reload and present the editor to the user
   */
  const handleReady = () => {
    if (!window.TinyMCE && window.tinymce) {
      window.TinyMCE = window.tinymce;
    }
    setIsReady(true);
  };

  /**
   * Handles changes to the input field's value.
   */
  const handleChange = (event) => {
    if (typeof onChange === 'function') {
      if (!event.target) {
        return;
      }
      onChange(event, { id, value: event.target.value });
    }
  };

  /**
   * Forces the editor to invoke a change on the InputField
   */
  const registerChangeListener = () => {
    const target = getEditorElement();
    getEditor().on('change keyup', () => {
      handleChange({ target });
    });
  };

  /**
   * TinyMCE operates from a global script being loaded in first.
   * We must ensure this dependency is loaded before proceeding to
   * render the editor proper
   */
  const renderDependencyScript = () => {
    if (!window.tinymce && !window.TinyMCE) {
      return <Script url={data.editorjs} onLoad={handleReady} />;
    }
    // If the script is already loaded, mark as ready after this render cycle finishes.
    setTimeout(() => {
      handleReady();
    }, 0);
    return null;
  };

  useEffect(() => {
    if (!isReady) {
      return;
    }
    setTimeout(() => {
      const { document, jQuery: $ } = window;
      const mountEvent = $ ? $.Event('EntwineElementsAdded') : new Event('noop');
      const editorElement = getEditorElement();
      mountEvent.targets = [editorElement];
      if ($) {
        $(document).triggerHandler(mountEvent);
      }
      registerChangeListener();
    }, 1);
  }, [isReady, id]);

  useEffect(() => {
    if (value && inputRef.current) {
      const event = new Event('change', { bubbles: true });
      event.simulated = true;
      event.value = value;
      inputRef.current.dispatchEvent(event);
    }
  }, [value, inputRef]);

  useEffect(() => () => {
    if (!isReady) {
      return;
    }
    const { document, jQuery: $ } = window;
    const unmountEvent = $ ? $.Event('EntwineElementsRemoved') : new Event('noop');
    const editorElement = getEditorElement();
    // Tell tinyMCE to persist changes into the text field
    const editor = getEditor();
    if (editor) {
      editor.save();
    }
    unmountEvent.targets = [editorElement];
    // Ensure that redux knows of the latest changes before the editor is destroyed.
    // This is pretty awful because TinyMCE triggers jQuery events which aren't picked up
    // by the react components. We also can't manufacture an event with the right target
    // without actually dispatching the event, and by then it's too late.
    handleChange({ target: editorElement });
    if ($) {
      $(document).triggerHandler(unmountEvent);
    }
  }, [isReady, id]);

  return isReady ? (
    <TextField
      id={id}
      name={name}
      className={className}
      extraClass={extraClass}
      disabled={disabled}
      readOnly={readOnly}
      value={value}
      placeholder={placeholder}
      autoFocus={autoFocus}
      onBlur={onBlur}
      onFocus={onFocus}
      onChange={onChange}
      data={data}
      tip={tip}
      type={type}
      attributes={mergedAttributes}
    />
  ) : (
    renderDependencyScript()
  );
};

export { TinyMceHtmlEditorField as Component };

export default fieldHolder(TinyMceHtmlEditorField);
