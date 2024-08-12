<?php

namespace KodePandai\ApiResponseScramble;

use Dedoc\Scramble\Support\OperationExtensions\RulesExtractor\ValidateCallExtractor as Extractor;
use Dedoc\Scramble\Support\OperationExtensions\RulesExtractor\ValidationNodesResult;
use Dedoc\Scramble\Support\SchemaClassDocReflector;
use KodePandai\ApiResponse\Facades\ApiResponse as ApiResponseFacade;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

class ValidateCallExtractor extends Extractor
{
    private ?Node\FunctionLike $_handle;

    public function __construct(?Node\FunctionLike $handle)
    {
        $this->_handle = $handle;

        parent::__construct($handle);
    }

    public function node(): ?ValidationNodesResult
    {
        $methodNode = $this->_handle;

        // find ApiResponse::validateOrFail

        /** @var Node\Expr\MethodCall $callToValidate */
        $callToValidate = (new NodeFinder)->findFirst(
            $methodNode,
            function (Node $node) {
                return $node instanceof Node\Expr\StaticCall
                    && $node->class instanceof Node\Name
                    && $node->class->name === ApiResponseFacade::class
                    && $node->name instanceof Node\Identifier
                    && $node->name->name === 'validateOrFail';
            }
        );

        $validationRules = $callToValidate->args[0] ?? null;

        if (! $validationRules) {
            return null;
        }

        $attr = $callToValidate->getAttribute('parsedPhpDoc', new PhpDocNode([]));
        $phpDocReflector = new SchemaClassDocReflector($attr);

        return new ValidationNodesResult(
            $validationRules instanceof Node\Arg ? $validationRules->value : $validationRules,
            schemaName: $phpDocReflector->getSchemaName(),
            description: $phpDocReflector->getDescription(),
        );
    }
}
