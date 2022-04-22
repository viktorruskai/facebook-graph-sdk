# Facebook Graph SDK

[![PHP](https://img.shields.io/packagist/php-v/viktorruskai/facebook-graph-sdk?label=PHP)](https://github.com/viktorruskai/facebook-graph-sdk)
[![PHPUnit](https://github.com/viktorruskai/facebook-graph-sdk/actions/workflows/phpunit.yml/badge.svg)](https://github.com/viktorruskai/facebook-graph-sdk/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/viktorruskai/facebook-graph-sdk/actions/workflows/phpstan.yml/badge.svg)](https://github.com/viktorruskai/facebook-graph-sdk/actions/workflows/phpstan.yml)
[![Latest Stable Version](https://img.shields.io/github/v/release/viktorruskai/facebook-graph-sdk)](https://packagist.org/packages/viktorruskai/facebook-graph-sdk)

This repository is forked from [original package](https://github.com/facebookarchive/php-graph-sdk). It was refactored to handle PHP 8.0 and PHP 8.1.

## Installation

The Facebook PHP SDK can be installed with [Composer](https://getcomposer.org/). Run this command:

```bash
$ composer require viktorruskai/facebook-graph-sdk
```

## Usage

> **Note:** This version of the Facebook SDK for PHP requires PHP 5.4 or greater.

Simple GET example of a user's profile.

```php
$fb = new \Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  //'default_access_token' => '{access-token}', // optional
]);

// Use one of the helper classes to get a Facebook\Authentication\AccessToken entity.
//   $helper = $fb->getRedirectLoginHelper();
//   $helper = $fb->getJavaScriptHelper();
//   $helper = $fb->getCanvasHelper();
//   $helper = $fb->getPageTabHelper();

try {
  // Get the \Facebook\GraphNodes\GraphUser object for the current user.
  // If you provided a 'default_access_token', the '{access-token}' is optional.
  $response = $fb->get('/me', '{access-token}');
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$me = $response->getGraphUser();
echo 'Logged in as ' . $me->getName();
```

Complete documentation, installation instructions, and examples are available [here](docs/).

## Tests
The tests can be executed by running this command from the root directory:

> **Note:** You can create a test app on [Facebook Developers](https://developers.facebook.com), then create `tests/FacebookTestCredentials.php` from `tests/FacebookTestCredentials.php.dist` and edit it to add your credentials. 

```bash
$ composer tests
```

By default the tests will send live HTTP requests to the Graph API. If you are without an internet connection you can skip these tests by excluding the `integration` group.

```bash
$ composer tests-without-http-requests
```

## Contributing

For us to accept contributions you will have to first have signed the [Contributor License Agreement](https://developers.facebook.com/opensource/cla). Please see [CONTRIBUTING](https://github.com/facebook/php-graph-sdk/blob/master/CONTRIBUTING.md) for details.

## License

Please see the [license file](https://github.com/facebook/php-graph-sdk/blob/master/LICENSE) for more information.
