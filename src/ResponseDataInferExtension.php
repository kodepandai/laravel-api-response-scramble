<?php

namespace KodePandai\ApiResponseScramble;

use Dedoc\Scramble\Infer\Extensions\ExpressionTypeInferExtension;
use Dedoc\Scramble\Infer\Scope\Scope;
use Dedoc\Scramble\Support\Type;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\Literal\LiteralIntegerType;
use Dedoc\Scramble\Support\Type\TypeHelper;
use Illuminate\Http\JsonResponse;
use KodePandai\ApiResponse\Facades\ApiResponse;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\NodeFinder;

class ResponseDataInferExtension implements ExpressionTypeInferExtension
{
    public function getType(Expr $node, Scope $scope): ?Type\Type
    {
        // TODO: parse title, message, statusCode call

        $scopeType = $scope->getType($node);

        if ($node instanceof Expr\MethodCall
            && $node->name instanceof Identifier
            && $node->name->name === 'data') { // callToData
            //
            $callToApiResponse = (new NodeFinder)->findFirst(
                $node, function (Node $node) {
                    return $node instanceof Node\Expr\StaticCall
                        && $node->class instanceof Node\Name
                        && $node->class->name === ApiResponse::class;
                }
            );

            if ($callToApiResponse) {
                return new Generic(
                    JsonResponse::class,
                    [
                        new Type\KeyedArrayType([
                            new Type\ArrayItemType_(
                                'success',
                                new Type\Literal\LiteralBooleanType(true),
                            ),
                            new Type\ArrayItemType_(
                                'title',
                                new Type\StringType,
                            ),
                            new Type\ArrayItemType_(
                                'message',
                                new Type\StringType,
                            ),
                            new Type\ArrayItemType_(
                                'data',
                                TypeHelper::getArgType($scope, $node->args, ['data', 0], new ArrayType)
                            ),
                        ]),
                        new LiteralIntegerType(200),
                        // TypeHelper::getArgType($scope, $node->args, ['data', 0], new ArrayType),
                        // TypeHelper::getArgType($scope, $node->args, ['status', 1], new LiteralIntegerType(200)),
                        // TypeHelper::getArgType($scope, $node->args, ['headers', 2], new ArrayType),
                    ],
                );
            }

        }

        return null;
    }
}
