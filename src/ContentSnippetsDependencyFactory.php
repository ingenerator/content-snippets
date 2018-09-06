<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets;


use Ingenerator\ContentSnippets\Repository\DoctrineContentSnippetRepository;

class ContentSnippetsDependencyFactory
{

    public static function definitions()
    {
        return [
            'content_snippets' => [
                'content_filter' => [
                    '_settings' => [
                        'class'     => ContentSnippetContentFilter::class,
                        'arguments' => [
                            '%content_snippets.html_purifier.purifier%',
                        ],
                    ],
                ],
                'html_purifier'  => [
                    'config'   => [
                        '_settings' => [
                            'class'       => static::class,
                            'constructor' => 'makePurifierConfig',
                            'arguments'   => [],
                        ],
                    ],
                    'purifier' => [
                        '_settings' => [
                            'class'     => \HTMLPurifier::class,
                            'arguments' => [
                                '%content_snippets.html_purifier.config%',
                            ],
                        ],
                    ],
                ],
                'repository'     => [
                    '_settings' => [
                        'class'     => DoctrineContentSnippetRepository::class,
                        'arguments' => [
                            '%doctrine.entity_manager%',
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function controllerDefinitions()
    {
        return [];
    }

    public static function makePurifierConfig()
    {
        return \HTMLPurifier_Config::create(
            [
                'AutoFormat.RemoveEmpty'                  => FALSE,
                'Attr.AllowedFrameTargets'                => ['_blank'],
                'AutoFormat.RemoveEmpty.RemoveNbsp'       => TRUE,
                'AutoFormat.RemoveSpansWithoutAttributes' => TRUE,
                'Cache.SerializerPath'                    => sys_get_temp_dir(),
                'Core.RemoveProcessingInstructions'       => TRUE,
                'HTML.Doctype'                            => 'HTML 4.01 Transitional',
                'URI.AllowedSchemes'                      => ['http', 'https', 'mailto', 'tel'],
                'URI.DefaultScheme'                       => 'https',
                'URI.DisableExternalResources'            => TRUE,
            ]
        );
    }
}
