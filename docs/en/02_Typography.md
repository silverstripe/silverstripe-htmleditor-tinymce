---
title: WYSIWYG Styles
summary: Add custom CSS properties to the WYSIWYG
icon: text-width
---

# WYSIWYG styles

TinyMCE lets you customise the style of content in the editor. This is done by setting the [`TinyMCEConfig.editor_css`](api:SilverStripe\TinyMCE\TinyMCEConfig->editor_css) configuration property to the path of the CSS file that should be used.

```yml
---
name: tinymce-css
---
SilverStripe\TinyMCE\TinyMCEConfig:
  editor_css:
    - 'app/css/editor.css'
```

Alternatively, you can set this on a specific `TinyMCEConfig` instance via the [`setContentCSS()`](api:SilverStripe\TinyMCE\TinyMCEConfig::setContentCSS()) method.

```php
use SilverStripe\TinyMCE\TinyMCEConfig;
$config = TinyMCEConfig::get('my-editor');
$config->setContentCSS(['/app/client/css/editor.css']);
```

> [!WARNING]
> `silverstripe/htmleditor-tinymce` adds a small CSS file to `editor_css` which highlights broken links - you'll
> probably want to include that in the array you pass to `setContentCSS()`, either by first calling
> `getContentCSS()` and merging that array with your new one (and passing the result to `setContentCSS()`)
> or by adding `'/_resources/vendor/silverstripe/htmleditor-tinymce/client/dist/styles/editor.css'` to the array you pass
> to `setContentCSS()`

## Custom style dropdown

The custom style dropdown can be enabled via the `importcss` plugin. ([Doc](https://www.tiny.cloud/docs/tinymce/6/importcss/))
Use the below code in `app/_config.php`:

```php
use SilverStripe\TinyMCE\TinyMCEConfig;

TinyMCEConfig::get('my-editor')
    ->addButtonsToLine(1, 'styles')
    ->setOption('importcss_append', true);
```

Any CSS classes within this file will be automatically added to the `WYSIWYG` editors 'style' dropdown.
For instance, to
add the color 'red' as an option within the `WYSIWYG` add the following to the `editor.css`

```css
.red {
    color: red;
}
```

Adding a tag to the selector will automatically wrap with this tag. For example:

```css
h4.red {
    color: red;
}
```

will add an `h4` tag to the selected block.

For further customisation, customize the `style_formats` option.
`style_formats` won't be applied if you do not enable `importcss_append`.
Here is a working example to get you started.  
See related [TinyMCE doc](https://www.tiny.cloud/docs/tinymce/6/user-formatting-options/#style_formats).

```php
use SilverStripe\TinyMCE\TinyMCEConfig;

$formats = [
    [
        'title' => 'Headings',
        'items' => [
            ['title' => 'Heading 1', 'block' => 'h1' ],
            ['title' => 'Heading 2', 'block' => 'h2' ],
            ['title' => 'Heading 3', 'block' => 'h3' ],
            ['title' => 'Heading 4', 'block' => 'h4' ],
            ['title' => 'Heading 5', 'block' => 'h5' ],
            ['title' => 'Heading 6', 'block' => 'h6' ],
            [
                'title' => 'Subtitle',
                'selector' => 'p',
                'classes' => 'title-sub',
            ],
        ],
    ],
    [
        'title' => 'Misc Styles',
        'items' => [
            [
                'title' => 'Style 1',
                'selector' => 'ul',
                'classes' => 'style1',
                'wrapper' => true,
                'merge_siblings' => false,
            ],
            [
                'title' => 'Button red',
                'inline' => 'span',
                'classes' => 'btn-red',
                'merge_siblings' => true,
            ],
        ],
    ],
];

TinyMCEConfig::get('cms')
    ->addButtonsToLine(1, 'styles')
    ->setOptions([
        'importcss_append' => true,
        'style_formats' => $formats,
    ]);
```

## API documentation

- [`HtmlEditorConfig`](api:SilverStripe\Forms\HTMLEditor\HtmlEditorConfig)
- [`TinyMCEConfig`](api:SilverStripe\TinyMCE\TinyMCEConfig)
