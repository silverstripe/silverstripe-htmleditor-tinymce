<?php

namespace SilverStripe\Forms\Tests\HTMLEditor;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Control\Director;
use SilverStripe\Control\SimpleResourceURLGenerator;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Core\Manifest\ModuleManifest;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Core\Manifest\ResourceURLGenerator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\HTMLEditor\HTMLEditorElementRule;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorRuleSet;
use SilverStripe\TinyMCE\TinyMCEConfig;

class TinyMCEConfigTest extends SapphireTest
{
    public function testEditorIdentifier()
    {
        $config = TinyMCEConfig::get('myconfig');
        $this->assertEquals('myconfig', $config->getOption('editorIdentifier'));
    }

    /**
     * Ensure that all TinyMCEConfig.tinymce_lang are valid
     */
    public function testLanguagesValid()
    {
        $configDir = TinyMCEConfig::config()->get('lang_dir');
        if (!$configDir) {
            $this->markTestSkipped('Test skipped without TinyMCE language resource folder being installed');
        }

        $langs = Director::baseFolder() . '/' . ModuleResourceLoader::resourcePath($configDir);

        // Test all langs exist as real files
        $checked = [];
        foreach (TinyMCEConfig::config()->get('tinymce_lang') as $locale => $resource) {
            // No need to check the same file twice.
            if (array_key_exists($resource, $checked)) {
                continue;
            }
            // Check valid
            $this->assertFileExists(
                "{$langs}/{$resource}.js",
                "Locale code {$locale} maps to {$resource}.js which exists"
            );
            // Check we don't simplify to locale when a specific version exists
            if (strpos($resource ?? '', '_') === false) {
                $this->assertFileDoesNotExist(
                    "{$langs}/{$locale}.js",
                    "Locale code {$locale} doesn't map to simple {$resource}.js when a better {$locale}.js is available"
                );
            }
            $checked[$resource] = true;
        }
    }

    public function testGetContentCSS()
    {
        TinyMCEConfig::config()->set('editor_css', [
            'silverstripe/framework:tests/php/Forms/HTMLEditor.css'
        ]);

        // Test default config
        $config = new TinyMCEConfig();
        $this->assertContains('silverstripe/framework:tests/php/Forms/HTMLEditor.css', $config->getContentCSS());

        // Test manual disable
        $config->setContentCSS([]);
        $this->assertEmpty($config->getContentCSS());

        // Test replacement config
        $config->setContentCSS([
            'silverstripe/framework:tests/php/Forms/HTMLEditor_another.css'
        ]);
        $this->assertEquals(
            [ 'silverstripe/framework:tests/php/Forms/HTMLEditor_another.css'],
            $config->getContentCSS()
        );
    }

    public function testProvideI18nEntities()
    {
        TinyMCEConfig::config()->set('image_size_presets', [
            ['i18n' => TinyMCEConfig::class . '.TEST', 'text' => 'Foo bar'],
            ['text' => 'No translation key'],
            ['i18n' => TinyMCEConfig::class . '.NO_TRANSLATION_TEXT'],
            ['i18n' => TinyMCEConfig::class . '.TEST_TWO', 'text' => 'Bar foo'],
        ]);

        $config = TinyMCEConfig::create();
        $translations = $config->provideI18nEntities();

        $this->assertEquals(
            3,
            sizeof($translations ?? []),
            'Only two presets have valid translation + the generic PIXEL_WIDTH one'
        );
        $this->assertEquals('Foo bar', $translations[TinyMCEConfig::class . '.TEST']);
        $this->assertEquals('Bar foo', $translations[TinyMCEConfig::class . '.TEST_TWO']);
        $this->assertEquals('{width} pixels', $translations[TinyMCEConfig::class . '.PIXEL_WIDTH']);
    }


    public function testEnablePluginsByString()
    {
        $c = new TinyMCEConfig();
        $c->enablePlugins('plugin1');
        $this->assertContains('plugin1', array_keys($c->getPlugins() ?? []));
    }

