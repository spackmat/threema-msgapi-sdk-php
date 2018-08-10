# [Threema Gateway](https://gateway.threema.ch/) PHP SDK

This is an unofficial wrapper for the Threema Gateway API.

You have four other alternatives

* Use the official Threema github repo https://github.com/threema-ch/threema-msgapi-sdk-php. No longer maintained (Oct 2015)
* Download the .zip file version from https://gateway.threema.ch/. Currently v1.1.7 Oct 2016. Missing the Bulk Lookup api.
* Use an unofficial version which stays close to the official version, occasionally has patches accepted by Threema https://github.com/rugk/threema-msgapi-sdk-php. It has an [`official`](https://github.com/rugk/threema-msgapi-sdk-php/tree/official) branch which mirrors the official version. Rugk has done a ton of great work to move the package forward into the modern ecosystem while maintaining as much backwards compatibility as possible.
* Use https://github.com/chillerlan/php-threema which is a separate implementation (Jun 2017) and missing some functionality. The API is cleaner / more logical in some places  

Why build another one?

* PHP7.2 has libsodium compiled in. If we target 7.2 as the minimum version, a whole lot of complicated code from the official version is no longer needed. We can delete the older PECL sodium drivers and the driver selection code. The Salt git submodule is no longer needed.
* Composer means that we can delete the phar command line runner, delete the two autoloaders that `require` a lot of files and do static initialisation for every page load (even if Threema is not being used) 
* Fix some of the problems caused by the above, plus some broken type hints (for phpStorm), and split the (small number of) unit tests out to a separate `/tests` directory so they do not clutter an authoritative classmap
* Add the missing bulk lookup function (not implemented in PHP, hard to add by subclassing one of the alternatives because some important methods on the Connection class are private)
* In general, try to adhere to modern php package standards so that it is more comfortable to use this in other projects. 
* Fix some of the architectural issues so it is easier to test and allows for dependency injection. 
* Working towards a [botman](https://github.com/botman/botman) integration / plugin 

Versioning

The official .zip is 1.1.7 as at time of writing (August 2018). Rugk version is 1.2.0 (August 2018). This repo is a clone of the 1.2.0 master. To reduce potential for confusion, this repo is named `threema-gateway` instead of `threema-msgapi-sdk`. It starts from 2.0.0 because of breaking changes. See [CHANGELOG.md](CHANGELOG.md) for details 

The contributors of this repository are not affiliated with Threema or the Threema GmbH.

## Installation

```
composer install lss\threema-gateway
```

If you want to check whether your server meets the requirements and everything is configured properly, run `vendor/bin/threema-gateway` without any parameters on the console. It should show a list of commands if it is working, or an error message if not. 

## SDK usage

* Go to https://gateway.threema.ch/ and create an account.
* Follow the documentation there to create a new key pair. Save these carefully and keep them secret. Do not make them accessible via the web.
* You will choose your Threema ID, which starts with a * and is a total of 8 characters long eg *MYKEY12
* Your API Secret is shown by your account name: a 16 character alphanumeric string.

### Creating a connection

```php
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;
use Threema\MsgApi\Receiver;

require_once('lib/bootstrap.php');

//define your connection settings
$settings = new ConnectionSettings(
    '*MYKEY12',
    'MYAPISECRET'
);

$connection = new Connection($settings);
```

### Creating a connection with advanced options

**Attention:** These settings change internal values of the TLS connection. Choosing wrong settings can weaken the TLS connection or prevent a successful connection to the server. Use them with care!

Each of the additional options shown below is optional. You can leave it out or use `null` to use the default value determined by cURL for this option.

```php
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;
use Threema\MsgApi\Receiver;

//define your connection settings
$settings = new ConnectionSettings(
    '*MYKEY12',
    'MYAPISECRET',
    null, //the host to be used, set to null to use the default (recommend)
    [
        'forceHttps' => true, //set to true to force HTTPS, default: true
        'tlsVersion' => '1.2', //set the version of TLS to be used, default: '1.2'
        'tlsCipher' => 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384' //choose a cipher or a list of ciphers, default: null
        'pinnedKey' => MsgApi\Constants::DEFAULT_PINNED_KEY // the hashes to pin, it is NOT recommend to change this value!
    ]
);

$connection = new Connection($settings);
```

**Note:** For `pinnedKey` to work you must install cURL 7.39 or higher. You can test whether it works by specifying an invalid pin.

### Sending a text message to a Threema ID (Simple Mode)

```php
$receiver = new Receiver('ABCD1234', Receiver::TYPE_ID);

$result = $connection->sendSimple($receiver, "This is a Test Message");
if($result->isSuccess()) {
    echo 'new id created '.$result->getMessageId();
}
else {
    echo 'error '.$result->getErrorMessage();
}
```

### Sending a text message to a Threema ID (E2E Mode)

```php
$e2eHelper = new \Threema\MsgApi\Helpers\E2EHelper($senderPrivateKey,$connection);
$result = $e2eHelper->sendTextMessage("TEST1234", "thePublicKeyAsHex", "This is an end-to-end encrypted message");

if(true === $result->isSuccess()) {
    echo 'Message ID: '.$result->getMessageId() . "\n";
}
else {
    echo 'Error: '.$result->getErrorMessage() . "\n";
}
```

### Sending a file message to a Threema ID (E2E Mode)

```php
$senderPrivateKey = "MY_PUBLIC_KEY_IN_BIN";
$filePath = "/path/to/my/file.pdf";

$e2eHelper = new \Threema\MsgApi\Helpers\E2EHelper($senderPrivateKey,$connection);
$result = $e2eHelper->sendFileMessage("TEST1234", "thePublicKeyAsHex", $filePath);

if(true === $result->isSuccess()) {
    echo 'File Message ID: '.$result->getMessageId() . "\n";
}
else {
    echo 'Error: '.$result->getErrorMessage() . "\n";
}
```

## Console client usage

Run 
```
vendor\bin\threema-gateway
``` 
for a list of commands and their options

A good smoke test to see if everything is working right is 
```
vendor\bin\threema-gateway -C *MYKEY12 MYAPISECRET
```
which should show you the number of credits remaining in your account or an error message on failure.

## Contributing

Your pull requests are welcome here. Please follow the informal coding style that already exists (which tries to stay close to Threema's original). 
See the notes at the top of this readme for caveats: this is a fork of an unofficial fork of an unsupported api. The goals of this fork are quite different to the others, so maybe think about where your contribution will be most useful.

The original code did not come with a lot of tests. All new code should be covered by phpUnit tests. To run the tests,
```
composer test
``` 
or
```
vendor/bin/phpunit
```

## To Do

* Refactor the Connection to split out the cUrl calls to a separate driver class. Then add a Guzzle Driver. This will also make the Connection testable because it is trivial to create a Mock Driver. 
* ReceiveMessageResult assumes you want to store file attachments on the local filesystem. This may not be true eg if using Amazon infrastructure. Refactor to allow for FileAcceptors(?) which can be overloaded to use Flysystem, local file system, or a null object pattern that ignores the file
* There are some useful Exception classes defined but they are not used in some places.

## Other platforms (Java and Python)

All repositories on GitHub are no longer maintained by the Threema GmbH. However, the community has forked the repositories of all platforms and they are now maintained unofficially.

You can find the Java repository at [simmac/threema-msgapi-sdk-java](https://github.com/simmac/threema-msgapi-sdk-java)  
and the Python repository at [lgrahl/threema-msgapi-sdk-python](https://github.com/lgrahl/threema-msgapi-sdk-python).
