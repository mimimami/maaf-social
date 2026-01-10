# MAAF Social Példák

## Alapvető Használat

### Social Manager Setup

```php
use MAAF\Social\SocialManager;
use MAAF\Social\Providers\GoogleProvider;
use MAAF\Social\Providers\FacebookProvider;
use MAAF\Social\Providers\GitHubProvider;

// Create social manager
$socialManager = new SocialManager();

// Register Google provider
$google = new GoogleProvider(
    clientId: 'google-client-id',
    clientSecret: 'google-client-secret',
    redirectUri: 'http://example.com/auth/google/callback'
);
$socialManager->registerProvider($google);

// Register Facebook provider
$facebook = new FacebookProvider(
    clientId: 'facebook-app-id',
    clientSecret: 'facebook-app-secret',
    redirectUri: 'http://example.com/auth/facebook/callback'
);
$socialManager->registerProvider($facebook);

// Register GitHub provider
$github = new GitHubProvider(
    clientId: 'github-client-id',
    clientSecret: 'github-client-secret',
    redirectUri: 'http://example.com/auth/github/callback'
);
$socialManager->registerProvider($github);
```

## OAuth Flow

### Authorization URL Generálás

```php
// Get authorization URL
$authUrl = $socialManager->getAuthorizationUrl(
    providerName: 'google',
    redirectUri: 'http://example.com/auth/google/callback',
    options: [
        'state' => csrf_token(), // CSRF protection
    ]
);

// Redirect user to authorization URL
header('Location: ' . $authUrl);
```

### Callback Kezelés

```php
// Handle OAuth callback
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

if ($code === null) {
    throw new \RuntimeException('Authorization code missing');
}

// Verify state (CSRF protection)
if ($state !== $_SESSION['oauth_state']) {
    throw new \RuntimeException('Invalid state');
}

// Get social user
$socialUser = $socialManager->handleCallback(
    providerName: 'google',
    code: $code,
    redirectUri: 'http://example.com/auth/google/callback'
);

// Use social user
echo "Email: " . $socialUser->getEmail() . "\n";
echo "Name: " . $socialUser->getName() . "\n";
echo "Provider: " . $socialUser->getProvider() . "\n";
```

## Social Login

### Login Létrehozása

```php
use MAAF\Social\SocialLogin;

$socialLogin = new SocialLogin($socialManager);

// Login with callbacks
$user = $socialLogin->login(
    providerName: 'google',
    code: $code,
    redirectUri: 'http://example.com/auth/google/callback',
    userFinder: function($identifier) {
        // Try to find by email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $identifier)->first();
        }
        // Try to find by ID
        return User::find($identifier);
    },
    userCreator: function($socialUser) {
        // Create new user
        return User::create([
            'email' => $socialUser->getEmail(),
            'name' => $socialUser->getName(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(), // Social emails are usually verified
        ]);
    }
);

if ($user !== null) {
    // Login successful
    auth()->login($user);
}
```

### Login Controller

```php
use MAAF\Core\Http\Request;
use MAAF\Core\Http\Response;
use MAAF\Social\SocialManager;
use MAAF\Social\SocialLogin;

class SocialAuthController
{
    public function __construct(
        private readonly SocialManager $socialManager,
        private readonly SocialLogin $socialLogin
    ) {
    }

    public function redirect(Request $request, string $provider): Response
    {
        $authUrl = $this->socialManager->getAuthorizationUrl(
            providerName: $provider,
            redirectUri: route('auth.callback', ['provider' => $provider]),
            options: [
                'state' => csrf_token(),
            ]
        );

        return Response::redirect($authUrl);
    }

    public function callback(Request $request, string $provider): Response
    {
        $code = $request->getQueryParam('code');
        $state = $request->getQueryParam('state');

        if ($code === null) {
            return Response::json(['error' => 'Authorization code missing'], 400);
        }

        // Verify state
        if ($state !== $request->getSession()->get('csrf_token')) {
            return Response::json(['error' => 'Invalid state'], 400);
        }

        // Login
        $user = $this->socialLogin->login(
            providerName: $provider,
            code: $code,
            redirectUri: route('auth.callback', ['provider' => $provider]),
            userFinder: fn($id) => User::findByEmail($id) ?? User::find($id),
            userCreator: fn($socialUser) => User::create([
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
            ])
        );

        if ($user === null) {
            return Response::json(['error' => 'Login failed'], 401);
        }

        // Login user
        auth()->login($user);

        return Response::redirect('/dashboard');
    }
}
```

## Account Linking

### Link Account

```php
// Link social account to existing user
$socialUser = $socialManager->handleCallback('google', $code, $redirectUri);
$socialManager->linkAccount($userId, $socialUser);

// Check if linked
if ($socialManager->isAccountLinked($userId, 'google')) {
    echo "Google account is linked\n";
}
```

### Unlink Account

```php
// Unlink social account
$socialManager->unlinkAccount($userId, 'google');

// Verify unlinked
if (!$socialManager->isAccountLinked($userId, 'google')) {
    echo "Google account unlinked\n";
}
```

### Get Linked Accounts

```php
// Get all linked accounts for user
$linkedAccounts = $socialManager->getLinkedAccounts($userId);

foreach ($linkedAccounts as $provider => $providerId) {
    echo "{$provider}: {$providerId}\n";
}
```

