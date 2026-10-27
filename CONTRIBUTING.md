# Contributing to Easy File Lock

Thanks for your interest. This project is intentionally small and focused — please keep changes minimal and well-justified.

## Reporting Issues

Use GitHub Issues for bugs and feature ideas. Please include the PHP version, WordPress version, web server (Apache / Nginx / LiteSpeed), and the relevant excerpts from `secure-files/logs/access.log`.

For security issues, see [SECURITY.md](SECURITY.md) — do not file public issues.

## Pull Requests

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/short-description`.
3. Keep commits focused and write meaningful messages (`feat:`, `fix:`, `docs:`, `chore:`).
4. Update `docs/changelog.md` under an "Unreleased" section.
5. Open a PR against `main`, fill out the template, and link related issues.

## Coding Style

- PHP code follows PSR-12 conventions where reasonable.
- Keep dependencies at zero — this project is intentionally framework-free.
- Comments and identifiers are in English.

## Local Testing

1. Set up a local WordPress instance.
2. Place `protected/` inside the WebRoot and `secure-files/` outside it.
3. Add `define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');` to `wp-config.php`.
4. Verify access for an `administrator`, a `subscriber`, and an unauthenticated visitor.
