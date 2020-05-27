<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Validation\ValidationException;


class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $transformer)
    {
        $transfromedInput = [];
        foreach ($request->request->all() as $input => $value) {
            $transfromedInput[$transformer::originalAttributes($input)] = $value;
        }
        $request->replace($transfromedInput);
        
        $response = $next($request);

        if (isset($response->exception) && $response->exception instanceof ValidationException) {
            $data = $response->getData();

            $transfromedErrors = []; 
            foreach ($data->error as $field => $errorMessage) {
                $transformedField = $transformer::transformedAttributes($field);
            }

            $transfromedErrors[$transformedField] = str_replace($field, $transformedField, $errorMessage);

            $data->error = $transfromedErrors;

            $response->setData($data);
        }

        return $response;
    }
}
