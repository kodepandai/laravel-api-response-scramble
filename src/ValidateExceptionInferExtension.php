<?php

namespace KodePandai\ApiResponseScramble;

use Dedoc\Scramble\Infer\Extensions\ExpressionExceptionExtension;
use Dedoc\Scramble\Infer\Scope\Scope;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Reference\StaticMethodCallReferenceType;
use KodePandai\ApiResponse\Facades\ApiResponse;
use KodePandai\ApiResponseScramble\Exceptions\ValidateException;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;

/**
 * Infer ApiResponse::validateOrFail then add 422 validation error to response.
 *
 * @see Dedoc\Scramble\Support\InferExtensions\PossibleExceptionInfer
 */
class ValidateExceptionInferExtension implements ExpressionExceptionExtension
{
    public function getException(Expr $node, Scope $scope): array
    {
        $scopeType = $scope->getType($node);

        if ($node instanceof Expr\StaticCall
            && $node->name instanceof Identifier
            && $node->name->name === 'validateOrFail'
            && $scopeType instanceof StaticMethodCallReferenceType
            && $scopeType->callee === ApiResponse::class) {
            return [
                new ObjectType(ValidateException::class),
            ];
        }

        return [];
    }
}
