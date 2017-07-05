<?php

namespace Trustpilot\Test;

use Trustpilot\Api\Invitation\Recipient;

class RecipientTest extends \Codeception\Test\Unit
{
    const DUMMY_USER_NAME = 'username';
    const DUMMY_EMAIL_ADDRESS = 'somewhere@foo.foo';

    /** @var Recipient */
    private $underTest;

    protected function _before()
    {
        $this->underTest = new Recipient(
            self::DUMMY_EMAIL_ADDRESS,
            self::DUMMY_USER_NAME
        );
    }

    public function testRecipientGiveBackCorrectValues()
    {
        $this->assertSame(self::DUMMY_EMAIL_ADDRESS, $this->underTest->getEmail());
        $this->assertSame(self::DUMMY_USER_NAME, $this->underTest->getName());
    }
}