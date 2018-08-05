# Changes

See README.md for info about version numbering. Follows https://keepachangelog.com/en/1.0.0/

## 1.0.0 - 2018-08-06
### Added
- bin/threema-gateway
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