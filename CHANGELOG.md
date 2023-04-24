# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.2] - 2023-04-24

### Fixed
- State that should be used to filter orders on UpdatePaymentStatus cron job
### Changed
- Use order_increment_id as your number when issue a new boleto
- Improve admin ui user experience by displaying the exact extension of credentials files
- Improve the way to display payment identifier on admin panel
### Removed
- 'Your number' config text input on admin panel
