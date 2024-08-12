<?php

namespace KodePandai\ApiResponseScramble;

use Dedoc\Scramble\Support\OperationExtensions\RequestBodyExtension;
use Illuminate\Routing\Route;

/**
 * Extract ApiResponse::validateOrFail() rules then add to request body.
 *
 * @see Dedoc\Scramble\Support\OperationExtensions\RequestBodyExtension
 */
class RequestBodyOperationExtension extends RequestBodyExtension
{
    /**
     * @param  mixed  $methodNode
     * @return array<int, mixed>
     */
    protected function extractRouteRequestValidationRules(Route $route, $methodNode)
    {
        $rules = [];
        $nodesResults = [];

        if (($validateCallExtractor = new ValidateCallExtractor($methodNode))->shouldHandle()) {
            if ($validateCallRules = $validateCallExtractor->extract()) {
                $rules = array_merge($rules, $validateCallRules);
                $nodesResults[] = $validateCallExtractor->node();
            }
        }

        return [$rules, array_filter($nodesResults)];
    }
}
