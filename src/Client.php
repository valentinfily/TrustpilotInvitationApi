<?php

namespace Trustpilot\Api\Invitation;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Trustpilot\Api\Authenticator\AccessToken;

class Client
{
    const ENDPOINT = 'https://invitations-api.trustpilot.com/v1/private/business-units/';

    /** @var AccessToken */
    private $accessToken;

    /** @var string */
    private $endpoint;

    /** @var GuzzleClientInterface */
    private $guzzle;

    /**
     * @param AccessToken $accessToken
     * @param string $endpoint
     * @param GuzzleClientInterface $guzzle
     */
    public function __construct(AccessToken $accessToken, $endpoint = null, GuzzleClientInterface $guzzle = null)
    {
        $this->accessToken = $accessToken;
        $this->guzzle = (null !== $guzzle) ? $guzzle : new GuzzleClient();
        $this->endpoint = $endpoint ?: self::ENDPOINT;
    }

    /**
     * @param InvitationContext $context
     * @param Recipient $recipient
     * @param Sender $sender
     * @param string $referenceId
     * @param \DateTimeInterface $time
     * @return array
     */
    public function invite(InvitationContext $context, Recipient $recipient, Sender $sender, $referenceId, \DateTimeInterface $time = null)
    {
        if (null === $time) {
            $time = new \DateTime();
        }

        $json = [
            'recipientEmail' => $recipient->getEmail(),
            'recipientName' => $recipient->getName(),
            'referenceId' => $referenceId,
            'templateId' => $context->getTemplateId(),
            'locale' => $context->getLocale(),
            'senderName' => $sender->getName(),
            'senderEmail' => $sender->getEmail(),
            'replyTo' => $sender->getReplyEmail(),
            'preferredSendTime' => $time->format('c'),
            'tags' => $context->getTags(),
            'redirectUri' => $context->getRedirectUri(),
        ];

        return $this->makeRequest($context->getBusinessUnitId() . '/invitations', $json);
    }

    /**
     * @param ProductReviewInvitationContext $context
     * @param Recipient $recipient
     * @param Product[] $products
     * @param $referenceId
     * @return array
     */
    public function productReviewInvitation(ProductReviewInvitationContext $context, Recipient $recipient, array $products, $referenceId)
    {
        $json = [
            'referenceId' => $referenceId,
            'locale' => $context->getLocale(),
            'products' => $products,
            //'productIds' => [],
            'consumer' => [
                'email' => $recipient->getEmail(),
                'name' => $recipient->getName()
            ],
            'redirectUri' => $context->getRedirectUri(),
        ];
        return $this->makeRequest($context->getBusinessUnitId() . '/invitation-links', $json);
    }

    /**
     * @param string $businessUnitId
     * @return array
     * @throws InvitationException
     */
    public function getInvitationTemplates($businessUnitId)
    {
        if (empty($businessUnitId)) {
            throw new InvitationException('Missing BusinessUnitId on calling getInvitationTemplates');
        }
        return $this->makeRequest($businessUnitId . '/templates');
    }

    /**
     * @param string $url
     * @param array $json
     * @return array
     */
    private function makeRequest($url, array $json = null)
    {
        $method = 'GET';
        $options = ['query' => ['token' => $this->accessToken->getToken()]];

        if (null !== $json) {
            $method = 'POST';
            $options['json'] = $json;
        }

        return $this->callEndpointAndGetBodyData($method, $url, $options);
    }

    private function callEndpointAndGetBodyData($method, $urlExtension, $options)
    {
        try {
            $response = $this->guzzle->request(
                $method,
                self::ENDPOINT . $urlExtension,
                $options
            );
        } catch (GuzzleException $e) {
            throw new InvitationException($e->getMessage(), $e->getCode(), $e);
        }

        return json_decode((string) $response->getBody(), true);
    }
}
