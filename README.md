## General

This tool will generate a bootstrap based web page based on OpenVPN's status logfile.

It supports v1, v2 and v3 of the OpenVPN status file, and will automatically detect which version is being used.

## Getting started

- Clone the repo
- Run `composer install`
- Create a local config file `cp config/config.default.php config/config.local.php`
- Set the `log_file` variable in `config/config.local.php`

## About robinleblon/php-openvpn-status

Forked from [bajensen/php-openvpn-status](https://github.com/bajensen/php-openvpn-status)