    public function testEnablePluginsByArray()
    {
        $c = new TinyMCEConfig();
        $c->enablePlugins(['plugin1', 'plugin2']);
        $this->assertContains('plugin1', array_keys($c->getPlugins() ?? []));
        $this->assertContains('plugin2', array_keys($c->getPlugins() ?? []));
    }

    public function testEnablePluginsByMultipleStringParameters()
    {
        $c = new TinyMCEConfig();
        $c->enablePlugins('plugin1', 'plugin2');
        $this->assertContains('plugin1', array_keys($c->getPlugins() ?? []));
        $this->assertContains('plugin2', array_keys($c->getPlugins() ?? []));
    }

    public function testEnablePluginsByArrayWithPaths()
    {
        // Disable nonces
        $urlGenerator = new SimpleResourceURLGenerator();
        Injector::inst()->registerService($urlGenerator, ResourceURLGenerator::class);

        Config::modify()->set(Director::class, 'alternate_base_url', 'http://mysite.com/subdir');
        $c = new TinyMCEConfig();
        $c->setTheme('modern');
        $c->setOption('language', 'es');
        $c->disablePlugins(
            'anchor',
            'table',
            'emoticons',
            'code',
            'image',
            'link',
            'importcss',
            'lists',
            'autolink',
            'searchreplace',
            'visualblocks',
            'wordcount',
            'help'
        );
        $c->enablePlugins(
            [
                'plugin1' => 'mypath/plugin1.js',
                'plugin2' => '/anotherbase/mypath/plugin2.js',
                'plugin3' => 'https://www.google.com/plugin.js',
                'plugin4' => null,
                'plugin5' => null,
            ]
        );
        $attributes = $c->getAttributes();
        $config = json_decode($attributes['data-config'] ?? '', true);
        $plugins = $config['external_plugins'];
        $this->assertNotEmpty($plugins);

        // Plugin specified via relative url
        $this->assertContains('plugin1', array_keys($plugins ?? []));
        $this->assertEquals(
            'http://mysite.com/subdir/mypath/plugin1.js',
            $plugins['plugin1']
        );

        // Plugin specified via root-relative url
        $this->assertContains('plugin2', array_keys($plugins ?? []));
        $this->assertEquals(
            'http://mysite.com/anotherbase/mypath/plugin2.js',
            $plugins['plugin2']
        );

        // Plugin specified with absolute url
        $this->assertContains('plugin3', array_keys($plugins ?? []));
        $this->assertEquals(
            'https://www.google.com/plugin.js',
            $plugins['plugin3']
        );

        // Plugin specified with standard location
        // Get the path dynamically, because CI uses _resources/client/dist/tinymce/plugins when run as nthe root.
        $module = ModuleLoader::getModule('silverstripe/htmleditor-tinymce');
        $pluginsPath = $module->getResource('client/dist/tinymce/plugins/')->getURL();
        $this->assertContains('plugin4', array_keys($plugins ?? []));
        $this->assertEquals(
            "$pluginsPath/plugin4/plugin.min.js",
            $plugins['plugin4']
        );

        // Check that internal plugins are extractable separately
        $this->assertEquals(['plugin4', 'plugin5'], $c->getInternalPlugins());
    }

    public function testDisablePluginsByString()
    {
        $c = new TinyMCEConfig();
        $c->enablePlugins('plugin1');
        $c->disablePlugins('plugin1');
        $this->assertNotContains('plugin1', array_keys($c->getPlugins() ?? []));
    }

    public function testDisablePluginsByArray()
    {
        $c = new TinyMCEConfig();
        $c->enablePlugins(['plugin1', 'plugin2']);
        $c->disablePlugins(['plugin1', 'plugin2']);
        $this->assertNotContains('plugin1', array_keys($c->getPlugins() ?? []));
        $this->assertNotContains('plugin2', array_keys($c->getPlugins() ?? []));
    }

