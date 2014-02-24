<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli;

use \KeePass\Entity\BaseEntity;
use \KeePass\EntityController\Controller as EntityController;
use \SharedMemory\Controller as SHMController;
use \KeePass\EntityController\Filters\Filter as ecFilter;

use \KeePass\Exceptions\EntityUnknownPropertyException;
use \KeePass\Exceptions\OptionNotAllowedException;

/**
 * Class EntityListBuilder
 *
 * This a helper to create index list for auto complete functions
 *
 * use method build to define which property or properties
 * (use a array of names as argument) needed in the index
 *
 * @package KeePassCli
 */
class EntityListBuilder
{
    /** @var array  */
    protected $index = array();
    /** @var array  */
    protected $availableTypes = array('group', 'entry');
    /** @var EntityController */
    protected $ec;
    /** @var  ecFilter  */
    protected $entities = array();
    /** @var  SHMController  */
    protected $shm;

    /**
     * @param EntityController $ec
     * @param SHMController    $shm
     * @param string           $type    type which the index has te build
     *
     * @throws OptionNotAllowedException
     */
    public function __construct(EntityController $ec, SHMController $shm, $type)
    {

        $this->ec  = $ec;
        $this->shm = $shm;

        if (!in_array($type, $this->availableTypes)) {

            throw new OptionNotAllowedException($type, $this->availableTypes);

        } else {

            $this->entities = $this->ec->getEntities($type);

        }

    }

    /**
     * will build index on given name(s)
     *
     * @param   string|array $name
     * @return  array
     * @throws \KeePass\Exceptions\EntityUnknownPropertyException
     */
    public function build($name)
    {
        $return = array();

        if( !empty($this->entities) ){

            if (!is_array($name)) {
                $name = array($name);
            }

            $entries = $this->entities->getResult();

            /** @var BaseEntity $entity */
            foreach ( $entries as $entity ) {

                foreach ( $name as $property ){

                    $methodName = sprintf('get%s', implode('', array_map('ucfirst', explode('_', $property))));

                    if (!method_exists($entity, $methodName)) {

                        throw new EntityUnknownPropertyException($methodName, $entity);

                    } else {
                        $value    = call_user_func(array($entity,$methodName));
                        $return[] = $value;
                        $this->index[$entity->getUuid()][] =  $value;

                    }

                }

            }

        }

        return $return;
    }

    /**
     * will return entity by given index value
     *
     * @param   string $indexName      name selected from array given from method build
     * @return  array
     * @throws \Exception
     */
    public function getResultByIndex($indexName){

        $return = false;

        array_walk($this->index, function($val, $key) use ($indexName, &$return){

            if (in_array($indexName, $val)) {
                $return[] =  $this->entities->where('uuid', $key)->getSingleResult();
            }

        });

        if ( $return === false ) {
            throw new \Exception(sprintf('Could not find entity by ref: %s', $indexName));
        } else {
            return $return;
        }

    }

}