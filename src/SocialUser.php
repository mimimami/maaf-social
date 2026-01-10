<?php

declare(strict_types=1);

namespace MAAF\Social;

/**
 * Social User
 * 
 * Social user osztály OAuth provider-ektől.
 * 
 * @version 1.0.0
 */
final class SocialUser
{
    public function __construct(
        private readonly string $provider,
        private readonly string $providerId,
        private readonly string $email,
        private readonly string $name = '',
        private readonly ?string $avatar = null,
        private readonly array $data = []
    ) {
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Convert to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'provider_id' => $this->providerId,
            'email' => $this->email,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'data' => $this->data,
        ];
    }
}
