<?php

namespace Trustpilot\Api\Invitation;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Trustpilot\Api\Authenticator\AccessToken;

class Client
{
    const ENDPOINT = 'https://invitations-api.trustpilot.com/v1/';

    /** @var AccessToken */
    private $accessToken;

    /** @var \Logger */
    private $logger;

    /** @var string */
    private $endpoint;

    /** @var GuzzleClientInterface */
    private $guzzle;

    /**
     * @param AccessToken $accessToken
     * @param \Logger $logger
     * @param string $endpoint
     * @param GuzzleClientInterface $guzzle
     */
    public function __construct(AccessToken $accessToken, \Logger $logger = null, $endpoint = null, GuzzleClientInterface $guzzle = null)
    {
        $this->accessToken = $accessToken;
        $this->logger = $logger;
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

        return $this->makeRequest('private/business-units/' . $context->getBusinessUnitId() . '/invitations', $json);
    }

    /**
     * @param ProductReviewInvitationContext $context
     * @param Recipient $recipient
     * @param $referenceId
     * @param Product[] $productsIds
     * @param Product[] $products
     * @return array
     */
    public function productReviewInvitation(
        ProductReviewInvitationContext $context,
        Recipient $recipient,
        $referenceId,
        array $productsIds = [],
        array $products = []
    ) {
        $json = [
            'referenceId' => $referenceId,
            'locale' => $context->getLocale(),
            'name' => $recipient->getName(),
            'email' => $recipient->getEmail(),
            'redirectUri' => $context->getRedirectUri(),
        ];

        $json[empty($productsIds) ? 'products' : 'productIds'] = empty($productsIds) ? $products : $productsIds;

        return $this->makeRequest('private/product-reviews/' . $context->getBusinessUnitId() . '/invitation-links', $json);
    }

    /**
     * @param string $businessUnitId
     * @param string[] $sku
     * @param string $language
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getProductReviews(
        $businessUnitId,
        $sku,
        $language = 'de',
        $page = 1,
        $perPage = 100
    ) {
        $query = [
            'page' => $page,
            'perPage' => $perPage,
            'sku' => implode(',', $sku),
            'language' => $language,
        ];

        return $this->makeRequest('private/product-reviews/' . $businessUnitId . '/reviews', null, $query);
    }

    /**
     * @param string $businessUnitId
     * @return array
     * @throws InvitationException
     */
    public function getInvitationTemplates($businessUnitId)
    {
        if (empty($businessUnitId)) {
            $this->log('Missing BusinessUnitId on calling getInvitationTemplates');
            throw new InvitationException('Missing BusinessUnitId on calling getInvitationTemplates');
        }
        return $this->makeRequest('private/business-units/' . $businessUnitId . '/templates');
    }

    /**
     * @param string $url
     * @param array $json
     * @param array $queryOptions
     * @return array
     */
    private function makeRequest($url, array $json = null, array $queryOptions = [])
    {
        $method = 'GET';
        $options = ['query' => array_merge(['token' => $this->accessToken->getToken()], $queryOptions)];

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
                $this->endpoint . $urlExtension,
                $options
            );
        } catch (GuzzleException $e) {
            $this->log('Error calling callEndpointAndGetBodyData: ' . $e->getMessage());
            $this->log('Passed options: ' . var_export($options, true));
            $this->log('Method: ' . $method);
            $this->log('Endpoint: ' . self::ENDPOINT . $urlExtension);

            throw new InvitationException($e->getMessage(), $e->getCode(), $e);
        }

        return json_decode((string) $response->getBody(), true);
    }

    private function log($value, $logLevel = 'error')
    {
        if ($this->logger !== null) {
            switch ($logLevel) {
                case 'debug':
                    $this->logger->debug($value);
                    break;
                case 'error':
                    $this->logger->error($value);
                    break;
                default:
                    $this->logger->info($value);
            }
        }
    }
}
