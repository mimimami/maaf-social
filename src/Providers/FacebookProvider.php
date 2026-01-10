<?php

declare(strict_types=1);

namespace MAAF\Social\Providers;

use MAAF\Social\OAuthProviderInterface;
use MAAF\Social\SocialUser;

/**
 * Facebook Provider
 * 
 * Facebook OAuth provider implementáció.
 * 
 * @version 1.0.0
 */
final class FacebookProvider implements OAuthProviderInterface
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
            'scope' => 'email',
        ];

        if (isset($options['state'])) {
            $params['state'] = $options['state'];
        }

        return 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
    }

    public function getUser(string $code, string $redirectUri): SocialUser
    {
        // Exchange code for token and get user info
        // Placeholder implementation
        return new SocialUser(
            provider: 'facebook',
            providerId: 'facebook_' . uniqid(),
            email: 'user@example.com',
            name: 'Facebook User',
            avatar: null,
            data: []
        );
    }

    public function getName(): string
    {
        return 'facebook';
    }
}
