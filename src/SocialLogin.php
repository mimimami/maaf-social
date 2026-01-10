<?php

declare(strict_types=1);

namespace MAAF\Social;

/**
 * Social Login
 * 
 * Social login kezelés.
 * 
 * @version 1.0.0
 */
final class SocialLogin
{
    public function __construct(
        private readonly SocialManager $socialManager
    ) {
    }

    /**
     * Login with social provider
     * 
     * @param string $providerName Provider name
     * @param string $code Authorization code
     * @param string $redirectUri Redirect URI
     * @param callable|null $userFinder User finder callback
     * @param callable|null $userCreator User creator callback
     * @return mixed User instance or null
     */
    public function login(
        string $providerName,
        string $code,
        string $redirectUri,
        ?callable $userFinder = null,
        ?callable $userCreator = null
    ): mixed {
        // Get social user from provider
        $socialUser = $this->socialManager->handleCallback($providerName, $code, $redirectUri);

        // Try to find existing user by social account
        $userId = $this->socialManager->findUserBySocialAccount($providerName, $socialUser->getProviderId());

        if ($userId !== null && $userFinder !== null) {
            return $userFinder($userId);
        }

        // Try to find user by email
        if ($userFinder !== null) {
            $user = $userFinder($socialUser->getEmail());
            if ($user !== null) {
                // Link account
                $this->socialManager->linkAccount($this->getUserId($user), $socialUser);
                return $user;
            }
        }

        // Create new user if creator provided
        if ($userCreator !== null) {
            $user = $userCreator($socialUser);
            if ($user !== null) {
                $this->socialManager->linkAccount($this->getUserId($user), $socialUser);
                return $user;
            }
        }

        return null;
    }

    /**
     * Get user ID from user instance
     * 
     * @param mixed $user User instance
     * @return string
     */
    private function getUserId(mixed $user): string
    {
        if (is_object($user)) {
            if (method_exists($user, 'getId')) {
                return (string)$user->getId();
            }
            if (isset($user->id)) {
                return (string)$user->id;
            }
        }

        if (is_array($user) && isset($user['id'])) {
            return (string)$user['id'];
        }

        return (string)$user;
    }
}
