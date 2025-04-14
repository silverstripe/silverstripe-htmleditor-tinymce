<?php

namespace SilverStripe\TinyMCE;

use Exception;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleResource;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\HTMLEditor\HTMLEditorAttributeRule;
use SilverStripe\Forms\HTMLEditor\HTMLEditorElementRule;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\Forms\HTMLEditor\HTMLEditorRuleSet;
use SilverStripe\i18n\i18n;
use SilverStripe\i18n\i18nEntityProvider;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ThemeResourceLoader;

/**
 * Default configuration for HTMLEditor specific to tinymce
 */
class TinyMCEConfig extends HTMLEditorConfig implements i18nEntityProvider
{
    /**
     * Map of locales to tinymce's supported languages
     *
     * @link https://www.tiny.cloud/get-tiny/language-packages/
     */
    private static array $tinymce_lang = [
        'ar_EG' => 'ar',
        'az_AZ' => 'az',
        'bg_BG' => 'bg_BG',
        'ca_AD' => 'ca',
        'ca_ES' => 'ca',
        'cs_CZ' => 'cs',
        'cy_GB' => 'cy',
        'da_DK' => 'da',
        'da_GL' => 'da',
        'de_AT' => 'de',
        'de_BE' => 'de',
        'de_CH' => 'de',
        'de_DE' => 'de',
        'de_LI' => 'de',
        'de_LU' => 'de',
        'de_BR' => 'de',
        'de_US' => 'de',
        'el_CY' => 'el',
        'el_GR' => 'el',
        'es_AR' => 'es',
        'es_BO' => 'es',
        'es_CL' => 'es',
        'es_CO' => 'es',
        'es_CR' => 'es',
        'es_CU' => 'es',
        'es_DO' => 'es',
        'es_EC' => 'es',
        'es_ES' => 'es',
        'es_GQ' => 'es',
        'es_GT' => 'es',
        'es_HN' => 'es',
        'es_MX' => 'es_MX',
        'es_NI' => 'es',
        'es_PA' => 'es',
        'es_PE' => 'es',
        'es_PH' => 'es',
        'es_PR' => 'es',
        'es_PY' => 'es',
        'es_SV' => 'es',
        'es_UY' => 'es',
        'es_VE' => 'es',
        'es_AD' => 'es',
        'es_BZ' => 'es',
        'es_US' => 'es',
        'et_EE' => 'et',
        'eo_XX' => 'eo',
        'fa_AF' => 'fa',
        'fa_IR' => 'fa',
        'fa_PK' => 'fa',
        'ff_FI' => 'fi',
        'fr_BE' => 'fr_FR',
        'fr_BF' => 'fr_FR',
        'fr_BI' => 'fr_FR',
        'fr_BJ' => 'fr_FR',
        'fr_CA' => 'fr_FR',
        'fr_CF' => 'fr_FR',
        'fr_CG' => 'fr_FR',
        'fr_CH' => 'fr_FR',
        'fr_CI' => 'fr_FR',
        'fr_CM' => 'fr_FR',
        'fr_DJ' => 'fr_FR',
        'fr_DZ' => 'fr_FR',
        'fr_FR' => 'fr_FR',
        'fr_GA' => 'fr_FR',
        'fr_GF' => 'fr_FR',
        'fr_GN' => 'fr_FR',
        'fr_GP' => 'fr_FR',
        'fr_HT' => 'fr_FR',
        'fr_KM' => 'fr_FR',
        'fr_LU' => 'fr_FR',
        'fr_MA' => 'fr_FR',
        'fr_MC' => 'fr_FR',
        'fr_MG' => 'fr_FR',
        'fr_ML' => 'fr_FR',
        'fr_MQ' => 'fr_FR',
        'fr_MU' => 'fr_FR',
        'fr_NC' => 'fr_FR',
        'fr_NE' => 'fr_FR',
        'fr_PF' => 'fr_FR',
        'fr_PM' => 'fr_FR',
        'fr_RE' => 'fr_FR',
        'fr_RW' => 'fr_FR',
        'fr_SC' => 'fr_FR',
        'fr_SN' => 'fr_FR',
        'fr_SY' => 'fr_FR',
        'fr_TD' => 'fr_FR',
        'fr_TG' => 'fr_FR',
        'fr_TN' => 'fr_FR',
        'fr_VU' => 'fr_FR',
        'fr_WF' => 'fr_FR',
        'fr_YT' => 'fr_FR',
        'fr_GB' => 'fr_FR',
        'fr_US' => 'fr_FR',
        'he_IL' => 'he_IL',
        'hi_IN' => 'hi',
        'hr_HR' => 'hr',
        'hu_HU' => 'hu_HU',
        'hu_AT' => 'hu_HU',
        'hu_RO' => 'hu_HU',
        'hu_RS' => 'hu_HU',
        'id_ID' => 'id',
        'is_IS' => 'is_IS',
        'it_CH' => 'it',
        'it_IT' => 'it',
        'it_SM' => 'it',
        'it_FR' => 'it',
        'it_HR' => 'it',
        'it_US' => 'it',
        'it_VA' => 'it',
        'ja_JP' => 'ja',
        'ko_KP' => 'ko_KR',
        'ko_KR' => 'ko_KR',
        'ko_CN' => 'ko_KR',
        'lt_LT' => 'lt',
        'lv_LV' => 'lv',
        'nb_NO' => 'nb_NO',
        'nb_SJ' => 'nb_NO',
        'ne_NP' => 'ne',
        'nl_AN' => 'nl',
        'nl_AW' => 'nl',
        'nl_BE' => 'nl_BE',
        'nl_NL' => 'nl',
        'nl_SR' => 'nl',
        'pl_PL' => 'pl',
        'pl_UA' => 'pl',
        // Note: All of the pt_* except pt_BR should map to pt_PT but that translation isn't available for tinymce6 yet.
        'pt_AO' => 'pt_BR',
        'pt_BR' => 'pt_BR',
        'pt_CV' => 'pt_BR',
        'pt_GW' => 'pt_BR',
        'pt_MZ' => 'pt_BR',
        'pt_PT' => 'pt_BR',
        'pt_ST' => 'pt_BR',
        'pt_TL' => 'pt_BR',
        'ro_MD' => 'ro',
        'ro_RO' => 'ro',
        'ro_RS' => 'ro',
        'ru_BY' => 'ru',
        'ru_KG' => 'ru',
        'ru_KZ' => 'ru',
        'ru_RU' => 'ru',
        'ru_SJ' => 'ru',
        'ru_UA' => 'ru',
        'sk_SK' => 'sk',
        'sk_RS' => 'sk',
        'sl_SI' => 'sl_SI',
        'sr_RS' => 'sr',
        'sv_FI' => 'sv_SE',
        'sv_SE' => 'sv_SE',
        'th_TH' => 'th_TH',
        'tr_CY' => 'tr',
        'tr_TR' => 'tr',
        'tr_DE' => 'tr',
        'tr_MK' => 'tr',
        'uk_UA' => 'uk',
        'vi_VN' => 'vi',
        'vi_US' => 'vi',
        'zh_CN' => 'zh_Hans',
        'zh_HK' => 'zh_Hans',
        'zh_MO' => 'zh_Hans',
        'zh_SG' => 'zh_Hans',
        'zh_TW' => 'zh_Hans',
        'zh_ID' => 'zh_Hans',
        'zh_MY' => 'zh_Hans',
        'zh_TH' => 'zh_Hans',
        'zh_US' => 'zh_Hans',
    ];

