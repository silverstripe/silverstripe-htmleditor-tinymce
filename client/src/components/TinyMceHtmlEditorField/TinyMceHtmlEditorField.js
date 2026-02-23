/* global window */
import React, { useEffect, useRef, useState } from 'react';
import Script from 'react-load-script';
import { getInputProps as getTextFieldInputProps } from 'components/TextField/TextField';
import {
  handleChange as handleChangeInputField,
  render as renderInputField,
} from 'components/InputField/InputField';
import fieldHolder from 'components/FieldHolder/FieldHolder';

const TinyMceHtmlEditorField = (_props) => {
  const defaultProps = {
    attributes: {},
    className: '',
    extraClass: '',
    type: 'text',
    value: '',
  };
  const props = {
    ...defaultProps,
    ..._props,
  };
  const data = props.data;
  const value = props.value;

  /**
   * sets initial state:
   * if editorjs IS defined, we are NOT ready (must check dependency first).
   * if editorjs is NOT defined, we ARE ready (no dependency).
   */
  const [isReady, setIsReady] = useState(!data.editorjs);
  const inputRef = useRef(null);
  const previousIsReady = useRef(isReady);
  const previousValue = useRef(value);
  // Persist the latest props to simulate class component `this.props` behavior and prevent stale closures.
  const propsRef = useRef(props);
  propsRef.current = props;
  // Track readiness on unmount so cleanup runs with the latest state.
  const isReadyRef = useRef(isReady);
  isReadyRef.current = isReady;

  const getInputProps = () => ({
    ...getTextFieldInputProps(propsRef.current),
    ...propsRef.current.data.attributes,
    innerRef: (ref) => { inputRef.current = ref; },
  });

  const getEditorElement = () => document.getElementById(getInputProps().id);

  const getEditor = () => window.TinyMCE && window.TinyMCE.get(getInputProps().id);

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
   * Forces the editor to invoke a change on the InputField
   */
  const registerChangeListener = () => {
    const target = getEditorElement();
    getEditor().on('change keyup', () => {
      handleChangeInputField(propsRef.current, { target });
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

  /**
   * When the handleReady callback is run, the state is changed.
   * This state change triggers the render of the .htmleditor element
   * however since this is not added by entwine, the entwine hook for
   * onadd is not run - we must trigger this manually.
   */
  useEffect(() => {
    if (!isReady) {
      previousIsReady.current = isReady;
      return;
    }

    if (isReady !== previousIsReady.current) {
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
    }

    previousIsReady.current = isReady;
  }, [isReady]);

  useEffect(() => {
    if (!isReady) {
      previousValue.current = value;
      return;
    }

    if (value !== previousValue.current) {
      const event = new Event('change', { bubbles: true });
      event.simulated = true;
      event.value = value;
      inputRef.current.dispatchEvent(event);
    }

    previousValue.current = value;
  }, [isReady, value]);

  useEffect(() => () => {
    if (!isReadyRef.current) {
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
    handleChangeInputField(propsRef.current, { target: editorElement });
    if ($) {
      $(document).triggerHandler(unmountEvent);
    }
  }, []);
  return isReady ? renderInputField(propsRef.current, getInputProps()) : renderDependencyScript();
};

export { TinyMceHtmlEditorField as Component };

export default fieldHolder(TinyMceHtmlEditorField);
