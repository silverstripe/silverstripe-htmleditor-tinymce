/* global jest, test, expect, afterEach, beforeEach */

import React from 'react';
import { render } from '@testing-library/react';
import TinyMceHtmlEditorField from '../TinyMceHtmlEditorField';

function makeProps(obj = {}) {
  return {
    id: 'test-editor',
    name: 'MyName',
    className: 'my-classname',
    value: '',
    data: {
      editorjs: null,
      attributes: {
        'data-abc': '123',
        'data-def': '456',
      }
    },
    ...obj
  };
}

beforeEach(() => {
  delete window.TinyMCE;
  delete window.tinymce;
});

afterEach(() => {
  delete window.TinyMCE;
  delete window.tinymce;
});

test('TinyMceHtmlEditorField render() renders with editorjs', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: 'I AM AN EDITOR_JS',
        attributes: {
          'data-abc': '123',
          'data-def': '456',
        }
      }
    })}
    />
  );
  expect(container.querySelectorAll('input')).toHaveLength(0);
});

test('TinyMceHtmlEditorField render() renders without editorjs', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: null,
        attributes: {
          'data-abc': '123',
          'data-def': '456',
        }
      }
    })}
    />
  );
  expect(container.querySelectorAll('input')).toHaveLength(1);
});

test('TinyMceHtmlEditorField renders input element with correct attributes', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      id: 'html-editor-1',
      data: {
        editorjs: null,
        attributes: {
          'data-test': 'value',
        }
      }
    })}
    />
  );
  const input = container.querySelector('input');
  expect(input).not.toBeNull();
  expect(input.id).toBe('html-editor-1');
  expect(input.getAttribute('data-test')).toBe('value');
});

test('TinyMceHtmlEditorField renders textarea when rows > 1', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: null,
        rows: 5,
        columns: 40,
        attributes: {}
      }
    })}
    />
  );
  const textarea = container.querySelector('textarea');
  expect(textarea).not.toBeNull();
  expect(textarea.rows).toBe(5);
  expect(textarea.cols).toBe(40);
});

test('TinyMceHtmlEditorField renders with provided value', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      value: '<p>Test content</p>',
      data: {
        editorjs: null,
        rows: 1,
        attributes: {}
      }
    })}
    />
  );
  const input = container.querySelector('input');
  expect(input.value).toBe('<p>Test content</p>');
});

test('TinyMceHtmlEditorField does not render input when editorjs URL provided and tinymce not loaded', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: 'http://example.com/tinymce.js',
        attributes: {}
      }
    })}
    />
  );
  expect(container.querySelectorAll('input')).toHaveLength(0);
});

test('TinyMceHtmlEditorField renders input when tinymce already loaded globally', () => {
  window.tinymce = { get: jest.fn() };
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: 'http://example.com/tinymce.js',
        attributes: {}
      }
    })}
    />
  );
  expect(container.querySelector('script')).toBeNull();
});

test('TinyMceHtmlEditorField preserves input attributes from data.attributes', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: null,
        attributes: {
          'data-custom': 'custom-value',
          'data-another': 'another-value',
          placeholder: 'Enter text here'
        }
      }
    })}
    />
  );
  const input = container.querySelector('input');
  expect(input.getAttribute('data-custom')).toBe('custom-value');
  expect(input.getAttribute('data-another')).toBe('another-value');
  expect(input.getAttribute('placeholder')).toBe('Enter text here');
});

test('TinyMceHtmlEditorField renders with className and name props', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      className: 'custom-class another-class',
      name: 'body-content',
      data: {
        editorjs: null,
        attributes: {}
      }
    })}
    />
  );
  const input = container.querySelector('input');
  expect(input.name).toBe('body-content');
  expect(input.classList.contains('custom-class')).toBe(true);
  expect(input.classList.contains('another-class')).toBe(true);
});

test('TinyMceHtmlEditorField handles empty data.attributes gracefully', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: null,
        attributes: {}
      }
    })}
    />
  );
  const input = container.querySelector('input');
  expect(input).not.toBeNull();
});

test('TinyMceHtmlEditorField renders correctly when editorjs is empty string', () => {
  const { container } = render(
    <TinyMceHtmlEditorField {...makeProps({
      data: {
        editorjs: '',
        attributes: {}
      }
    })}
    />
  );
  expect(container.querySelectorAll('input')).toHaveLength(1);
});
