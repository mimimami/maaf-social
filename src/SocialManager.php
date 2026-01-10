<?php

declare(strict_types=1);

namespace MAAF\Social;

/**
 * Social Manager
 * 
 * Social login és account linking kezelő.
 * 
 * @version 1.0.0
 */
final class SocialManager
{
    /**
     * @var array<string, OAuthProviderInterface>
     */
    private array $providers = [];

    /**
     * @var array<string, array<string, string>>
     */
    private array $linkedAccounts = [];

    /**
     * Register provider
     * 
     * @param OAuthProviderInterface $provider Provider instance
     * @return void
     */
    public function registerProvider(OAuthProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * Get provider
     * 
     * @param string $name Provider name
     * @return OAuthProviderInterface|null
     */
    public function getProvider(string $name): ?OAuthProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Get authorization URL
     * 
     * @param string $providerName Provider name
     * @param string $redirectUri Redirect URI
     * @param array<string, mixed> $options Options
     * @return string
     */
    public function getAuthorizationUrl(string $providerName, string $redirectUri, array $options = []): string
    {
        $provider = $this->getProvider($providerName);
        if ($provider === null) {
            throw new \RuntimeException("Provider not found: {$providerName}");
        }

        return $provider->getAuthorizationUrl($redirectUri, $options);
    }

    /**
     * Handle OAuth callback
     * 
     * @param string $providerName Provider name
     * @param string $code Authorization code
     * @param string $redirectUri Redirect URI
     * @return SocialUser
     */
    public function handleCallback(string $providerName, string $code, string $redirectUri): SocialUser
    {
        $provider = $this->getProvider($providerName);
        if ($provider === null) {
            throw new \RuntimeException("Provider not found: {$providerName}");
        }

        return $provider->getUser($code, $redirectUri);
    }

    /**
     * Link social account to user
     * 
     * @param string $userId User ID
     * @param SocialUser $socialUser Social user
     * @return void
     */
    public function linkAccount(string $userId, SocialUser $socialUser): void
    {
        if (!isset($this->linkedAccounts[$userId])) {
            $this->linkedAccounts[$userId] = [];
        }

        $this->linkedAccounts[$userId][$socialUser->getProvider()] = $socialUser->getProviderId();
    }

    /**
     * Unlink social account from user
     * 
     * @param string $userId User ID
     * @param string $providerName Provider name
     * @return void
     */
    public function unlinkAccount(string $userId, string $providerName): void
    {
        if (isset($this->linkedAccounts[$userId][$providerName])) {
            unset($this->linkedAccounts[$userId][$providerName]);
        }
    }

    /**
     * Get linked accounts for user
     * 
     * @param string $userId User ID
     * @return array<string, string>
     */
    public function getLinkedAccounts(string $userId): array
    {
        return $this->linkedAccounts[$userId] ?? [];
    }

    /**
     * Check if account is linked
     * 
     * @param string $userId User ID
     * @param string $providerName Provider name
     * @return bool
     */
    public function isAccountLinked(string $userId, string $providerName): bool
    {
        return isset($this->linkedAccounts[$userId][$providerName]);
    }

    /**
     * Find user by social account
     * 
     * @param string $providerName Provider name
     * @param string $providerId Provider user ID
     * @return string|null User ID
     */
    public function findUserBySocialAccount(string $providerName, string $providerId): ?string
    {
        foreach ($this->linkedAccounts as $userId => $accounts) {
            if (isset($accounts[$providerName]) && $accounts[$providerName] === $providerId) {
                return $userId;
            }
        }

        return null;
    }
}
