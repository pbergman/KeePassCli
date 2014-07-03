<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Helper;

use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\OutputInterface;


class EntityTableHelper extends TableHelper
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'entity_table';
    }

    public function addEntity($entity, $minimize = false)
    {
        $entityRef  = new \ReflectionClass($entity);
        $entityType = strtolower($entityRef->getShortName());
        $rows       = array();
        $properties = array_merge($entityRef->getParentClass()->getProperties(), $entityRef->getProperties());

        switch ( $entityType ) {
            case 'entry':
                foreach ( $properties as $property ){

                    $propertyValue = call_user_func(array($entity, $this->getMethodName($property->name)));

                    switch( $property->name ){
                        case 'data':
                            array_walk($propertyValue, function($val, $key) use (&$rows){
                                $rows[] = array($key, $val);
                            });
                            break;
                        case 'last_modified':
                        case 'created':
                            if ( $minimize == false) {
                                /** @var \DateTime $propertyValue */
                                $rows[] = array($property->name, $propertyValue->format('Y-m-d H:i:s'));
                            }
                            break;
                        default:
                            if ( $minimize == false) {
                                $rows[] = array($property->name, $propertyValue);
                            }
                    }

                }
                break;
            case 'group':
                foreach ( $properties as $property ){

                    $propertyValue = call_user_func(array($entity, $this->getMethodName($property->name)));

                    switch( $property->name ){
                        case 'groups':
                        case 'entries':
                            if (!empty($propertyValue)) {
                                $rows[] = array($property->name, implode("\n", $propertyValue));
                            }
                            break;
                        case 'last_modified':
                        case 'created':
                            /** @var \DateTime $propertyValue */
                            $rows[] = array($property->name, $propertyValue->format('Y-m-d H:i:s'));
                            break;
                        default:
                            $rows[] = array($property->name, $propertyValue);
                    }

                }
                break;
            default:
                throw new \Exception(sprintf('Unknown entity type: %s', $entityType));
        }


        $this->setRows($rows);

    }

    /**
     * will return get method name of given name
     *
     * @param   string $name    name of property
     * @return  string
     */
    protected function getMethodName($name)
    {
        return sprintf('get%s', implode('', array_map('ucfirst', explode('_', $name))));
    }


}