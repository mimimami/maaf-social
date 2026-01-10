# MAAF Social Dokumentáció

## Áttekintés

MAAF Social egy OAuth provider modulokkal rendelkező rendszer social loginnal és social account linkinggel.

## Funkciók

- ✅ **OAuth Providerek** - Google, Facebook, GitHub, stb.
- ✅ **Social Login** - Social login kezelés
- ✅ **Account Linking** - Social account linking
- ✅ **Provider Manager** - Provider kezelő
- ✅ **CLI Támogatás** - Social kezelés CLI parancsokkal

## Telepítés

```bash
composer require maaf/social
```

## Használat

### Alapvető Használat

```php
use MAAF\Social\SocialManager;
use MAAF\Social\Providers\GoogleProvider;
use MAAF\Social\SocialLogin;

// Create social manager
$socialManager = new SocialManager();

// Register providers
$google = new GoogleProvider('client-id', 'client-secret', 'redirect-uri');
$socialManager->registerProvider($google);

// Get authorization URL
$authUrl = $socialManager->getAuthorizationUrl('google', 'http://example.com/callback');

// Handle callback
$socialUser = $socialManager->handleCallback('google', $code, 'http://example.com/callback');
```

### Social Login

```php
use MAAF\Social\SocialLogin;

$socialLogin = new SocialLogin($socialManager);

// Login with user finder and creator
$user = $socialLogin->login(
    providerName: 'google',
    code: $code,
    redirectUri: 'http://example.com/callback',
    userFinder: function($identifier) {
        // Find user by ID or email
        return User::findByEmail($identifier);
    },
    userCreator: function($socialUser) {
        // Create new user from social user
        return User::create([
            'email' => $socialUser->getEmail(),
            'name' => $socialUser->getName(),
        ]);
    }
);
```

### Account Linking

```php
// Link account
$socialManager->linkAccount($userId, $socialUser);

// Unlink account
$socialManager->unlinkAccount($userId, 'google');

// Get linked accounts
$linkedAccounts = $socialManager->getLinkedAccounts($userId);

// Check if linked
if ($socialManager->isAccountLinked($userId, 'google')) {
    // Account is linked
}
```

## CLI Parancsok

```bash
# List providers
php maaf social:list
```

## További információk

- [API Dokumentáció](api.md)
- [Példák](examples.md)
- [Best Practices](best-practices.md)
