<?php

declare(strict_types=1);

namespace MAAF\Social;

/**
 * OAuth Provider Interface
 * 
 * OAuth provider interface absztrakcióhoz.
 * 
 * @version 1.0.0
 */
interface OAuthProviderInterface
{
    /**
     * Get authorization URL
     * 
     * @param string $redirectUri Redirect URI
     * @param array<string, mixed> $options Options
     * @return string
     */
    public function getAuthorizationUrl(string $redirectUri, array $options = []): string;

    /**
     * Exchange authorization code for access token
     * 
     * @param string $code Authorization code
     * @param string $redirectUri Redirect URI
     * @return SocialUser
     */
    public function getUser(string $code, string $redirectUri): SocialUser;

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getName(): string;
}
