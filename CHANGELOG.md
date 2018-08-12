# Changes

See README.md for info about version numbering. Follows https://keepachangelog.com/en/1.0.0/

## 2.2.0 - 2018-08-10
- Temporarily ignoring semver because no one else is using htis library at the moment. Will add a note to the changelog when we follow semver again
### Changed
- Coding style fixes
- Stricter types on some classes. Gradually migrating to strict_types=1
- Removed the Public Key Store: it was horrible to use on the Connection class making it hard to use dependency injection. Storage is a separate concern. The API assumes you also store the threema id separately as well, so this is no extra burden on the caller (though it will hurt a bit for the command line)
- Migrated command line to Symfony console: to ease dependency injection; to make the commands more self documenting / easier to use; to get private keys and api secrets off the command line (may be insecure)

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