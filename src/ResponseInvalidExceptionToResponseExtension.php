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
use KodePandai\ApiResponseScramble\Exceptions\ResponseInvalidException;

/**
 * Handle ObjectType(ApiValidationException::class) to correct response body.
 *
 * @see Dedoc\Scramble\Support\ExceptionToResponseExtensions\ValidationExceptionToResponseExtension
 */
class ResponseInvalidExceptionToResponseExtension extends ExceptionToResponseExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType
            && $type->isInstanceOf(ResponseInvalidException::class);
    }

    public function toResponse(Type $type): Response
    {

        $errors = (new OpenApiTypes\ArrayType)
            ->setItems(new OpenApiTypes\StringType);

        if ($key = $type->getAttribute('error_key')) {
            $errors = (new OpenApiTypes\ObjectType);
            $val = $type->getAttribute('error_values');
            if (is_array($val)) {
                $errType = (new OpenApiTypes\ArrayType)->default($val);
            } else { // string
                $errType = (new OpenApiTypes\StringType)->default($val);
            }
            $errors->addProperty($key, $errType);
        }

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
            ->addProperty('errors', $errors)
            ->setRequired(['success', 'title', 'message', 'errors']);

        return Response::make((int) ('422'.rand(1111, 9999)))
            ->description('Validation error')
            ->setContent(
                'application/json',
                Schema::fromType($validationResponseBodyType)
            );
    }

    // TODO: resolve reference back to original name, not class name
    //
    public function reference(ObjectType $type): Reference
    {
        $name = Str::start($type->name, '\\');

        $name .= '_'.md5((string) json_encode([
            'key' => $type->getAttribute('error_key'),
            'value' => $type->getAttribute('value'),
        ]));

        return new Reference('responses', $name, $this->components);
    }
}
