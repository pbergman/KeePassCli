<?php

/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli;

use SharedMemory\IOController;

/**
 * Class SHMHelper
 *
 * helper class to save thing outside the indexer and will
 * not get cleared with destroy an removeCache methods.
 *
 * @package KeePassCli
 */
class SHMHelper extends IOController
{
    /** @var int  */
    private $token;

    /**
     * @param int $keyOffset
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($keyOffset = 0){

        if (!is_numeric($keyOffset)) {
            throw new \InvalidArgumentException('Offset needs to be a numeric value!');
        }

        $this->token = ftok(__FILE__, "K") + (int) $keyOffset;
    }

    /**
     * removes value from shared memory by token
     *
     * @return bool|void
     */
    public function remove()
    {
        parent::remove($this->token);
    }

    /**
     * will return token
     *
     * @return int
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * saves given value in shared memory
     *
     * @param string $string
     *
     * @return bool
     */
    public function save($string)
    {
        return parent::write($this->token, $string);
    }

    /**
     * get value from token in shared memory
     *
     * @return bool|string
     */
    public function get()
    {
        return parent::get($this->token);
    }
}