    /**
     * Location of module relative to BASE_DIR. This must contain the following dirs
     * - plugins
     * - themes
     * - skins
     *
     * Supports vendor/module:path
     */
    private static string $base_dir = 'silverstripe/htmleditor-tinymce:client/dist/tinymce';

    /**
     * Location of tinymce translation files relative to BASE_DIR.
     *
     * Supports vendor/module:path
     */
    private static string $lang_dir = 'silverstripe/htmleditor-tinymce:client/tinymce_lang';

    /**
     * Extra editor.css file paths.
     *
     * Supports vendor/module:path syntax
     */
    private static array $editor_css = [
        'silverstripe/htmleditor-tinymce:client/dist/styles/editor.css',
    ];

    /**
     * List of image size preset that will appear when you select an image. Each preset can have the following:
     * * `name` to store an internal name for the preset (required)
     * * `i18n` to store a translation key (e.g.: `TinyMCEConfig.BESTFIT`)
     * * `text` that will appear in the button (should be the default English translation)
     * * `width` which will define the horizontal size of the preset. If not provided, the preset will match the
     *   original size of the image.
     * @var array[]
     */
    private static array $image_size_presets = [
        [
            'width' => 600,
            'i18n' => TinyMCEConfig::class . '.BEST_FIT',
            'text' => 'Best fit',
            'name' => 'bestfit',
            'default' => true,
        ],
        [
            'i18n' => TinyMCEConfig::class . '.ORIGINAL',
            'text' => 'Original',
            'name' => 'originalsize',
        ],
    ];

