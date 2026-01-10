<?php

declare(strict_types=1);

namespace MAAF\Social\CLI\Commands;

use MAAF\Core\Cli\CommandInterface;
use MAAF\Social\SocialManager;

/**
 * Social List Command
 * 
 * Regisztrált OAuth provider-ek listázása.
 * 
 * @version 1.0.0
 */
final class SocialListCommand implements CommandInterface
{
    public function __construct(
        private readonly ?SocialManager $socialManager = null
    ) {
    }

    public function getName(): string
    {
        return 'social:list';
    }

    public function getDescription(): string
    {
        return 'List registered OAuth providers';
    }

    public function execute(array $args): int
    {
        if ($this->socialManager === null) {
            echo "❌ Social manager not available\n";
            return 1;
        }

        // Note: Would need reflection or provider registry to list providers
        echo "Registered OAuth Providers:\n";
        echo str_repeat("=", 80) . "\n";
        echo "Use social:link to link accounts\n";
        echo "\n";

        return 0;
    }
}
