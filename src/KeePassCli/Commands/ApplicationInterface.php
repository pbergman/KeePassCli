<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

interface ApplicationInterface
{
    public function setKeePassApplication(\KeePass\Application $application);

}
