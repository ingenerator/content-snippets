<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\ContentSnippets;


use Doctrine\ORM\EntityManagerInterface;
use Ingenerator\ContentSnippets\ContentSnippetsDependencyFactory;
use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;

class ContentSnippetsDependencyFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function provider_service_names()
    {
        $container = new DependencyContainer(
            [
                '_include' => [
                    ContentSnippetsDependencyFactory::definitions(),
                    ContentSnippetsDependencyFactory::controllerDefinitions()
                ]
            ]
        );

        $services = [];
        foreach ($container->listServices() as $service_key) {
            $services[] = [$service_key];
        }
        return $services;
    }

    /**
     * @dataProvider provider_service_names
     */
    public function test_all_service_definitions_are_valid($service_name)
    {
        $container = new DependencyContainer(
            [
                '_include' => [
                    ContentSnippetsDependencyFactory::definitions(),
                    ContentSnippetsDependencyFactory::controllerDefinitions(),
                    $this->dummy_dependencies(
                        [
                            'doctrine.entity_manager' => EntityManagerInterface::class,
                        ]
                    ),
                ],
            ]
        );
        $container->get($service_name);
    }

    protected function dummy_dependencies(array $dependencies)
    {
        $definitions = [];
        foreach ($dependencies as $key => $class_or_interface) {
            $definitions[$key] = [
                '_settings' => [
                    'class' => $this->make_dummy_mock_class($class_or_interface),
                ],
            ];
        }
        return $definitions;
    }

    protected function make_dummy_mock_class($class_or_interface)
    {
        $dummy_name = 'DummyDependency_'.str_replace('\\', '_', $class_or_interface);
        if ( ! class_exists($dummy_name)) {
            $this->getMockBuilder($class_or_interface)
                ->setMockClassName($dummy_name.'Raw')
                ->disableOriginalConstructor()
                ->getMock();
            // Ouch. This appears to be the simplest way to use phpunit's mocking but splat the constructor
            // Which is required when the class we're mocking has either a private constructor or a set
            // of required arguments
            eval('class '.$dummy_name.' extends '.$dummy_name.'Raw { public function __construct() {} }');
        }
        return $dummy_name;
    }


}
