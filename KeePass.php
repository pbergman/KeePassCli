#!/usr/bin/php
<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

require_once dirname(__FILE__).'/vendor/autoload.php';

$app = new \KeePassCli\Application();
$app->run();