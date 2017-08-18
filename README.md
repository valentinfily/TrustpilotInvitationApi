# Trustpilot Invitation API Client

[![Latest Stable Version](https://poser.pugx.org/itspirit/trustpilot-invitation-api/v/stable)](https://packagist.org/packages/itspirit/trustpilot-invitation-api)
[![Total Downloads](https://poser.pugx.org/itspirit/trustpilot-invitation-api/downloads)](https://packagist.org/packages/itspirit/trustpilot-invitation-api)
[![License](https://poser.pugx.org/itspirit/trustpilot-invitation-api/license)](https://packagist.org/packages/itspirit/trustpilot-invitation-api)
[![composer.lock available](https://poser.pugx.org/phpunit/phpunit/composerlock)](https://packagist.org/packages/phpunit/phpunit)

A PHP library for accessing the [Trustpilot Invitation API](https://developers.trustpilot.com/invitation-api).

## Install

Install using [composer](https://getcomposer.org/):

```sh
composer install itspirit/trustpilot-invitation-api
```

## Usage

```php
use Trustpilot\Api\Authenticator\Authenticator;
use Trustpilot\Api\Invitation\Client;
use Trustpilot\Api\Invitation\Recipient;
use Trustpilot\Api\Invitation\Sender;
use Trustpilot\Api\Invitation\Context;

$authenticator = new Authenticator($apiKey, $apiToken, $username, $password);
$accessToken = $authenticator->getAccessToken();

$client = new Client($accessToken);

$context = new Context($businessUnitId, $templateId, $redirectUri);
// The last two arguments to the Context constructor ($tags and $locale) are optional
// $context = new Context($templateId, $redirectUri, $tags = array(), $locale = 'en-US');

$recipient = new Recipient($recipientEmail, $recipientName);
$sender    = new Sender($senderEmail, $senderName, $replyTo);

$client->invite($context, $recipient, $sender, $reference) /* : array */
```
