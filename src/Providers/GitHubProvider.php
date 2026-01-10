<?php

declare(strict_types=1);

namespace MAAF\Social\Providers;

use MAAF\Social\OAuthProviderInterface;
use MAAF\Social\SocialUser;

/**
 * GitHub Provider
 * 
 * GitHub OAuth provider implementáció.
 * 
 * @version 1.0.0
 */
final class GitHubProvider implements OAuthProviderInterface
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
            'scope' => 'user:email',
        ];

        if (isset($options['state'])) {
            $params['state'] = $options['state'];
        }

        return 'https://github.com/login/oauth/authorize?' . http_build_query($params);
    }

    public function getUser(string $code, string $redirectUri): SocialUser
    {
        // Exchange code for token and get user info
        // Placeholder implementation
        return new SocialUser(
            provider: 'github',
            providerId: 'github_' . uniqid(),
            email: 'user@example.com',
            name: 'GitHub User',
            avatar: null,
            data: []
        );
    }

    public function getName(): string
    {
        return 'github';
    }
}
