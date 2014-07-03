<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

use PBergman\KeePass\Entity\Entry;
use \KeePassCli\EntityListBuilder;
use \KeePassCli\Helper\EntityTableHelper;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Helper\DialogHelper;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;


class ExpectSsh extends Command implements ApplicationInterface, ValidateInterface
{
    /** @var \PBergman\KeePass\Application  */
    protected $application;

    /**
     * @param \PBergman\KeePass\Application $application
     */
    public function setKeePassApplication(\PBergman\KeePass\Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return bool
     */
    public static function validate()
    {
        $expectVersion = shell_exec('dpkg-query -W --showformat=\'${Version}\' expect 2> /dev/null');

        return !empty($expectVersion);
    }

    protected function configure()
    {
        $this
            ->setName('expect:ssh')
            ->setDescription('Will initialize ssh');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var DialogHelper  $dialog */
        $dialog         = $this->getHelperSet()->get('dialog');
        /** @var EntityTableHelper $entityTable */
        $entityTable    = $this->getHelperSet()->get('entity_table');
        /** @var \PBergman\KeePass\EntityController\Controller $ec */
        $ec             = $this->application->get('keepass')->getEntityController();
        $ac             = new EntityListBuilder($ec, 'entry');
        $index          = $ac->build(array('name','namespace'));
        $name           = $dialog->ask($output, 'Server: ', null, $index);
        $results        = $ac->getResultByIndex($name);
        /** @var Entry $result */
        $result         = false;

        if (count($results) > 1){

            $servers = array();

            foreach ($results as $result) {
                /** @var Entry $entry */
                if ( false !== $entry = $ec->getEntities('entry')->where('uuid', $result)->getSingleResult()) {
                    $servers[$entry->getNamespace()] = $entry;
                }
            }

            $serverIndex = array_keys($servers);
            $selected    = $dialog->select($output, 'Got more than one result, select server: ' , $serverIndex, 0);
            $result      = $servers[$serverIndex[$selected]];

        } else {

            $result = $ec->getEntities('entry')->where('uuid', current($results))->getSingleResult();

        }

        if (!empty($result)) {
            $output->writeln("\n<info>Selected server: </info>\n");
            $entityTable->setHeaders(array('Name','Value'));
            $entityTable->addEntity($result);
            $entityTable->render($output);
            $output->writeln('');


            $data = $result->getData();

            if (empty($data['url'])) {
                throw new \Exception(sprintf("Entry %s hasn't got a url\n%s", $result->getName(), print_r($data, true)));
            } else {

                switch($output->getVerbosity()){
                    case OutputInterface::VERBOSITY_VERBOSE:
                        $param = '-v';
                        break;
                    case OutputInterface::VERBOSITY_VERY_VERBOSE:
                        $param = '-vv';
                        break;
                    case OutputInterface::VERBOSITY_DEBUG:
                        $param = '-vvv';
                        break;
                    default:
                        $param = null;
                }

                $url = $this->formatUrl($data['url']);

                passthru(
                    sprintf(
                        'expect -c \'
                             spawn ssh %s %s@%s;
                             expect {
                                 -re "(P|p)assword\:" { send "%s\n"; interact; }
                                 -ex "continue connecting (yes/no)?" { send "yes\n"; exp_continue;}
                                 -re "Last login" { interact; }
                         }\';',
                        $param,
                        $data['username'],
                        $url,
                        $data['password']
                    )
                );
            }

        }

    }

    /**
     * return formatted url
     *
     * @param $url
     * @param bool $all
     * @return mixed
     */
    protected function formatUrl($url, $all = false){

        $return = $url;

        if(preg_match('/^(?P<protocol>http|https):\/\/(?P<url>[^:]+):?(?P<port>\d+)?\/?$/',$url , $ret)){

            $return = ($all === false) ? $ret['url'] : $ret;

        }

        return $return;
    }
}