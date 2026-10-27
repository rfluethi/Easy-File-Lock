# Easy File Lock

[![License: MIT](../../../typing-trainer/backend/node_modules/ms/license.md) [![GitHub release](https://img.shields.io/github/v/release/rfluethi/Easy-File-Lock)](https://github.com/rfluethi/Easy-File-Lock/releases/latest) [![CI](https://github.com/rfluethi/Easy-File-Lock/actions/workflows/ci.yml/badge.svg)](https://github.com/rfluethi/Easy-File-Lock/actions/workflows/ci.yml)

A secure system for file access control on WordPress websites.

## Features

- WordPress-based authentication (only logged-in users can access protected files)
- Role-based access control via `$role_folders` in `secure-config.php`
- User-based access control via `$user_folders` — map individual users by WordPress user ID to a specific folder
- Files stored outside the WebRoot, served only through the PHP gatekeeper
- MIME-type whitelist with hard rejection of non-whitelisted extensions
- Per-extension Content-Security-Policy for safe inline rendering of HTML and SVG
- Persistent access log independent of `DEBUG_MODE`, with anonymised IP addresses (`efl_anonymize_ip()`)
- Default-deny `.htaccess` fallback when `mod_rewrite` is unavailable

## Installation

1. Download the latest version:
   - [protected.zip](https://github.com/rfluethi/Easy-File-Lock/releases/latest/download/protected.zip)
   - [secure-files.zip](https://github.com/rfluethi/Easy-File-Lock/releases/latest/download/secure-files.zip)

2. Extract `protected.zip` to your WordPress installation root directory
3. Extract `secure-files.zip` outside your WordPress installation root directory
4. Configure permissions as described in the [documentation](docs/configuration.md)

## Verify

1. Log in to WordPress as a `subscriber`.
2. Visit `https://your-domain/protected/group-1/example-1.pdf` → file should render.
3. Visit `https://your-domain/protected/group-2/example-2.pdf` → response should be `403 Forbidden`.
4. Check `secure-files/logs/access.log` and confirm both an `ALLOW 200` and a `DENY 403` line are present.

## Documentation

- [Technical Documentation](docs/technical.md)
- [Configuration Guide](docs/configuration.md)
- [Installation Guide](docs/installation.md)
- [Security Policy](docs/security.md)
- [Changelog](docs/changelog.md)
- [Troubleshooting](docs/troubleshooting.md)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on reporting issues and submitting pull requests. For security vulnerabilities, see [SECURITY.md](SECURITY.md) — do not file public issues.

## License

This project is licensed under the MIT License - see the [license](../../../typing-trainer/backend/node_modules/ms/license.md) file for details.
