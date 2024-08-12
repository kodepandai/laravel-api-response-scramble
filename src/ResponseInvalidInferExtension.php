<?php

namespace KodePandai\ApiResponseScramble;

use Dedoc\Scramble\Infer\Extensions\ExpressionExceptionExtension;
use Dedoc\Scramble\Infer\Scope\Scope;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Reference\StaticMethodCallReferenceType;
use Dedoc\Scramble\Support\Type\Type;
use KodePandai\ApiResponse\Facades\ApiResponse;
use KodePandai\ApiResponseScramble\Exceptions\ResponseInvalidException;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar;

/**
 * Infer ApiResponse::invalid then add 422 validation error to response.
 *
 * @see Dedoc\Scramble\Support\InferExtensions\PossibleExceptionInfer
 */
class ResponseInvalidInferExtension implements ExpressionExceptionExtension
{
    /**
     * @return array<int, Type>
     */
    public function getException(Expr $node, Scope $scope): array
    {
        $scopeType = $scope->getType($node);

        if ($node instanceof Expr\StaticCall
            && $node->name instanceof Identifier
            && $node->name->name === 'invalid'
            && $scopeType instanceof StaticMethodCallReferenceType
            && $scopeType->callee === ApiResponse::class) {

            [$errKey, $errValues] = $node->getArgs();

            $errKey = $errKey->value instanceof Scalar\String_
                ? $errKey->value->value : null;

            if ($errValues->value instanceof Expr\Array_) {
                $errValues = $this->extractErrValuesArr($errValues->value);
            }

            if ($errValues instanceof Node\Arg
                && $errValues->value instanceof Scalar\String_) {
                $errValues = $errValues->value->value;
            }

            return [
                (new ObjectType(ResponseInvalidException::class))
                    ->mergeAttributes([
                        'error_key' => $errKey,
                        'error_values' => $errValues,
                    ]),
            ];
        }

        return [];
    }

    /**
     * @return array<int, mixed>
     */
    public function extractErrValuesArr(Expr\Array_ $array): array
    {
        $errors = [];

        foreach ($array->items as $item) {
            if ($item->value instanceof Scalar\String_
                || $item->value instanceof Scalar\Int_
                || $item->value instanceof Scalar\Float_) {
                $errors[] = $item->value->value;
            }
        }

        return $errors;
    }
}
