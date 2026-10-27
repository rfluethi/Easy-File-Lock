# Changelog

## [1.1.0] - 2026-04-28

### Added
- Multi-folder support for `$role_folders`: a role can now be mapped to an array of directories (`'editor' => ['group-1', 'group-3']`). Single-string syntax remains fully compatible.
- Per-extension Content-Security-Policy with `sandbox` directive for HTML and SVG.
- Persistent access log (independent of `DEBUG_MODE`) via `efl_log()`.
- Fail-closed `.htaccess` fallback when `mod_rewrite` is unavailable.
- Dotfile protection via `<FilesMatch "^\.">` in `.htaccess`.
- Hard rejection (HTTP 415) for file extensions not in the MIME whitelist.
- `Cross-Origin-Resource-Policy: same-origin` header on all file responses.
- `efl_to_bytes()` helper for correct PHP memory-limit comparison.
- `efl_anonymize_ip()` helper: IPv4 last octet → `xxx`, IPv6 last 64 bits zeroed — no full IP addresses stored in logs.
- `SECURITY.md`, `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`.
- Issue templates, PR template, Dependabot config, CI workflow.

### Changed
- `DEBUG_MODE` default changed from `true` to `false`.
- Path sanitization replaced with strict component whitelist (no leading dots).
- Administrator role check tightened to a literal string comparison.
- `efl_log()` falls back to `error_log()` on failed access-log writes.
- Log file creation uses `umask(0133)` to avoid a brief permission race.

### Documentation
- README features list rewritten to match actual capabilities.
- `security.md` `.htaccess` example updated to reflect current file.
- CSP Sandbox, SameSite, and rate-limiting guidance added to `security.md`.

## [1.0.1] - 2024-05-04

### Added
- Added English translation
- Added automatic version update in README.md

### Changed
- Improved documentation structure for better readability
- Minor code improvements

### Fixed
- No bug fixes in this version

## [1.0.0] - 2024-05-03

### Added
- Initial version
- WordPress integration
- Role-based access control
- File access protection
- Logging functionality