    /**
     * Default TinyMCE JS options which apply to all new configurations.
     *
     * @link https://www.tiny.cloud/docs/tinymce/6/tinydrive-getting-started/#configure-the-required-tinymce-options
     */
    private static array $default_options = [
        'fix_list_elements' => true, // https://www.tiny.cloud/docs/tinymce/6/content-filtering/#fix_list_elements
        'formats' => [
            'alignleft' => [
                [
                    'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,li',
                    'classes' => 'text-left'
                ],
                [
                    'selector' => 'div,ul,ol,table,img,figure',
                    'classes' => 'left'
                ]
            ],
            'aligncenter' => [
                [
                    'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,li',
                    'classes' => 'text-center'
                ],
                [
                    'selector' => 'div,ul,ol,table,img,figure',
                    'classes' => 'center'
                ]
            ],
            'alignright' => [
                [
                    'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,li',
                    'classes' => 'text-right'
                ],
                [
                    'selector' => 'div,ul,ol,table,img,figure',
                    'classes' => 'right'
                ]
            ],
            'alignjustify' => [
                [
                    'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,li',
                    'classes' => 'text-justify'
                ],
            ],
        ],
        'friendly_name' => '(Please set a friendly name for this config)',
        'priority' => 0, // used for Per-member config override
        'browser_spellcheck' => true,
        'body_class' => 'typography',
        'statusbar' => true,
        'elementpath' => true, // https://www.tiny.cloud/docs/tinymce/6/statusbar-configuration-options/#elementpath
        'relative_urls' => true,
        'remove_script_host' => true,
        'convert_urls' => false, // Prevent site-root images being rewritten to base relative
        'menubar' => false,
        'language' => 'en',
        'branding' => false,
        'promotion' => false,
        'upload_folder_id' => null, // Set folder ID for insert media dialog
        // https://www.tiny.cloud/docs/tinymce/6/autolink/#example-using-link_default_target
        'link_default_target' => '_blank',
        'convert_unsafe_embeds' => true, // SS-2024-001
    ];

    protected static string $configType = 'tinyMCE';

    protected static string $schemaComponent = 'TinyMceHtmlEditorField';

    /**
     * List of content css files to use for this instance, or null to default to editor_css config.
     *
     * @var string[]|null
     */
    protected ?array $contentCSS = null;

    private array $options = [];

    /**
     * Holder list of enabled plugins
     */
    protected array $plugins = [
        'anchor' => null,
        'table' => null,
        'emoticons' => null,
        'code' => null,
        'image' => null,
        'importcss' => null,
        'lists' => null,
        'autolink' => null,
        'searchreplace' => null,
        'visualblocks' => null,
        'wordcount' => null,
    ];

    /**
     * Name of the TinyMCE theme to use
     */
    protected string $theme = 'silver';

    public function __construct()
    {
        $this->options = static::config()->get('default_options');
    }

