<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

use \KeePassCli\EntityListBuilder;
use \Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\TableHelper;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;


class EntryInfo extends Command implements ApplicationInterface
{
    /** @var \KeePass\Application  */
    protected $application;

    /**
     * @param \KeePass\Application $application
     */
    public function setKeePassApplication(\KeePass\Application $application)
    {
        $this->application = $application;
    }

    protected function configure()
    {
        $this
            ->setName('entries:info')
            ->setDescription('Lists info about selected entry')
            ->addArgument(
                'server',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Will show given server'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $ac = new EntityListBuilder(
            $this->application->get('keepass')->getEntityController(),
            $this->application->get('shm'),
            'entry'
        );

        $index   = $ac->build(array('name','namespace'));
        /** @var DialogHelper  $dialog */
        $dialog  = $this->getHelperSet()->get('dialog');
        $name    = $dialog->ask($output, 'Server: ', null, $index);
        $results = $ac->getResultByIndex($name);
        /** @var TableHelper $table */
        $table   = $this->getHelperSet()->get('table');

        $table->setHeaders(array('Name','Value'));

        foreach ($results as $result) {

            $ref        = new \ReflectionClass($result);
            $properties = array_merge($ref->getParentClass()->getProperties(), $ref->getProperties());
            $rows       = array();

            foreach ( $properties as $property ){

                switch( $property->name ){
                    case 'data':
                        array_walk(call_user_func(array($result, $this->getMethodName($property->name))), function($val, $key) use (&$rows){
                            $rows[] = array($key, $val);
                        });

                        break;
                    case 'last_modified':
                    case 'created':
                        /** @var \DateTime $time */
                        $time   = call_user_func(array($result, $this->getMethodName($property->name)));
                        $rows[] = array($property->name, $time->format('Y-m-d H:i:s'));
                        break;
                    default:
                        $rows[] = array($property->name, (string) call_user_func(array($result, $this->getMethodName($property->name))));
                }

            }

            $table->setRows($rows);
            $table->render($output);

        }

    }

    protected function getMethodName($name)
    {
        return sprintf('get%s', implode('', array_map('ucfirst', explode('_', $name))));

    }

}