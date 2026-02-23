/* global jest, test, expect, beforeEach, afterEach */

import React from 'react';
import { render, act, fireEvent, screen } from '@testing-library/react';
import TinyMceHtmlEditorField, { Component as TinyMceHtmlEditorFieldComponent } from '../TinyMceHtmlEditorField';

let scriptProps = null;

jest.mock('react-load-script', () => (props) => {
  scriptProps = props;
  return <div />;
});

const makeProps = (props = {}) => {
  const data = {
    editorjs: null,
    attributes: {
      'data-abc': '123',
      'data-def': '456',
    },
    ...(props.data || {}),
  };

  if (props.data && props.data.attributes) {
    data.attributes = {
      'data-abc': '123',
      'data-def': '456',
      ...props.data.attributes,
    };
  }

  return {
    id: 'TinyMceHtmlEditorField',
    name: 'MyName',
    className: 'my-classname',
    value: '',
    onChange: jest.fn(),
    ...props,
    data,
  };
};

const setupTinyMCE = () => {
  const on = jest.fn();
  const save = jest.fn();
  const get = jest.fn(() => ({ on, save }));
  return {
    get,
    on,
    save,
  };
};

beforeEach(() => {
  scriptProps = null;
  delete window.TinyMCE;
  delete window.tinymce;
});

afterEach(() => {
  jest.useRealTimers();
});

test('TinyMceHtmlEditorField provides attributes and change handling', () => {
  const props = makeProps();
  render(<TinyMceHtmlEditorFieldComponent {...props} />);
  const input = screen.getByRole('textbox');
  expect(input.getAttribute('data-abc')).toBe('123');
  expect(input.getAttribute('data-def')).toBe('456');
  expect(input.className).toContain('my-classname');
  fireEvent.change(input, { target: { value: 'updated value' } });
  expect(props.onChange).toHaveBeenCalledWith(expect.any(Object), { id: props.id, value: 'updated value' });
});

test('TinyMceHtmlEditorField defaults className and extraClass', () => {
  const props = makeProps({
    className: undefined,
    extraClass: undefined,
  });
  render(<TinyMceHtmlEditorFieldComponent {...props} />);
  const input = screen.getByRole('textbox');
  expect(input.className).not.toContain('undefined');
});

test('TinyMceHtmlEditorField marks ready after dependency loads', () => {
  const { get } = setupTinyMCE();
  const props = makeProps({
    data: {
      editorjs: 'script-url',
    },
  });
  render(<TinyMceHtmlEditorFieldComponent {...props} />);
  expect(screen.queryByRole('textbox')).toBeNull();
  expect(scriptProps.url).toBe('script-url');
  window.tinymce = { get };
  act(() => {
    scriptProps.onLoad();
  });
  expect(screen.getByRole('textbox')).not.toBeNull();
  expect(window.TinyMCE).toBe(window.tinymce);
});

test('TinyMceHtmlEditorField registers a TinyMCE change listener when ready', () => {
  jest.useFakeTimers();
  const { get, on } = setupTinyMCE();
  window.TinyMCE = { get };
  const props = makeProps({
    data: {
      editorjs: 'script-url',
    },
  });
  render(<TinyMceHtmlEditorFieldComponent {...props} />);
  act(() => {
    jest.runOnlyPendingTimers();
  });
  act(() => {
    jest.runOnlyPendingTimers();
  });
  expect(get).toHaveBeenCalledWith(props.id);
  expect(on).toHaveBeenCalledWith('change keyup', expect.any(Function));
});

test('TinyMceHtmlEditorField dispatches a simulated change event on value updates', () => {
  const props = makeProps({ value: 'first' });
  const dispatchSpy = jest.spyOn(HTMLInputElement.prototype, 'dispatchEvent');
  const { rerender } = render(<TinyMceHtmlEditorFieldComponent {...props} />);
  rerender(<TinyMceHtmlEditorFieldComponent {...props} value="second" />);
  expect(dispatchSpy).toHaveBeenCalled();
  const event = dispatchSpy.mock.calls[0][0];
  expect(event.simulated).toBe(true);
  expect(event.value).toBe('second');
  dispatchSpy.mockRestore();
});

test('TinyMceHtmlEditorField skips TinyMCE teardown when not ready', () => {
  jest.useFakeTimers();
  const { get } = setupTinyMCE();
  window.TinyMCE = { get };
  const props = makeProps({
    data: {
      editorjs: 'script-url',
    },
  });
  const { unmount } = render(<TinyMceHtmlEditorFieldComponent {...props} />);
  unmount();
  jest.clearAllTimers();
  expect(get).not.toHaveBeenCalled();
});

test('TinyMceHtmlEditorField render() renders with editorjs', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...{
      name: 'MyName',
      className: 'my-classname',
      data: {
        editorjs: 'I AM AN EDITOR_JS',
        attributes: {
          'data-abc': '123',
          'data-def': '456',
        }
      }
    }}
    />
  );
  expect(container.querySelectorAll('input')).toHaveLength(0);
});

test('TinyMceHtmlEditorField render() renders without editorjs', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...{
      name: 'MyName',
      className: 'my-classname',
      data: {
        editorjs: null,
        attributes: {
          'data-abc': '123',
          'data-def': '456',
        }
      }
    }}
    />
  );
  expect(container.querySelectorAll('input')).toHaveLength(1);
});
