<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Repository;


use Doctrine\Common\Collections\Collection;

abstract class AbstractArrayRepository
{
    /**
     * @var array
     */
    protected $entities;

    /**
     * @var string
     */
    protected $save_log;

    protected function __construct(array $entities)
    {
        $this->entities = $entities;
    }

    /**
     * @param array|\Model_Base_Record $entity,...
     *
     * @return static
     */
    public static function with($entity)
    {
        return static::withList(func_get_args());
    }

    /**
     * @param array[] $entity_data
     *
     * @return static
     */
    public static function withList(array $entity_data)
    {
        $entity_class = static::getEntityBaseClass();
        $entities     = [];
        foreach ($entity_data as $entity) {
            if ( ! $entity instanceof $entity_class) {
                $entity = static::stubEntity($entity);
            }
            $entities[] = $entity;
        }

        return new static($entities);
    }

    /**
     * @return string
     */
    protected static function getEntityBaseClass()
    {
        throw new \BadMethodCallException('Implement your own '.__METHOD__.'!');
    }

    /**
     * @param array $data
     *
     * @return \Model_Base_Record
     */
    protected static function stubEntity(array $data)
    {
        throw new \BadMethodCallException('Implement your own '.__METHOD__.'!');
    }

    /**
     * @return static
     */
    public static function withNothing()
    {
        return new static([]);
    }

    protected function assertNothingSaved()
    {
        \PHPUnit_Framework_Assert::assertEquals(
            '',
            $this->save_log,
            'Expected no saved entities'
        );
    }

    protected function assertSavedOnly(\Model_Base_Record $entity)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $this->save_log,
            $this->formatSaveLog($entity),
            'Expected entity to be saved exactly once with matching data'
        );
    }

    protected function formatSaveLog(\Model_Base_Record $entity)
    {
        return sprintf(
            "%s (object %s) with data:\n%s\n",
            get_class($entity),
            spl_object_hash($entity),
            json_encode($this->entityToArray($entity), JSON_PRETTY_PRINT)
        );
    }

    protected function entityToArray(\Model_Base_Record $entity, & $seen_objects = [])
    {
        $entity_hash = spl_object_hash($entity);
        if (isset($seen_objects[$entity_hash])) {
            return '**RECURSION**';
        } else {
            $seen_objects[$entity_hash] = TRUE;
        }

        $all_props = \Closure::bind(
            function ($e) {
                return get_object_vars($e);
            },
            NULL,
            $entity
        );
        $obj_identity  = function ($a) {
            return get_class($a).'#'.spl_object_hash($a);
        };
        $result    = [];
        foreach ($all_props($entity) as $key => $var) {
            if ( ! is_object($var)) {
                $result[$key] = $var;
            } elseif ($var instanceof \Model_Base_Record) {
                $result[$key] = [
                    $obj_identity($var) => $this->entityToArray($var, $seen_objects)
                ];
            } elseif ($var instanceof Collection) {
                $result[$key] = [];
                foreach ($var as $collection_item) {
                    $result[$key][] = [
                        $obj_identity($var) => $this->entityToArray($collection_item, $seen_objects)
                    ];
                }
            } elseif ($var instanceof \DateTimeInterface) {
                $result[$key][get_class($var)] = $var->format(\DateTime::ISO8601);
            } else {
                $result[$key] = [$obj_identity($var) => '__object'];
            }
        }

        return $result;
    }

    /**
     * @param callable $callable
     *
     * @return int[]
     */
    protected function countWith($callable)
    {
        $counts = [];
        foreach ($this->entities as $entity) {
            $group = call_user_func($callable, $entity);
            $counts[$group] = \Arr::get($counts, $group, 0) +1;
        }
        return $counts;
    }

    /**
     * @param callable $callable
     *
     * @return \Model_Base_Record
     */
    protected function loadWith($callable)
    {
        if ( ! $entity = $this->findWith($callable)) {
            throw new \InvalidArgumentException('No entity matching criteria');
        }

        return $entity;
    }

    /**
     * @param $callable
     *
     * @return \Model_Base_Record
     */
    protected function findWith($callable)
    {
        $entities = $this->listWith($callable);
        if (count($entities) > 1) {
            throw new \UnexpectedValueException(
                'Found multiple entities : expected unique condition.'
            );
        }

        return array_pop($entities);
    }

    /**
     * @param callable $callable
     *
     * @return \Model_Base_Record[]
     */
    protected function listWith($callable)
    {
        $entities = [];
        foreach ($this->entities as $entity) {
            if (call_user_func($callable, $entity)) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    protected function saveEntity(\Model_Base_Record $entity)
    {
        $this->save_log .= $this->formatSaveLog($entity);
        if ( ! in_array($entity, $this->entities, TRUE)) {
            $this->entities[] = $entity;
        }
    }

}
