# [Threema Gateway](https://gateway.threema.ch/) PHP SDK

This is an unofficial wrapper for the Threema Gateway API.

You have four other alternatives

* Use the official Threema github repo https://github.com/threema-ch/threema-msgapi-sdk-php. No longer maintained (Oct 2015)
* Download the .zip file version from https://gateway.threema.ch/. Currently v1.1.7 Oct 2016. Missing the Bulk Lookup api.
* Use an unofficial version which stays close to the official version, occasionally has patches accepted by Threema https://github.com/rugk/threema-msgapi-sdk-php. It has an [`official`](https://github.com/rugk/threema-msgapi-sdk-php/tree/official) branch which mirrors the official version. Rugk has done a ton of great work to move the package forward into the modern ecosystem while maintaining as much backwards compatibility as possible.
* Use https://github.com/chillerlan/php-threema which is a separate implementation (Jun 2017) and missing some functionality. The API is cleaner / more logical in some places than the above alternatives. 

Why build another one?

* PHP7.2 has **libsodium** compiled in. If we target 7.2 as the minimum version, a whole lot of complicated code from the official version is no longer needed. We can delete the older PECL sodium drivers and the driver selection code. The Salt git submodule is no longer needed.
* Composer means that we can delete the phar command line runner, delete the two autoloaders that `require` a lot of files and do static initialisation for every page load (even if Threema is not being used) 
* Fix some of the problems caused by the above, plus some broken type hints (for phpStorm), and split the (small number of) unit tests out to a separate `/tests` directory so they do not clutter an authoritative classmap
* Add the missing **bulk lookup** function (hard to add by subclassing one of the alternatives)
* Add support for additional undocumented message types eg Location.
* In general, try to adhere to modern **php package conventions** so that it is more comfortable to use this in other projects. 
* Fix some of the architectural issues so it is easier to test and allows for dependency injection.
* Make the api more consistent: eg it was hard to know when to pass a binary value or a hex encoded value.
* Simplify the api: remove or wrap helper classes to reduce complexity

In summary, what is new

* PHP7.2+ only (libsodium)
* Simple package following modern conventions
* Composer support: clean autoloader
* Dependencies injected
* Symfony Console commands for familiar use and easier integration within a bigger app
* Supports Bulk Lookup API call
* Supports receiving Location message type (not publicly documented)

Versioning

The official .zip is 1.1.7 as at time of writing (August 2018). Rugk version is 1.2.0 (August 2018). This repo is a clone of the 1.2.0 master. To reduce potential for confusion, this repo is named `threema-gateway` instead of `threema-msgapi-sdk`. It starts from 2.0.0 because of breaking changes. See [CHANGELOG.md](CHANGELOG.md) for details 

The contributors of this repository are not affiliated with Threema or the Threema GmbH.

## Installation

```
composer install lss/threema-gateway
```

If you want to check whether your server meets the requirements and everything is configured properly, run `vendor/bin/threema-gateway` without any parameters on the console. It should show a list of commands if it is working, or an error message if not. 

## SDK usage

* Follow the documentation below to create a new key pair. Save these carefully and keep them secret. Do not make them accessible via the web. 
* Go to https://gateway.threema.ch/ and create an account.
* You will choose your Threema ID, which starts with a * and is a total of 8 characters long eg *MYKEY12.
* Upload your public key.
* Your API Secret is shown by your account name: a 16 character alphanumeric string.

### Creating a connection

```php
$connectionFactory = new ConnectionFactory();
$connection = $connectionFactory->getConnection('*MYKEY12', 'MYAPISECRET');
```

There is only one encryption method and one HttpDriver (currently) available. If you want to change connection settings 
or provide alternate drivers or mock for testing, pass them in to the ConnectionFactory constructor

### Sending a text message to a Threema ID (Simple Mode)

The message is encrypted on the Threema Gateway server.

```php
$result = $connection->sendToThreemaID('TEST1234', "This is a Test Message");
if ($result->isSuccess()) {
    echo 'new id created '.$result->getMessageId();
}
else {
    echo 'error '.$result->getErrorMessage();
}
```

### Sending a text message to a Threema ID (E2E Mode)

The message is encrypted locally before sending to Threema Gateway. 

```php
$result = $connection->sendTextMessage($myPrivateKeyHex, "TEST1234", "thePublicKeyAsHex", "This is an end-to-end encrypted message");

if ($result->isSuccess()) {
    echo 'Message ID: '.$result->getMessageId() . "\n";
}
else {
    echo 'Error: '.$result->getErrorMessage() . "\n";
}
```

### Sending a file message to a Threema ID (E2E Mode)

```php
$result = $connection->sendFileMessage($myPrivateKeyHex, "TEST1234", "thePublicKeyAsHex", "/path/to/my/file.pdf");

if ($result->isSuccess()) {
    echo 'File Message ID: '.$result->getMessageId() . "\n";
}
else {
    echo 'Error: '.$result->getErrorMessage() . "\n";
}
```

### Technical notes

Much of the communication with the Threema Gateway server is in binary. But not all of it. Sometimes you get a hex version of the binary value back.
The PHP wrapper attempts to hide all this from you: pass all values to `$connection` as hex encoded binary strings (use `$encryptor->bin2hex()`).
All values coming back are hex encoded binary strings.

The `$encryptor` mostly requires and returns binary strings as parameters, but for normal use of the api you will not need the 
encryptor so will not need to worry about it. See the console commands for examples if unsure. Most parameters now have
phpDoc comments to tell you if they are binary strings or hex strings.

tl;dr: expect to see and use hex

## Console client usage

Run 
```
vendor/bin/threema-gateway
``` 
for a list of commands and their options. 

Store your api secret, public and private keys in a file called `default.key` in the current working directory. See
`default.key.sample` for a template. This will save you a lot of typing and keep your secrets off the command line.
Do _not_ check your default.key file into version control.

To generate a new key pair,
```
vendor/bin/threema-gateway key:create-pair
```
which will print the keys to the console. Copy and paste those to your `default.key` file. You also need this to register your ID on Threema Gateway.

A good smoke test to see if everything is working right is 
```
vendor/bin/threema-gateway credits
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

Also check with phpstan, which must be green (zero errors) at maximum level.
```
composer phpstan
```

## To Do

* ReceiveMessageResult assumes you want to store file attachments on the local filesystem. This may not be true eg if using Amazon infrastructure. Refactor to allow for FileAcceptors(?) which can be overloaded to use Flysystem, local file system, or a null object pattern that ignores the file
* Url class is probably not needed
* Wait for PSR-18 to be approved, then migrate HttpDriver to use it (then we can drop direct cUrl support ourselves and rely on third parties to do a better job)

## Other platforms (Java and Python)

All repositories on GitHub are no longer maintained by the Threema GmbH. However, the community has forked the repositories of all platforms and they are now maintained unofficially.

You can find the Java repository at [simmac/threema-msgapi-sdk-java](https://github.com/simmac/threema-msgapi-sdk-java)  
and the Python repository at [lgrahl/threema-msgapi-sdk-python](https://github.com/lgrahl/threema-msgapi-sdk-python).
