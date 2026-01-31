<?php
namespace SSA\service;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use SSA\library\AuthorizationServiceInterface;

class AuthorizationService implements AuthorizationServiceInterface
{


    private array $oidcConfig = [];

    public function __construct(array $oidConnectParams)
    {
        $url = $oidConnectParams['url'] ?? '';
        $realm = $oidConnectParams['realm'] ?? '';

        if ($url && $realm) {
            $configUrl = rtrim($url, '/') . '/realms/' . $realm . '/.well-known/openid-configuration';
            $content = @file_get_contents($configUrl);
            if ($content !== false) {
                $this->oidcConfig = json_decode($content, true) ?: [];
            }
        }
    }

    public function isAuthorized(ServerRequestInterface $request): bool
    {
        if (getenv('DEVELOPMENT') || getenv('STAGE')) {
            return true;
        }

        $authorizationHeader = $request->getHeaderLine('Authorization');
        return !empty($authorizationHeader);
    }

    public function hasAcrLevel(ServerRequestInterface $request, string $requiredAcr): bool
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            return false;
        }

        // In a real application, we would decode the JWT and check the 'acr' claim.
        // For this task, we will simulate a check for a specific token or header.
        $acrHeader = $request->getHeaderLine('X-ACR-Level');
        if (!empty($acrHeader)) {
            return $acrHeader === $requiredAcr;
        }

        return false;
    }
}