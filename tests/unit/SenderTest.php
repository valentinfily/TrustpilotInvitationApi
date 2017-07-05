<?php

namespace Trustpilot\Test;

use Trustpilot\Api\Invitation\Sender;

class SenderTest extends \Codeception\Test\Unit
{
    const DUMMY_USER_NAME = 'username';
    const DUMMY_EMAIL_ADDRESS = 'somewhere@foo.foo';
    const DUMMY_REPLY_TO_EMAIL_ADDRESS = 'reply@foo.foo';

    /** @var Sender */
    private $underTest;

    protected function _before()
    {
        $this->underTest = new Sender(
            self::DUMMY_EMAIL_ADDRESS,
            self::DUMMY_USER_NAME,
            self::DUMMY_REPLY_TO_EMAIL_ADDRESS
        );
    }

    public function testSenderGiveBackCorrectValues()
    {
        $this->assertSame(self::DUMMY_EMAIL_ADDRESS, $this->underTest->getEmail());
        $this->assertSame(self::DUMMY_REPLY_TO_EMAIL_ADDRESS, $this->underTest->getReplyEmail());
        $this->assertSame(self::DUMMY_USER_NAME, $this->underTest->getName());
    }
}