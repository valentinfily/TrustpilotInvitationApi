<?php

namespace Trustpilot\Api\Invitation;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Trustpilot\Api\Authenticator\AccessToken;

class Client
{
    const INVITATIONS_API_ENDPOINT = 'https://invitations-api.trustpilot.com/v1/';
    const API_ENDPOINT = 'https://api.trustpilot.com/v1/';

    /** @var AccessToken */
    private $accessToken;

    /** @var \Logger */
    private $logger;

    /** @var string */
    private $endpointApi;

    /** @var string */
    private $endpointInvitationApi;

    /** @var GuzzleClientInterface */
    private $guzzle;

    /**
     * @param AccessToken $accessToken
     * @param \Logger $logger
     * @param string $apiInvitationEndpoint
     * @param string $invitationApiEndpoint
     * @param GuzzleClientInterface $guzzle
     */
    public function __construct(
        AccessToken $accessToken,
        \Logger $logger = null,
        $apiInvitationEndpoint = null,
        $invitationApiEndpoint = null,
        GuzzleClientInterface $guzzle = null
    ) {
        $this->accessToken = $accessToken;
        $this->logger = $logger;
        $this->guzzle = (null !== $guzzle) ? $guzzle : new GuzzleClient();
        $this->endpointApi = $apiInvitationEndpoint ?: self::API_ENDPOINT;
        $this->endpointInvitationApi = $invitationApiEndpoint ?: self::INVITATIONS_API_ENDPOINT;
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

        $url = 'private/business-units/' . $context->getBusinessUnitId() . '/invitations';

        return $this->makeRequest($url, $json, [], $this->endpointInvitationApi);
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
            'consumer' => [
                'email' => $recipient->getEmail(),
                'name' => $recipient->getName()
            ],
            'redirectUri' => $context->getRedirectUri(),
        ];

        $json[empty($productsIds) ? 'products' : 'productIds'] = empty($productsIds) ? $products : $productsIds;

        $url = 'private/product-reviews/business-units/' . $context->getBusinessUnitId() . '/invitation-links';

        return $this->makeRequest($url, $json, [], $this->endpointApi);
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

        $url = 'product-reviews/business-units/' . $businessUnitId . '/reviews';

        return $this->makeRequest($url, [], $query, $this->endpointApi);
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
        $url = 'private/business-units/' . $businessUnitId . '/templates';

        return $this->makeRequest($url, [], [], $this->endpointInvitationApi);
    }

    /**
     * @param string $url
     * @param array $json
     * @param array $queryOptions
     * @param string $endpoint
     * @return array
     */
    private function makeRequest($url, array $json, array $queryOptions, $endpoint)
    {
        $method = 'GET';
        $options = ['query' => array_merge(['token' => $this->accessToken->getToken()], $queryOptions)];

        if (!empty($json)) {
            $method = 'POST';
            $options['json'] = $json;
        }

        return $this->callEndpointAndGetBodyData($method, $endpoint . $url, $options);
    }

    private function callEndpointAndGetBodyData($method, $url, $options)
    {
        try {
            $response = $this->guzzle->request(
                $method,
                $url,
                $options
            );
        } catch (GuzzleException $e) {
            $this->log('Error calling callEndpointAndGetBodyData: ' . $e->getMessage());
            $this->log('Passed options: ' . var_export($options, true));
            $this->log('Method: ' . $method);
            $this->log('Endpoint: ' . $url);

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
