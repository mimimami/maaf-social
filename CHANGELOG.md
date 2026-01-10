# Changelog

## [1.0.0] - 2024-01-XX

### Added

- ✅ **OAuth Providers**
  - `OAuthProviderInterface` - OAuth provider interface
  - `GoogleProvider` - Google OAuth provider
  - `FacebookProvider` - Facebook OAuth provider
  - `GitHubProvider` - GitHub OAuth provider
  - `SocialManager` - Social kezelő több provider támogatással

- ✅ **Social User**
  - `SocialUser` - Social user osztály OAuth provider-ektől
  - Provider ID, email, name, avatar támogatás

- ✅ **Social Login**
  - `SocialLogin` - Social login kezelés
  - User finder és creator callback támogatás
  - Automatikus account linking

- ✅ **Account Linking**
  - Account linking kezelés
  - Link/unlink műveletek
  - Linked accounts query
  - User lookup by social account

- ✅ **CLI Commands**
  - `SocialListCommand` - Regisztrált OAuth provider-ek listázása

### Changed
- N/A (első kiadás)

### Fixed
- N/A (első kiadás)
