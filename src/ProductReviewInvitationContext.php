<?php

namespace Trustpilot\Api\Invitation;

class ProductReviewInvitationContext
{
    /** @var string */
    private $businessUnitId;

    /** @var string */
    private $redirectUri;

    /** @var string */
    private $locale;

    /**
     * @param string $businessUnitId
     * @param string $redirectUri
     * @param string $locale
     */
    public function __construct($businessUnitId, $redirectUri, $locale = 'en-US')
    {
        $this->businessUnitId = $businessUnitId;
        $this->redirectUri = $redirectUri;
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getBusinessUnitId()
    {
        return $this->businessUnitId;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
