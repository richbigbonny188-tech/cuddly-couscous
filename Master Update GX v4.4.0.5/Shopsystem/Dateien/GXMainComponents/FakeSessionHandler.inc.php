<?php


namespace Gambio\GX;


use SessionHandlerInterface;

class FakeSessionHandler implements SessionHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function close()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy($session_id)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read($session_id)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function write($session_id, $session_data)
    {
        return true;
    }
}