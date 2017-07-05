<?php

namespace Trustpilot\Test;

use Trustpilot\Api\Invitation\InvitationContext;

class ContextTest extends \Codeception\Test\Unit
{
    const DUMMY_BUSINESS_ID = 'business_id';
    const DUMMY_TEMPLATE_ID = 'template_id';
    const DUMMY_REDIRECT_URI = 'https://redirect.uri';
    const DUMMY_TAGS = ['tag1', 'tag2', 'tag3'];
    const DUMMY_LOCALE = 'de_DE';

    /** @var InvitationContext */
    private $underTest;

    protected function _before()
    {
    }

    public function testContextGiveBackCorrectValuesInclusiveExpectedDefaultValues()
    {
        $this->underTest = new InvitationContext(
            self::DUMMY_BUSINESS_ID,
            self::DUMMY_TEMPLATE_ID,
            self::DUMMY_REDIRECT_URI
        );

        $this->assertSame(self::DUMMY_BUSINESS_ID, $this->underTest->getBusinessUnitId());
        $this->assertSame(self::DUMMY_TEMPLATE_ID, $this->underTest->getTemplateId());
        $this->assertSame(self::DUMMY_REDIRECT_URI, $this->underTest->getRedirectUri());
        $this->assertEmpty($this->underTest->getTags());
        $this->assertSame('en-US', $this->underTest->getLocale());
    }

    public function testContextGiveBackCorrectValuesWithExtendedValues()
    {
        $this->underTest = new InvitationContext(
            self::DUMMY_BUSINESS_ID,
            self::DUMMY_TEMPLATE_ID,
            self::DUMMY_REDIRECT_URI,
            self::DUMMY_TAGS,
            self::DUMMY_LOCALE
        );

        $this->assertSame(self::DUMMY_BUSINESS_ID, $this->underTest->getBusinessUnitId());
        $this->assertSame(self::DUMMY_TEMPLATE_ID, $this->underTest->getTemplateId());
        $this->assertSame(self::DUMMY_REDIRECT_URI, $this->underTest->getRedirectUri());
        $this->assertSame(self::DUMMY_TAGS, $this->underTest->getTags());
        $this->assertSame(self::DUMMY_LOCALE, $this->underTest->getLocale());
    }
}
