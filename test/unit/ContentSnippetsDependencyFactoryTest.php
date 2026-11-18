<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\ContentSnippets;


use Doctrine\ORM\EntityManagerInterface;
use Ingenerator\ContentSnippets\ContentSnippetsDependencyFactory;
use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContentSnippetsDependencyFactoryTest extends TestCase
{

    public static function provider_service_names()
    {
        $container = new DependencyContainer(
            [
                '_include' => [
                    ContentSnippetsDependencyFactory::definitions(),
                    ContentSnippetsDependencyFactory::controllerDefinitions(),
                ],
            ]
        );

        $services = [];
        foreach ($container->listServices() as $service_key) {
            $services[] = [$service_key];
        }

        return $services;
    }

    #[DataProvider('provider_service_names')]
    public function test_all_service_definitions_are_valid($service_name)
    {
        $container = new DependencyContainer(
            [
                '_include' => [
                    ContentSnippetsDependencyFactory::definitions(),
                    ContentSnippetsDependencyFactory::controllerDefinitions(),
                    [
                        'doctrine.entity_manager' => [
                            '_settings' => [
                                'class'=> $this->createStub(EntityManagerInterface::class)::class
                            ]
                        ],
                    ],
                ],
            ]
        );
        $this->assertIsObject($container->get($service_name));
    }

}
