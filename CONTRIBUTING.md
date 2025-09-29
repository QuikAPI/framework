# Contributing to QuikAPI

Thank you for your interest in contributing!

## Code of Conduct

Be respectful and collaborative. Harassment or disrespectful behavior is not tolerated.

## Getting Started

- Fork the repo and create your feature branch from `master`.
- Ensure PHP >= 8.1 and Composer are installed.
- Install dependencies:
  ```bash
  composer install
  ```
- Run the dev server for local testing:
  ```bash
  php -S 127.0.0.1:8082 QuikAPI/server.php
  ```

## Development Guidelines

- **Style**: Follow PSR-12 for PHP code style.
- **Commits**: Use Conventional Commits (e.g., `feat: ...`, `fix: ...`, `docs: ...`).
- **Tests**: Add tests where possible. Keep coverage for core behavior.
- **Docs**: Update `README.md` or `docs/` when changing public behavior.

## Pull Request Process

- Open PRs against `master`.
- PR title should follow Conventional Commits.
- Describe changes clearly. Include screenshots or examples for user-facing changes.
- Keep PRs focused and reasonably small.

## Reviews and Approvals

- All PRs require at least **1 approval** from a maintainer before merge.
- Maintainers may request changes; please address feedback promptly.
- CI must be green before merging.

## Branch Protection (maintainers)

To enforce reviews and status checks, enable GitHub branch protection on `master`:
- Require a pull request before merging
- Require approvals: 1 (or 2, if preferred)
- Require status checks to pass (CI)
- Dismiss stale approvals on new commits (optional)
- Restrict who can push to matching branches (optional)

## Security Issues

Do not open public issues for security problems. Email the maintainers listed in `MAINTAINERS.md` with details.

## Questions

Open a Discussion or an Issue with the `question` label.