### Find User by Social Account

```php
// Find user by social account
$userId = $socialManager->findUserBySocialAccount('google', 'google-user-id');

if ($userId !== null) {
    $user = User::find($userId);
    // User found
}
```

## Teljes Példa

### Setup és Használat

```php
use MAAF\Social\SocialManager;
use MAAF\Social\Providers\GoogleProvider;
use MAAF\Social\SocialLogin;

// 1. Setup social manager
$socialManager = new SocialManager();

// 2. Register providers
$google = new GoogleProvider('client-id', 'client-secret', 'redirect-uri');
$socialManager->registerProvider($google);

// 3. Setup social login
$socialLogin = new SocialLogin($socialManager);

// 4. Get authorization URL
$authUrl = $socialManager->getAuthorizationUrl('google', 'redirect-uri');

// 5. Handle callback and login
$user = $socialLogin->login(
    providerName: 'google',
    code: $code,
    redirectUri: 'redirect-uri',
    userFinder: fn($id) => User::findByEmail($id),
    userCreator: fn($socialUser) => User::create([
        'email' => $socialUser->getEmail(),
        'name' => $socialUser->getName(),
    ])
);

// 6. Link account
if ($user !== null) {
    $socialUser = $socialManager->handleCallback('google', $code, 'redirect-uri');
    $socialManager->linkAccount($user->id, $socialUser);
}
```

## Provider Specifikus Használat

### Google

```php
$google = new GoogleProvider(
    clientId: 'your-google-client-id',
    clientSecret: 'your-google-client-secret',
    redirectUri: 'http://example.com/auth/google/callback'
);

$socialManager->registerProvider($google);

// Get authorization URL
$authUrl = $socialManager->getAuthorizationUrl('google', 'redirect-uri');
// Redirects to: https://accounts.google.com/o/oauth2/v2/auth?...
```

### Facebook

```php
$facebook = new FacebookProvider(
    clientId: 'your-facebook-app-id',
    clientSecret: 'your-facebook-app-secret',
    redirectUri: 'http://example.com/auth/facebook/callback'
);

$socialManager->registerProvider($facebook);

// Get authorization URL
$authUrl = $socialManager->getAuthorizationUrl('facebook', 'redirect-uri');
// Redirects to: https://www.facebook.com/v18.0/dialog/oauth?...
```

### GitHub

```php
$github = new GitHubProvider(
    clientId: 'your-github-client-id',
    clientSecret: 'your-github-client-secret',
    redirectUri: 'http://example.com/auth/github/callback'
);

$socialManager->registerProvider($github);

// Get authorization URL
$authUrl = $socialManager->getAuthorizationUrl('github', 'redirect-uri');
// Redirects to: https://github.com/login/oauth/authorize?...
```

## Middleware Integration

### Social Auth Middleware

```php
use MAAF\Core\Http\MiddlewareInterface;
use MAAF\Core\Http\Request;
use MAAF\Core\Http\Response;
use MAAF\Social\SocialManager;

class SocialAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SocialManager $socialManager
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $provider = $request->getAttribute('provider');
        
        if ($provider === null) {
            return $next($request);
        }

        // Check if user has linked account
        $user = $request->getAttribute('user');
        if ($user !== null && $this->socialManager->isAccountLinked($user->id, $provider)) {
            // Account is linked, allow access
            return $next($request);
        }

        // Redirect to link account
        $authUrl = $this->socialManager->getAuthorizationUrl($provider, route('auth.callback'));
        return Response::redirect($authUrl);
    }
}
```

## Best Practices

### Security

```php
// 1. Always use state parameter for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$authUrl = $socialManager->getAuthorizationUrl('google', 'redirect-uri', [
    'state' => $state,
]);

// Verify state in callback
if ($_GET['state'] !== $_SESSION['oauth_state']) {
    throw new \RuntimeException('Invalid state');
}
```

### User Creation

```php
// 1. Check if user exists before creating
$userFinder = function($identifier) {
    // Try email first
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        return User::where('email', $identifier)->first();
    }
    return User::find($identifier);
};

// 2. Create user with verified email
$userCreator = function($socialUser) {
    return User::create([
        'email' => $socialUser->getEmail(),
        'name' => $socialUser->getName(),
        'email_verified_at' => now(), // Social emails are verified
        'avatar' => $socialUser->getAvatar(),
    ]);
};
```

### Account Linking

```php
// 1. Link account after successful login
$socialUser = $socialManager->handleCallback('google', $code, $redirectUri);
$socialManager->linkAccount($user->id, $socialUser);

// 2. Allow multiple providers per user
$socialManager->linkAccount($user->id, $googleUser);
$socialManager->linkAccount($user->id, $facebookUser);
$socialManager->linkAccount($user->id, $githubUser);

// 3. Check linked accounts before showing link option
if (!$socialManager->isAccountLinked($user->id, 'google')) {
    // Show "Link Google" button
}
```

### Error Handling

```php
try {
    $socialUser = $socialManager->handleCallback('google', $code, $redirectUri);
} catch (\RuntimeException $e) {
    // Handle error
    error_log("OAuth error: " . $e->getMessage());
    return Response::json(['error' => 'Authentication failed'], 401);
}
```
