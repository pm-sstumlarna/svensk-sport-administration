<?php

namespace SSA\library;

use Psr\Http\Message\ServerRequestInterface;

interface AuthorizationServiceInterface
{
    public function isAuthorized(ServerRequestInterface $request): bool;

    /**
     * Checks if the request has the required Authentication Context Class Reference (ACR) level.
     *
     * @param ServerRequestInterface $request
     * @param string $requiredAcr
     * @return bool
     */
    public function hasAcrLevel(ServerRequestInterface $request, string $requiredAcr): bool;
}