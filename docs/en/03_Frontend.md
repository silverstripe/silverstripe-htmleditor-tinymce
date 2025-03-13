---
title: Using TinyMCE on the front-end
---

# Using TinyMCE on the front-end

> [!WARNING]
> The plugins that come with `silverstripe/htmleditor-tinymce` (e.g. for inserting links) are not supported for use on the front-end.

The following JavaScript code will initialise TinyMCE for every [`HTMLEditorField`](api:SilverStripe\Forms\HTMLEditor\HTMLEditorField) on the page which uses the [`TinyMCEConfig`](api:SilverStripe\TinyMCE\TinyMCEConfig).

```js
const selector = 'textarea[data-editor="tinyMCE"]';
// eslint-disable-next-line no-restricted-syntax
for (const field of document.querySelectorAll(selector)) {
  const id = field.getAttribute('id');
  const config = JSON.parse(field.dataset.config);
  config.height = config.row_height ? config.row_height : undefined;
  config.selector = `textarea#${id}`;
  if (typeof config.baseURL !== 'undefined') {
    tinymce.EditorManager.baseURL = config.baseURL;
  }
  config.skin = config.skin || 'silverstripe';
  tinymce.init(config);
}
```

Note that the `tinymce` variable should already be available as it is included by the Requirements API when rendering the `HTMLEditorField` with the `$Field` template variable.
