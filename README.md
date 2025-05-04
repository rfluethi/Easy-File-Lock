# Easy File Lock

A system for managing and delivering protected files in WordPress.

## Features

- Stores files outside the WebRoot
- Role-based access control
- Efficient streaming of large files
- Protection against unauthorized access

## Installation

### Quick Start

1. Download the latest release files:
   - [protected.zip](https://github.com/rfluethi/Easy-File-Lock/releases/latest/download/protected.zip)
   - [secure-files.zip](https://github.com/rfluethi/Easy-File-Lock/releases/latest/download/secure-files.zip)

2. Extract the files:
   - Copy `protected/` into `public_html/` so it resides inside the WebRoot.
   - Place `secure-files/` outside `public_html/` to keep it outside the WebRoot.

3. Configure WordPress:
   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

### Detailed Instructions

See the [Installation Guide](docs/installation.md) for full setup details.

## Documentation

- [Technical Documentation](docs/technical.md) – In-depth technical information
- [Configuration](docs/configuration.md) – Configuration options
- [Troubleshooting](docs/troubleshooting.md) – Solutions to common problems
- [Security](docs/security.md) – Security notes and best practices
- [Changelog](docs/changelog.md) – Version history

## Releases

### Current Version
- Version: [v1.0.0](https://github.com/rfluethi/Easy-File-Lock/releases/latest)
- Downloads:
  - [protected.zip](https://github.com/rfluethi/Easy-File-Lock/releases/latest/download/protected.zip)
  - [secure-files.zip](https://github.com/rfluethi/Easy-File-Lock/releases/latest/download/secure-files.zip)

### Older Versions
Find all releases in the [Release Overview](https://github.com/rfluethi/Easy-File-Lock/releases).

## Known Issues

- Large files (>100MB) may cause timeouts on some hosting providers
- Some hosting environments do not support access outside the WebRoot
- Certain PHP configurations may limit the maximum file size

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push the branch (`git push origin feature/AmazingFeature`)
5. Create a pull request

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.