    /**
     * Get the theme name
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * Set the theme name
     */
    public function setTheme(string $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Holder list of buttons, organised by line. This array is 1-based indexed array
     *
     * {@link https://www.tiny.cloud/docs/tinymce/6/basic-setup/#toolbar-configuration}
     *
     * @var array
     */
    protected $buttons = [
        1 => [
            'bold', 'italic', 'underline', 'removeformat', '|',
            'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
            'bullist', 'numlist', 'outdent', 'indent',
        ],
        2 => [
            'blocks', '|',
            'pastetext', '|',
            'table', 'sslink', 'unlink', '|',
            'code', 'visualblocks'
        ],
        3 => []
    ];

    public function getOption(string $key): mixed
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return null;
    }

    public function setOption(string $key, mixed $value): static
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function setOptions(array $options): static
    {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getAttributes(): array
    {
        return array_merge(
            parent::getAttributes(),
            [
                'data-config' => json_encode($this->getConfig()),
            ]
        );
    }

    public function setRows(int $numRows): static
    {
        parent::setRows($numRows);
        $rowHeight = static::config()->get('fixed_row_height');
        $height = $numRows * $rowHeight;
        if ($height) {
            $this->setOption('height', 'auto');
            $this->setOption('row_height', sprintf('%dpx', $height));
        }
        return $this;
    }

    public function getElementRuleSet(): HTMLEditorRuleSet
    {
        $ruleSet = new HTMLEditorRuleSet();
        $valid = $this->getOption('valid_elements');
        if ($valid) {
            $this->parseElementRules($valid, $ruleSet);
        }

        $valid = $this->getOption('extended_valid_elements');
        if ($valid) {
            $this->parseElementRules($valid, $ruleSet);
        }

        return $ruleSet;
    }

    public function setElementRuleSet(HTMLEditorRuleSet $ruleset): static
    {
        $this->setOptions([
            'valid_elements' => $this->convertRuleSetToValidElements($ruleset),
            'extended_valid_elements' => '',
        ]);
        return $this;
    }

    private function convertRuleSetToValidElements(HTMLEditorRuleSet $ruleset): string
    {
        $validElements = [];
        $globalAttributes = $ruleset->getGlobalRule()->getAttributeRules();
        if (!empty($globalAttributes)) {
            $validElements[] = '@' . $this->convertAttributeRules($globalAttributes);
        }
        foreach ($ruleset->getElementSubstitutionRules() as $from => $to) {
            $validElements[] = "$to/$from";
        }
        foreach ($ruleset->getElementRules() as $elementRule) {
            $ruleDefinition = '';
            if ($elementRule->getPadEmpty()) {
                $ruleDefinition .= '#';
            } elseif ($elementRule->getRemoveIfEmpty()) {
                $ruleDefinition .= '-';
            }
            $name = $elementRule->getName();
            if ($elementRule->getNameIsPattern()) {
                $name = HTMLEditorRuleSet::regexToPattern($name);
            }
            $ruleDefinition .= $name;
            if ($elementRule->getRemoveIfNoAttributes()) {
                $ruleDefinition .= '!';
            }
            $ruleDefinition .= $this->convertAttributeRules($elementRule->getAttributeRules());
            $validElements[] = $ruleDefinition;
        }
        return implode(',', $validElements);
    }

    /**
     * Converts an array of HTMLEditorAttributeRule objects to a string compatible with the valid_elements option.
     *
     * @param HTMLEditorAttributeRule[] $attributeRules
     */
    private function convertAttributeRules(array $attributeRules): string
    {
        if (empty($attributeRules)) {
            return '';
        }
        $attrs = [];
        foreach ($attributeRules as $rule) {
            $attr = $rule->getIsRequired() ? '!' : '';
            $name = $rule->getName();
            if ($rule->getNameIsPattern()) {
                $name = HTMLEditorRuleSet::regexToPattern($name);
            }
            $attr .= $name;
            $defaultValue = $rule->getDefaultValue();
            $forcedValue = $rule->getForcedValue();
            $validValues = $rule->getValidValues();
            if ($defaultValue !== null) {
                $attr .= "=$defaultValue";
            } elseif ($forcedValue !== null) {
                $attr .= "~$forcedValue";
            } elseif (!empty($validValues)) {
                $attr .= '<' . implode('?', $validValues);
            }

            $attrs[] = $attr;
        }
        return '[' . implode('|', $attrs) . ']';
    }

    /**
     * Given a valid_elements string, parse out the actual element and attribute rules and add to the
     * internal allow list
     *
     * Logic based heavily on javascript version from tiny_mce_src.js
     *
     * @param string $validElementsConfig The valid_elements or extended_valid_elements string to add to the allow list
     * @see https://www.tiny.cloud/docs/tinymce/6/content-filtering/#valid_elements
     */
    private function parseElementRules($validElementsConfig, HTMLEditorRuleSet $ruleSet): void
    {
        $elementRuleRegExp = '/^([#+\-])?([^\[!\/]+)(?:\/([^\[!]+))?(?:(!?)\[([^\]]+)])?$/';
        $attrRuleRegExp = '/^([!\-])?(\w+[\\:]:\w+|[^=~<]+)?(?:([=~<])(.*))?$/';
        $globalRule = $ruleSet->getGlobalRule();

        foreach (explode(',', $validElementsConfig ?? '') as $validElement) {
            if (preg_match($elementRuleRegExp ?? '', $validElement ?? '', $matches)) {
                $prefix = $matches[1] ?? null;
                $elementName = $matches[2] ?? null;
                $alias = $matches[3] ?? '';
                $attrModifier = $matches[4] ?? null;
                $attrData = $matches[5] ?? null;
                $elementNameIsPattern = HTMLEditorRuleSet::nameIsPattern($elementName);

                if ($elementNameIsPattern) {
                    $elementName = HTMLEditorRuleSet::patternToRegex($elementName);
                }

                // Use the global rule, or create a new one
                if ($elementName === '@') {
                    $elementRule = $globalRule;
                } else {
                    // Note that this intentionally overrides any pre-existing rule for the same element
                    $elementRule = new HTMLEditorElementRule(
                        $elementName,
                        $elementNameIsPattern,
                        padEmpty: $prefix === '#',
                        removeIfEmpty: $prefix === '-',
                        removeIfNoAttributes: $attrModifier === '!'
                    );
                }

                // Attributes defined
                if ($attrData) {
                    foreach (explode('|', $attrData ?? '') as $attr) {
                        if (preg_match($attrRuleRegExp ?? '', $attr ?? '', $matches)) {
                            $attrType = $matches[1] ?? null;
                            // Note the preg_replace converts any "\:" or "::" in the name to a single ":".
                            // This allows attributes like "xml:lang" and matches how TinyMCE's js handles this.
                            $attrName = isset($matches[2]) ? preg_replace('/[\\:]:/', ':', $matches[2]) : null;
                            $attrNameIsPattern = HTMLEditorRuleSet::nameIsPattern($attrName);
                            $prefix = $matches[3] ?? null;
                            $value = $matches[4] ?? [];

                            if ($attrNameIsPattern) {
                                $attrName = HTMLEditorRuleSet::patternToRegex($attrName);
                            }

                            // Denied from global
                            // (note it's unclear what this means, but this is done by TinyMCE js as well)
                            if ($attrType === '-') {
                                $elementRule->removeAttributeRule($attrName);
                                continue;
                            }

                            // Attribute value rules
                            $valueType = HTMLEditorAttributeRule::VALUE_VALID;
                            switch ($prefix) {
                                case '=':
                                    $valueType = HTMLEditorAttributeRule::VALUE_DEFAULT;
                                    break;
                                case '~':
                                    $valueType = HTMLEditorAttributeRule::VALUE_FORCED;
                                    break;
                                case '<':
                                    // Allow-list of values
                                    $value = explode('?', $value ?? '');
                                    break;
                            }

                            $attrRule = new HTMLEditorAttributeRule($attrName, $attrNameIsPattern, $value, $valueType);

                            if ($attrType === '!') {
                                $attrRule->setIsRequired(true);
                            }

                            // Add the attribute rule to the element rule.
                            $elementRule->addAttributeRule($attrRule);
                        }
                    }
                }

                // Nothing further to do for the global rule
                if ($elementRule === $globalRule) {
                    continue;
                }

                // Add the element rule to the ruleset.
                $ruleSet->addElementRule($elementRule);
                if ($alias) {
                    $ruleSet->addElementSubstitutionRule($alias, $elementName);
                }
            }
        }
    }

    /**
     * Enable one or several plugins. Will maintain unique list if already
     * enabled plugin is re-passed. If passed in as a map of plugin-name to path,
     * the plugin will be loaded by tinymce.PluginManager.load() instead of through tinyMCE.init().
     * Keep in mind that these externals plugins require a dash-prefix in their name.
     *
     * @see https://www.tiny.cloud/docs/tinymce/6/editor-important-options/#external_plugins
     *
     * If passing in a non-associative array, the plugin name should be located in the standard tinymce
     * plugins folder.
     *
     * If passing in an associative array, the key of each item should be the plugin name.
     * The value of each item is one of:
     *  - null - Will be treated as a standard plugin in the standard location
     *  - relative path - Will be treated as a relative url
     *  - absolute url - Some url to an external plugin
     *  - An instance of ModuleResource object containing the plugin
     *
     * @param string|array ...$plugin a string, or several strings, or a single array of strings - The plugins to enable
     */
    public function enablePlugins(string|array $plugin): static
    {
        $plugins = func_get_args();
        if (is_array(current($plugins ?? []))) {
            $plugins = current($plugins ?? []);
        }
        foreach ($plugins as $name => $path) {
            // if plugins are passed without a path
            if (is_numeric($name)) {
                $name = $path;
                $path = null;
            }
            if (!array_key_exists($name, $this->plugins ?? [])) {
                $this->plugins[$name] = $path;
            }
        }
        return $this;
    }

    /**
     * Enable one or several plugins. Will properly handle being passed a plugin that is already disabled
     * @param string|array ...$plugin a string, or several strings, or a single array of strings - The plugins to enable
     */
    public function disablePlugins(string|array $plugin): static
    {
        $plugins = func_get_args();
        if (is_array(current($plugins ?? []))) {
            $plugins = current($plugins ?? []);
        }
        foreach ($plugins as $name) {
            unset($this->plugins[$name]);
        }
        return $this;
    }

    /**
     * Gets the list of all enabled plugins as an associative array.
     * Array keys are the plugin names, and values are potentially the plugin location,
     * or ModuleResource object
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get list of plugins without custom locations, which is the set of
     * plugins which can be loaded via the standard plugin path, and could
     * potentially be minified
     */
    public function getInternalPlugins(): array
    {
        // Return only plugins with no custom url
        $plugins = [];
        foreach ($this->getPlugins() as $name => $url) {
            if (empty($url)) {
                $plugins[] = $name;
            }
        }
        return $plugins;
    }

    /**
     * Get all button rows, skipping empty rows
     */
    public function getButtons(): array
    {
        return array_filter($this->buttons ?? []);
    }

    /**
     * Totally re-set the buttons on a given line
     *
     * @param int $line The line number to redefine, from 1 to 3
     * @param string|string[] $buttons,... An array of strings, or one or more strings.
     *                                     The button names to assign to this line.
     */
    public function setButtonsForLine(int $line, string|array $buttons): static
    {
        if (func_num_args() > 2) {
            $buttons = func_get_args();
            array_shift($buttons);
        }
        $this->buttons[$line] = is_array($buttons) ? $buttons : [$buttons];
        return $this;
    }

    /**
     * Add buttons to the end of a line
     * @param int $line The line number to redefine, from 1 to 3
     * @param string|string[] ...$buttons A string or several strings, or a single array of strings.
     * The button names to add to this line
     */
    public function addButtonsToLine(int $line, string|array $buttons): static
    {
        if (func_num_args() > 2) {
            $buttons = func_get_args();
            array_shift($buttons);
        }
        if (!is_array($buttons)) {
            $buttons = [$buttons];
        }
        foreach ($buttons as $button) {
            $this->buttons[$line][] = $button;
        }
        return $this;
    }

    /**
     * Internal function for adding and removing buttons related to another button
     * @param string $name The name of the button to modify
     * @param int $offset The offset relative to that button to perform an array_splice at.
     * 0 for before $name, 1 for after.
     * @param int $del The number of buttons to remove at the position given by index(string) + offset
     * @param mixed $add An array or single item to insert at the position given by index(string) + offset,
     * or null for no insertion
     * @return bool True if $name matched a button, false otherwise
     */
    protected function modifyButtons(string $name, int $offset, int $del = 0, mixed $add = null): bool
    {
        foreach ($this->buttons as &$buttons) {
            if (($idx = array_search($name, $buttons ?? [])) !== false) {
                if ($add) {
                    array_splice($buttons, $idx + $offset, $del, $add);
                } else {
                    array_splice($buttons, $idx + $offset, $del);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Insert buttons before the first occurrence of another button
     * @param string $before the name of the button to insert other buttons before
     * @param string|string[] ...$buttons a string, or several strings, or a single array of strings.
     * The button names to insert before that button
     * @return bool True if insertion occurred, false if it did not (because the given button name was not found)
     */
    public function insertButtonsBefore(string $before, string|array $buttons): bool
    {
        if (func_num_args() > 2) {
            $buttons = func_get_args();
            array_shift($buttons);
        }
        if (!is_array($buttons)) {
            $buttons = [$buttons];
        }
        return $this->modifyButtons($before, 0, 0, $buttons);
    }

    /**
     * Insert buttons after the first occurrence of another button
     * @param string $after the name of the button to insert other buttons before
     * @param string|string[] ...$buttons a string, or several strings, or a single array of strings.
     * The button names to insert after that button
     * @return bool True if insertion occurred, false if it did not (because the given button name was not found)
     */
    public function insertButtonsAfter(string $after, string|array $buttons): bool
    {
        if (func_num_args() > 2) {
            $buttons = func_get_args();
            array_shift($buttons);
        }
        if (!is_array($buttons)) {
            $buttons = [$buttons];
        }
        return $this->modifyButtons($after, 1, 0, $buttons);
    }

    /**
     * Remove the first occurrence of buttons
     * @param string|string[] $buttons,... An array of strings, or one or more strings. The button names to remove.
     */
    public function removeButtons(string|array $buttons): void
    {
        if (func_num_args() > 1) {
            $buttons = func_get_args();
        }
        if (!is_array($buttons)) {
            $buttons = [$buttons];
        }
        foreach ($buttons as $button) {
            $this->modifyButtons($button, 0, 1);
        }
    }

    /**
     * Generate the JavaScript that will set TinyMCE's configuration:
     * - Parse all configurations into JSON objects to be used in JavaScript
     * - Includes TinyMCE and configurations using the {@link Requirements} system
     */
    protected function getConfig(): array
    {
        $settings = $this->getOptions();

        // https://www.tiny.cloud/docs/tinymce/6/url-handling/#document_base_url
        $settings['document_base_url'] = rtrim(Director::absoluteBaseURL(), '/') . '/';

        // https://www.tiny.cloud/docs/tinymce/6/apis/tinymce.root/#properties
        $baseResource = $this->getTinyMCEResource();
        if ($baseResource instanceof ModuleResource) {
            $tinyMCEBaseURL = $baseResource->getURL();
        } else {
            $tinyMCEBaseURL = Controller::join_links(Director::baseURL(), $baseResource);
        }
        $settings['baseURL'] = $tinyMCEBaseURL;

        // map all plugins to absolute urls for loading
        $plugins = [];
        foreach ($this->getPlugins() as $plugin => $path) {
            if ($path instanceof ModuleResource) {
                $path = Director::absoluteURL($path->getURL());
            } elseif (!$path) {
                // Empty paths: Convert to urls in standard base url
                $path = Controller::join_links(
                    $tinyMCEBaseURL,
                    "plugins/{$plugin}/plugin.min.js"
                );
            } elseif (!Director::is_absolute_url($path)) {
                // Non-absolute urls are made absolute
                $path = Director::absoluteURL((string) $path);
            }
            $plugins[$plugin] = $path;
        }

        // https://www.tiny.cloud/docs/tinymce/6/editor-important-options/#external_plugins
        if ($plugins) {
            $settings['external_plugins'] = $plugins;
        }

        // https://www.tiny.cloud/docs/tinymce/6/basic-setup/#toolbar-configuration
        $buttons = $this->getButtons();
        $settings['toolbar'] = [];
        foreach ($buttons as $rowButtons) {
            $row = implode(' ', $rowButtons);
            if (count($buttons ?? []) > 1) {
                $settings['toolbar'][] = $row;
            } else {
                $settings['toolbar'] = $row;
            }
        }

        // https://www.tiny.cloud/docs/tinymce/6/add-css-options/#content_css
        $settings['content_css'] = $this->getEditorCSS();

        // https://www.tiny.cloud/docs/tinymce/6/editor-theme/#theme_url
        $theme = $this->getTheme();
        if (!Director::is_absolute_url($theme)) {
            $theme = Controller::join_links($tinyMCEBaseURL, "themes/{$theme}/theme.min.js");
        }
        $settings['theme_url'] = $theme;

        $this->initImageSizePresets($settings);

        // Set correct language if one was not explicitly set
        $settings['language'] ??= TinyMCEConfig::getTinymceLang();
        $langUrl = TinyMCEConfig::getTinymceLangUrl();
        if ($langUrl) {
            $settings['language_url'] ??= $langUrl;
        }

        // Send back
        return $settings;
    }

    /**
     * Initialise the image preset on the settings array. This is a custom configuration option that asset-admin reads
     * to provide some preset image sizes.
     */
    private function initImageSizePresets(array &$settings): void
    {
        if (empty($settings['image_size_presets'])) {
            $settings['image_size_presets'] = static::config()->get('image_size_presets');
        }

        foreach ($settings['image_size_presets'] as &$preset) {
            if (isset($preset['width'])) {
                $preset['width'] = (int) $preset['width'];
            }

            if (isset($preset['i18n'])) {
                /** @phpstan-ignore translation.key (we need the key to be dynamic here) */
                $preset['text'] = _t(
                    $preset['i18n'],
                    isset($preset['text']) ? $preset['text'] : ''
                );
            } elseif (empty($preset['text']) && isset($preset['width'])) {
                $preset['text'] = _t(
                    TinyMCEConfig::class . '.PIXEL_WIDTH',
                    '{width} pixels',
                    $preset
                );
            }
        }
    }

    /**
     * Get location of all editor.css files.
     * All resource specifiers are resolved to urls.
     */
    protected function getEditorCSS(): array
    {
        $editor = [];
        $resourceLoader = ModuleResourceLoader::singleton();
        foreach ($this->getContentCSS() as $contentCSS) {
            $editor[] = $resourceLoader->resolveURL($contentCSS);
        }
        return $editor;
    }

    /**
     * Get list of resource paths to css files.
     *
     * Will default to `editor_css` config, as well as any themed `editor.css` files.
     * Use setContentCSS() to override.
     *
     * @return string[]
     */
    public function getContentCSS(): array
    {
        // Prioritise instance specific content
        if (isset($this->contentCSS)) {
            return $this->contentCSS;
        }

        // Add standard editor.css
        $editor = [];
        $editorCSSFiles = $this->config()->get('editor_css');
        if ($editorCSSFiles) {
            foreach ($editorCSSFiles as $editorCSS) {
                $editor[] = $editorCSS;
            }
        }

        // Themed editor.css
        $themes = HTMLEditorConfig::getThemes() ?: SSViewer::get_themes();
        $themedEditor = ThemeResourceLoader::inst()->findThemedCSS('editor', $themes);
        if ($themedEditor) {
            $editor[] = $themedEditor;
        }
        return $editor;
    }

    /**
     * Set explicit set of CSS resources to use for `content_css` option.
     *
     * Note: If merging with default paths, you should call getContentCSS() and merge
     * prior to assignment.
     *
     * @param string[] $css Array of resource paths. Supports module prefix,
     * e.g. `silverstripe/admin:client/dist/styles/editor.css`
     */
    public function setContentCSS(array $css): static
    {
        $this->contentCSS = $css;
        return $this;
    }

    /**
     * Generate gzipped TinyMCE configuration including plugins and languages.
     * This ends up "pre-loading" TinyMCE bundled with the required plugins
     * so that multiple HTTP requests on the client don't need to be made.
     */
    public function getScriptURL(): string
    {
        $generator = Injector::inst()->get(TinyMCEScriptGenerator::class);
        return $generator->getScriptURL($this);
    }

    public function init(): void
    {
        // include TinyMCE Javascript
        Requirements::javascript($this->getScriptURL());
    }

    public function getConfigSchemaData(): array
    {
        $data = parent::getConfigSchemaData();
        $data['editorjs'] = $this->getScriptURL();
        return $data;
    }

    /**
     * Get the current tinyMCE language
     */
    public static function getTinymceLang(): string
    {
        $lang = static::config()->get('tinymce_lang');
        $locale = i18n::get_locale();
        if (isset($lang[$locale])) {
            return $lang[$locale];
        }
        return 'en';
    }

    /**
     * Get the URL for the language pack of the current language
     */
    public static function getTinymceLangUrl(): string
    {
        $lang = static::getTinymceLang();
        $dir = static::config()->get('lang_dir');
        if ($lang !== 'en' && !empty($dir)) {
            $resource = ModuleResourceLoader::singleton()->resolveResource($dir);
            return Director::absoluteURL($resource->getRelativeResource($lang . '.js')->getURL());
        }
        return '';
    }

    /**
     * Returns the full filesystem path to TinyMCE resources (which could be different from the original tinymce
     * location in the module).
     *
     * Path will be absolute.
     */
    public function getTinyMCEResourcePath(): string
    {
        $resource = $this->getTinyMCEResource();
        if ($resource instanceof ModuleResource) {
            return $resource->getPath();
        }
        return Director::baseFolder() . '/' . $resource;
    }

    /**
     * Get front-end url to tinymce resources
     */
    public function getTinyMCEResourceURL(): string
    {
        $resource = $this->getTinyMCEResource();
        if ($resource instanceof ModuleResource) {
            return $resource->getURL();
        }
        return $resource;
    }

    /**
     * Get resource root for TinyMCE, either as a string or ModuleResource instance
     * Path will be relative to BASE_PATH if string.
     *
     * @throws Exception
     */
    public function getTinyMCEResource(): ModuleResource|string
    {
        $configDir = static::config()->get('base_dir');
        if ($configDir) {
            return ModuleResourceLoader::singleton()->resolveResource($configDir);
        }

        throw new Exception(sprintf(
            'If the silverstripe/admin module is not installed you must set the TinyMCE path in %s.base_dir',
            __CLASS__
        ));
    }

    /**
     * Sets the upload folder name used by the insert media dialog
     *
     * @param string $folderName
     * @return $this
     */
    public function setFolderName(string $folderName): TinyMCEConfig
    {
        $folder = Folder::find_or_make($folderName);
        $folderID = $folder ? $folder->ID : null;
        $this->setOption('upload_folder_id', $folderID);
        return $this;
    }

    public function provideI18nEntities()
    {
        $entities = [
            TinyMCEConfig::class . '.PIXEL_WIDTH' => '{width} pixels',
        ];
        foreach (static::config()->get('image_size_presets') as $preset) {
            if (empty($preset['i18n']) || empty($preset['text'])) {
                continue;
            }
            $entities[$preset['i18n']] = $preset['text'];
        }

        return $entities;
    }
}
