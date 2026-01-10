<?php

declare(strict_types=1);

namespace MAAF\Social\Providers;

use MAAF\Social\OAuthProviderInterface;
use MAAF\Social\SocialUser;

/**
 * Google Provider
 * 
 * Google OAuth provider implementáció.
 * 
 * @version 1.0.0
 */
final class GoogleProvider implements OAuthProviderInterface
{
    private readonly string $clientId;
    private readonly string $clientSecret;
    private readonly string $redirectUri;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    public function getAuthorizationUrl(string $redirectUri, array $options = []): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
        ];

        if (isset($options['state'])) {
            $params['state'] = $options['state'];
        }

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function getUser(string $code, string $redirectUri): SocialUser
    {
        // Exchange code for token
        // $tokenResponse = $this->exchangeCodeForToken($code, $redirectUri);
        // $userInfo = $this->getUserInfo($tokenResponse['access_token']);

        // Placeholder implementation
        return new SocialUser(
            provider: 'google',
            providerId: 'google_' . uniqid(),
            email: 'user@example.com',
            name: 'Google User',
            avatar: null,
            data: []
        );
    }

    public function getName(): string
    {
        return 'google';
    }
}
