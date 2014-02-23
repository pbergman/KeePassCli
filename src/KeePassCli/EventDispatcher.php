<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;

/**
 * Class EventDispatcher
 *
 * will look in \Symfony\Component\Console\ConsoleEvents for the constant values and CamelCase value
 * and check if that class exists in KeePassCli\EventListeners and if it is a instance from
 * KeePassCli\EventListeners\EventListenerInterface. if the class matches those thing it will add the
 * class to the listeners with method listenerAction this method will get the ConsoleCommandEvent
 *
 * so for example:
 *
 * ConsoleEvents::COMMAND = 'console.command';
 *
 * so this will check for
 *
 * \KeePassCli\EventListeners\ConsoleCommand
 *
 * if so ConsoleCommand will be add to the listener and get ConsoleCommandEvent to the method  listenerAction
 *
 * @package KeePassCli
 */
class EventDispatcher extends BaseEventDispatcher
{

    const NAMESPACE_EVENT_LISTENER = '\KeePassCli\EventListeners';

    public function __construct(){

        $refConsoleEvents = new \ReflectionClass('\Symfony\Component\Console\ConsoleEvents');

        foreach ( $refConsoleEvents->getConstants() as $value ){

            $className = sprintf('%s\%s', self::NAMESPACE_EVENT_LISTENER, implode('', array_map('ucfirst', explode('.',$value))));

            if (class_exists($className)) {

                $refClass = new \ReflectionClass($className);

                if ($refClass->isSubclassOf(sprintf('%s\EventListenerInterface',self::NAMESPACE_EVENT_LISTENER))) {

                    $this->addListener($value, array(new $className, 'listenerAction'));

                }

            }
        }

    }

}