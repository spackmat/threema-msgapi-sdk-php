# Changes

See README.md for info about version numbering. Follows https://keepachangelog.com/en/1.0.0/

## 2.2.2 - 2018-08-15
### Changed
- Split calculateMac out of connection class to remove duplication
- phpstan fixes

## 2.2.1 - 2018-08-15
### Changed
- Moved internal Constants class

## 2.2.0 - 2018-08-14
- Temporarily ignoring semver because no one else is using this library at the moment. Will add a note to the changelog when we follow semver again
### Changed
- Coding style fixes
- phpstan passes at level 7 (max)
- Stricter types on some classes. Gradually migrating to strict_types=1
- Removed the Public Key Store: it was horrible to use on the Connection class making it hard to use dependency injection. Storage is a separate concern. The API assumes you also store the threema id separately as well, so this is no extra burden on the caller (though it will hurt a bit for the command line)
- Migrated command line to Symfony console: to ease dependency injection; to make the commands more self documenting / easier to use; to get private keys and api secrets off the command line (may be insecure)
- Removed CryptTool/Encryptor singleton for dependency injection. Renamed CryptTool to Encryptor.
- Use sodium_hex2bin() and _bin2hex()
- Split cUrl stuff out of Connection class into HttpDriver and created an interface for dependency injection
- Close the curl connection after finished (for long running processes)
- Created ConnectionFactory to hold the runtime environment together
- Made helper methods on Connection class for end to end encrypted messages: now the user does not have to know about E2EHelper, they can just call methods on the Connection class
- Made all key and nonce parameters hex on the Connection class so users to not have to know / remember which is hex and which is binary. Everything outside is hex. The Encryptor class needs some binary things, but the parameters are now clearly labelled (we hope!)
- Moved some internal classes around to put related things together
- Simplified a lot of complex conditions that were checking for null even when null could never be returned

## 2.1.1 - 2018-08-07
### Added
- bulk lookup result carries through the original array key (user.id or person.id etc) for email and phone number

## 2.1.0 - 2018-08-06
### Added
- bulk lookup for phone numbers and emails

## 2.0.0 - 2018-08-06
### Added
- bin/threema-gateway to replace the .phar file
### Changed
- use composer autoloader
- set default curl tls options for high security (https on, TLS 1.2)
- moved unit tests to separate test directory
- pds/skeleton checks pass 
### Removed
- old PECL sodium drivers
- .phar binary and build tools
- shell scripts needed for sodium and phar build
- git submodules for Salt classes 
- old bootstrap and hard coded autoloader 