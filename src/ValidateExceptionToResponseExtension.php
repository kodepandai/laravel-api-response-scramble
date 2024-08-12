<?php

namespace KodePandai\ApiResponseScramble;

use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Support\Str;
use KodePandai\ApiResponse\Exceptions\ApiValidationException;
use KodePandai\ApiResponseScramble\Exceptions\ValidateException;

/**
 * Handle ObjectType(ApiValidationException::class) to correct response body.
 *
 * @see Dedoc\Scramble\Support\ExceptionToResponseExtensions\ValidationExceptionToResponseExtension
 */
class ValidateExceptionToResponseExtension extends ExceptionToResponseExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType
            && $type->isInstanceOf(ValidateException::class);
    }

    public function toResponse(Type $type): Response
    {
        $validationResponseBodyType = (new OpenApiTypes\ObjectType)
            ->addProperty(
                'success',
                (new OpenApiTypes\BooleanType)->default(false)
            )
            ->addProperty(
                'title',
                (new OpenApiTypes\StringType)->default(__('api-response::trans.validation_error'))
            )
            ->addProperty(
                'message',
                (new OpenApiTypes\StringType)->default(__('api-response::trans.given_data_was_invalid'))
            )
            ->addProperty(
                'data',
                (new OpenApiTypes\ArrayType)->default([])->example([])
            )
            ->addProperty(
                'errors',
                (new OpenApiTypes\ObjectType)
                    ->setDescription('A detailed description of each field that failed validation.')
                    ->additionalProperties(
                        (new OpenApiTypes\ArrayType)->setItems(new OpenApiTypes\StringType)
                    )
            )
            ->setRequired(['success', 'title', 'message', 'errors']);

        return Response::make(422)
            ->description('Validation error')
            ->setContent(
                'application/json',
                Schema::fromType($validationResponseBodyType)
            );
    }

    public function reference(ObjectType $type): Reference
    {
        return new Reference('responses', Str::start($type->name, '\\'), $this->components);
    }
}
