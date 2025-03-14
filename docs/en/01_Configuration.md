---
title: Configuring TinyMCE
icon: code
---

# Configuring TinyMCE

## Adding and removing capabilities

In its simplest form, TinyMCE configuration includes adding and removing buttons and plugins.

You can add plugins to the editor using the [`TinyMCEConfig::enablePlugins()`](api:SilverStripe\TinyMCE\TinyMCEConfig::enablePlugins()) method. This will
transparently generate the relevant underlying TinyMCE code.

> [!TIP]
> We've done an explicit `instanceof` check here for correctness, but in reality unless your project also uses an alternative WYSIWYG editor, you can safely omit that check. The remaining examples in this documentation will omit the check.

```php
// app/_config.php
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\TinyMCE\TinyMCEConfig;

$editorConfig = HTMLEditorConfig::get('cms');
if ($editorConfig instanceof TinyMCEConfig) {
    $editorConfig->enablePlugins('emoticons');
}
```

> [!NOTE]
> This utilities the TinyMCE's [`external_plugins`](https://www.tiny.cloud/docs/tinymce/6/editor-important-options/#external_plugins)
> option under the hood.

Plugins and advanced themes can provide additional buttons that can be added (or removed) through the
configuration. Here is an example of adding a `ssmacron` button after the `charmap` button:

```php
// app/_config.php
use SilverStripe\TinyMCE\TinyMCEConfig;

TinyMCEConfig::get('cms')->insertButtonsAfter('charmap', 'ssmacron');
```

Buttons can also be removed:

```php
// app/_config.php
use SilverStripe\TinyMCE\TinyMCEConfig;

TinyMCEConfig::get('cms')->removeButtons('tablecontrols', 'blockquote', 'hr');
```

> [!WARNING]
> Internally `TinyMCEConfig` uses the TinyMCE's `toolbar` option to configure these. See the
> [TinyMCE documentation of this option](https://www.tiny.cloud/docs/tinymce/6/toolbar-configuration-options/#toolbar)
> for more details.

## Enabling custom plugins

It is also possible to add custom plugins to TinyMCE, for example toolbar buttons.
You can enable them through [`TinyMCEConfig::enablePlugins()`](api:SilverStripe\TinyMCE\TinyMCEConfig::enablePlugins()):

```php
// app/_config.php
use SilverStripe\TinyMCE\TinyMCEConfig;

TinyMCEConfig::get('cms')->enablePlugins(['myplugin' => 'app/javascript/myplugin/editor_plugin.js']);
```

> [!TIP]
> The path for the plugin file must be one of the following:
>
> - `null` (if the plugin being enabled is a built-in plugin)
> - a path, relative to your `_resources/` directory, to the plugin file
> - a `ModuleResource` instance representing the plugin JavaScript file (see `silverstripe/htmleditor-tinymce`'s `_config.php` file for examples)
> - an absolute URL (e.g. for a third-party plugin to be fetched from a CDN).

You can learn how to [create a plugin](https://www.tiny.cloud/docs/tinymce/6/creating-a-plugin/) from the TinyMCE documentation.

## Setting options

TinyMCE behaviour can be affected through its [configuration options](https://www.tiny.cloud/docs/tinymce/6/basic-setup).
These options will be passed straight to the editor.

> [!WARNING]
> While you can define `valid_elements` and `extended_valid_elements` using this API and it will be respected, the generalised API described in [defining HTML editor configurations](https://docs.silverstripe.org/en/developer_guides/forms/field_types/htmleditorfield/#defining-html-editor-configurations) should generally be preferred instead.

A default set of options has been defined in the [`TinyMCEConfig.default_options`](api:SilverStripe\TinyMCE\TinyMCEConfig->default_options) configuration property. Updating this configuration property will update the default options which are set for all `TinyMCEConfig` instances.

For example to disable resizing TinyMCE editors:

```yml
SilverStripe\TinyMCE\TinyMCEConfig:
  default_options:
    resize: false
```

You can also set options for a specific named config, either by setting [`HTMLEditorConfig.default_config_definitions`](api:SilverStripe\Forms\HTMLEditor\HTMLEditorConfig->default_config_definitions) via YAML configuration or by calling [`setOption`](api:SilverStripe\Forms\HTMLEditor\HTMLEditorConfig::setOption()) in your `_config.php` file:

```yml
SilverStripe\Forms\HTMLEditor\HTMLEditorConfig:
  default_config_definitions:
    my-config:
      options:
        resize: false
```

```php
// app/_config.php
use SilverStripe\TinyMCE\TinyMCEConfig;

TinyMCEConfig::get('my-config')->setOption('resize', false);
```

> [!HINT]
> Note that the `setOption()` method *overrides* any existing value for that option. For options that accept a string or array, if you only want to change some small part
of the existing option value, you can call `getOption()`, modify the returned value, and then pass the result to `setOption()`.

### Image size pre-sets

Silverstripe CMS will suggest pre-set image size in the HTML editor. Content authors can quickly switch between the pre-set size when interacting with images.

The default values are defined in [`TinyMCEConfig.image_size_presets`](api:SilverStripe\TinyMCE\TinyMCEConfig->image_size_presets). Developers can customise the pre-set sizes by altering their TinyMCEConfig.

You can alter the defaults for all TinyMCE editors with YAML configuration.

```yml
SilverStripe\TinyMCE\TinyMCEConfig:
  image_size_presets:
    - name: widesize
      i18n: SilverStripe\TinyMCE\TinyMCEConfig.WIDE_SIZE
      text: Wide size
      width: 900
```

You can edit the image size pre-sets for an individual configuration by calling `setOption()` on a `TinyMCEConfig` instance.

Remember that calling `setOption()` overrides the existing value, so you need to also include the default presets if you want those.

```php
use SilverStripe\TinyMCE\TinyMCEConfig;

$presets = array_merge(
    [
        [
            'width' => 300,
            'text' => 'Small fit',
            'name' => 'smallfit',
            'default' => true,
        ],
    ],
    TinyMCEConfig::config()->get('image_size_presets')
);
TinyMCEConfig::get('cms')->setOption('image_size_presets', $presets);
```

## Disable oEmbed

The ["oEmbed" standard](https://www.oembed.com/) is implemented by many media services around the web, allowing easy
representation of files just by referencing a website URL. For example, a content author can insert a playable youtube
video just by knowing its URL, as opposed to dealing with manual HTML code.

To disable oEmbed you will need to follow the below to remove the plugin from TinyMCE, as well
as disabling the internal service via YAML:

```php
use SilverStripe\TinyMCE\TinyMCEConfig;

TinyMCEConfig::get('cms')->disablePlugins('ssembed');
```

```yml
---
Name: oembed-disable
---
SilverStripe\AssetAdmin\Forms\RemoteFileFormFactory:
  enabled: false
```

## Doctypes

Since TinyMCE generates markup, it needs to know which doctype your documents will be rendered in. You can set this
through the [`element_format`](https://www.tiny.cloud/docs/tinymce/6/content-filtering/#element_format) configuration variable.

In case you want to adhere to the stricter xhtml format (for example rendering self closing tags like `<br/>` instead of `<br>`),
use the following configuration:

```php
use SilverStripe\TinyMCE\TinyMCEConfig;

TinyMCEConfig::get('cms')->setOption('element_format', 'xhtml');
```