    public function testDisablePluginsByMultipleStringParameters()
    {
        $c = new TinyMCEConfig();
        $c->enablePlugins('plugin1', 'plugin2');
        $c->disablePlugins('plugin1', 'plugin2');
        $this->assertNotContains('plugin1', array_keys($c->getPlugins() ?? []));
        $this->assertNotContains('plugin2', array_keys($c->getPlugins() ?? []));
    }

    public function testDisablePluginsByArrayWithPaths()
    {
        $c = new TinyMCEConfig();
        $c->enablePlugins(['plugin1' => '/mypath/plugin1', 'plugin2' => '/mypath/plugin2']);
        $c->disablePlugins(['plugin1', 'plugin2']);
        $plugins = $c->getPlugins();
        $this->assertNotContains('plugin1', array_keys($plugins ?? []));
        $this->assertNotContains('plugin2', array_keys($plugins ?? []));
    }

    public function testExceptionThrownWhenBaseDirAbsent()
    {
        TinyMCEConfig::config()->remove('base_dir');
        ModuleLoader::inst()->pushManifest(new ModuleManifest(__DIR__));

        try {
            $config = new TinyMCEConfig();
            $this->expectException(Exception::class);
            $this->expectExceptionMessageMatches('/module is not installed/');
            $config->getScriptURL();
        } finally {
            ModuleLoader::inst()->popManifest();
        }
    }

    public function testGetAttributes()
    {
        // Create an editor and set fixed_row_height to 0
        $editor = new HTMLEditorField('Content');
        TinyMCEConfig::config()->set('fixed_row_height', 0);
        // Get the attributes and config from the editor
        $attributes = $editor->getAttributes();
        $dataConfig = json_decode($attributes['data-config'], true);
        // If fixed_row_height is 0 then row_height and height config are not set
        $this->assertArrayNotHasKey('height', $dataConfig, 'Config height should not be set');
        $this->assertArrayNotHasKey('row_height', $dataConfig, 'Config row_height should not be set');
        // Set the fixed_row_height back to 20px
        TinyMCEConfig::config()->set('fixed_row_height', 20);
        // Set the rows to 0
        $editor->setRows(0);
        // Get the attributes and config from the editor
        $attributes = $editor->getAttributes();
        $dataConfig = json_decode($attributes['data-config'], true);
        // If rows is 0 then row_height and height config are not set
        $this->assertArrayNotHasKey('height', $dataConfig, 'Config height should not be set');
        $this->assertArrayNotHasKey('row_height', $dataConfig, 'Config row_height should not be set');
        // Set the rows to 5
        $editor->setRows(5);
        // Get the attributes and config from the editor
        $attributes = $editor->getAttributes();
        $dataConfig = json_decode($attributes['data-config']);
        // Check the height is set to auto and the row height is set to 100px (5 rows * 20px)
        $this->assertEquals('auto', $dataConfig->height, 'Config height is not set');
        $this->assertEquals('100px', $dataConfig->row_height, 'Config row_height is not set');
        // Change the row height to 60px and set the rows to 3
        $editor->setRows(3);
        // Get the attributes and config from the editor
        $attributes = $editor->getSchemaStateDefaults();
        $dataConfig = json_decode($attributes['data']['attributes']['data-config']);
        // Check the height is set to auto and the row height is set to 60px (3 rows * 20px)
        $this->assertEquals('auto', $dataConfig->height, 'Config height is not set');
        $this->assertEquals('60px', $dataConfig->row_height, 'Config row_height is not set');
    }

    public static function provideGetElementRuleSet(): array
    {
        $expected = [
            HTMLEditorElementRule::GLOBAL_NAME => [
                'attributes' => [
                    'id' => [
                        'name' => 'id',
                        'nameIsPattern' => false,
                        'default' => null,
                        'forced' => null,
                        'valid' => [],
                        'isRequired' => false,
                    ],
                    'class' => [
                        'name' => 'class',
                        'nameIsPattern' => false,
                        'default' => null,
                        'forced' => null,
                        'valid' => [],
                        'isRequired' => false,
                    ],
                    'style' => [
                        'name' => 'style',
                        'nameIsPattern' => false,
                        'default' => null,
                        'forced' => null,
                        'valid' => [],
                        'isRequired' => false,
                    ],
                ]
            ],
            'b' => ['convertTo' => 'strong'],
            'a' => [
                'name' => 'a',
                'nameIsPattern' => false,
                'padEmpty' => false,
                'removeIfEmpty' => false,
                'removeIfNoAttributes' => false,
                'attributes' => [
                    'rel' => [
                        'name' => 'rel',
                        'nameIsPattern' => false,
                        'default' => null,
                        'forced' => null,
                        'valid' => [],
                        'isRequired' => false,
                    ],
                    'href' => [
                        'name' => 'href',
                        'nameIsPattern' => false,
                        'default' => null,
                        'forced' => null,
                        'valid' => [],
                        'isRequired' => true,
                    ],
                ],
            ],
            'strong' => [
                'name' => 'strong',
                'nameIsPattern' => false,
                'padEmpty' => false,
                'removeIfEmpty' => true,
                'removeIfNoAttributes' => false,
                'attributes' => [
                    'class' => [
                        'name' => 'class',
                        'nameIsPattern' => false,
                        'default' => null,
                        'forced' => null,
                        'valid' => ['class1', 'class2', 'class3'],
                        'isRequired' => false,
                    ],
                ],
            ],
            'span' => [
                'name' => 'span',
                'nameIsPattern' => false,
                'padEmpty' => false,
                'removeIfEmpty' => false,
                'removeIfNoAttributes' => true,
                'attributes' => [
                    '/^data-.+$/' => [
                        'name' => '/^data-.+$/',
                        'nameIsPattern' => true,
                        'default' => null,
                        'forced' => null,
                        'valid' => [],
                        'isRequired' => false,
                    ],
                ],
            ],
        ];
        return [
            'nothing valid' => [
                'validElements' => '',
                'extendedValidElements' => '',
                'expected' => [HTMLEditorElementRule::GLOBAL_NAME => ['attributes' => []]],
            ],
            'valid_elements only' => [
                'validElements' => '@[id|class|style],a[rel|!href],-strong/b[class<class1?class2?class3],'
                                   . 'span![data-+],',
                'extendedValidElements' => '',
                'expected' => $expected,
            ],
            'extended_valid only' => [
                'validElements' => '',
                'extendedValidElements' => '@[id|class|style],a[rel|!href],-strong/b[class<class1?class2?class3],'
                                           . 'span![data-+]',
                'expected' => $expected,
            ],
            'configs get combined' => [
                'validElements' => '@[id|class|style],a[rel|!href]',
                'extendedValidElements' => '-strong/b[class<class1?class2?class3],span![data-+]',
                'expected' => $expected,
            ],
            'some more options' => [
                'validElements' => '@[class=default-value],t?[align~left],#p![*],div',
                'extendedValidElements' => '',
                'expected' => [
                    HTMLEditorElementRule::GLOBAL_NAME => [
                        'attributes' => [
                            'class' => [
                                'name' => 'class',
                                'nameIsPattern' => false,
                                'default' => 'default-value',
                                'forced' => null,
                                'valid' => [],
                                'isRequired' => false,
                            ],
                        ]
                    ],
                    'p' => [
                        'name' => 'p',
                        'nameIsPattern' => false,
                        'padEmpty' => true,
                        'removeIfEmpty' => false,
                        'removeIfNoAttributes' => true,
                        'attributes' => [
                            '/^.*$/' => [
                                'name' => '/^.*$/',
                                'nameIsPattern' => true,
                                'default' => null,
                                'forced' => null,
                                'valid' => [],
                                'isRequired' => false,
                            ],
                        ],
                    ],
                    'div' => [
                        'name' => 'div',
                        'nameIsPattern' => false,
                        'padEmpty' => false,
                        'removeIfEmpty' => false,
                        'removeIfNoAttributes' => false,
                        'attributes' => [],
                    ],
                    // This comes last because pattern elements are merged onto the end of the array
                    '/^t.?$/' => [
                        'name' => '/^t.?$/',
                        'nameIsPattern' => true,
                        'padEmpty' => false,
                        'removeIfEmpty' => false,
                        'removeIfNoAttributes' => false,
                        'attributes' => [
                            'align' => [
                                'name' => 'align',
                                'nameIsPattern' => false,
                                'default' => null,
                                'forced' => 'left',
                                'valid' => ['left'],
                                'isRequired' => false,
                            ],
                        ],
                    ],
                ],
            ],
            // During sanitisation, all of these listed elements would become "<section>"
            // and use the section rules.
            'chained conversions' => [
                'validElements' => 'div/span[id],p/div[class]',
                'extendedValidElements' => 'section/p[style]',
                'expected' => [
                    HTMLEditorElementRule::GLOBAL_NAME => ['attributes' => []],
                    'span' => ['convertTo' => 'div'],
                    'div' => ['convertTo' => 'p'],
                    'p' => ['convertTo' => 'section'],
                    'section' => [
                        'name' => 'section',
                        'nameIsPattern' => false,
                        'padEmpty' => false,
                        'removeIfEmpty' => false,
                        'removeIfNoAttributes' => false,
                        'attributes' => [
                            'style' => [
                                'name' => 'style',
                                'nameIsPattern' => false,
                                'default' => null,
                                'forced' => null,
                                'valid' => [],
                                'isRequired' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('provideGetElementRuleSet')]
    public function testGetElementRuleSet(string $validElements, string $extendedValidElements, array $expected): void
    {
        $config = new TinyMCEConfig();
        $config->setOptions([
            'valid_elements' => $validElements,
            'extended_valid_elements' => $extendedValidElements,
        ]);
        $ruleset = $config->getElementRuleSet();

        $actual = $this->getElementRulesAsArray($ruleset);
        $this->assertSame($expected, $actual);
    }

    private function getElementRulesAsArray(HTMLEditorRuleSet $ruleset): array
    {
        $elementRules = [
            HTMLEditorElementRule::GLOBAL_NAME => [
                'attributes' => $this->getAttributeRulesAsArray($ruleset->getGlobalRule())
            ]
        ];
        foreach ($ruleset->getElementSubstitutionRules() as $from => $to) {
            $elementRules[$from] = ['convertTo' => $to];
        }
        foreach ($ruleset->getElementRules() as $name => $elementRule) {
            $elementRules[$name] = [
                'name' => $elementRule->getName(),
                'nameIsPattern' => $elementRule->getNameIsPattern(),
                'padEmpty' => $elementRule->getPadEmpty(),
                'removeIfEmpty' => $elementRule->getRemoveIfEmpty(),
                'removeIfNoAttributes' => $elementRule->getRemoveIfNoAttributes(),
                'attributes' => $this->getAttributeRulesAsArray($elementRule),
            ];
        }
        return $elementRules;
    }

    private function getAttributeRulesAsArray(HTMLEditorElementRule $elementRule): array
    {
        $attributeRules = [];
        foreach ($elementRule->getAttributeRules() as $name => $attributeRule) {
            $attributeRules[$name] = [
                'name' => $attributeRule->getName(),
                'nameIsPattern' => $attributeRule->getNameIsPattern(),
                'default' => $attributeRule->getDefaultValue(),
                'forced' => $attributeRule->getForcedValue(),
                'valid' => $attributeRule->getValidValues(),
                'isRequired' => $attributeRule->getIsRequired(),
            ];
        }
        return $attributeRules;
    }
}
