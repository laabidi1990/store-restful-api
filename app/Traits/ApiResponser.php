<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ApiResponser
{
    private function successResponse($data, $code) 
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code) 
    {
        return response()->json([
            'error' => $message, 
            'code' => $code,
        ]);
    }

    protected function showAll(Collection $collection, $code = 200) 
    {
        if ($collection->isEmpty()) {
            return $this->successResponse(['data' => $collection], $code);
        }

        $transformer = $collection->first()->transformer;

        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);

        $collection = $this->paginate($collection, 10);
        $collection = $this->transformData($collection, $transformer);

        return $this->successResponse($collection, $code);
    }
    
    protected function showOne(Model $model, $code = 200) 
    {
        $transformer = $model->transformer;
        $model = $this->transformData($model, $transformer);

        return $this->successResponse($model, $code);
    }

    protected function showMessage($message, $code = 200) 
    {
        return $this->successResponse(['data' => $message], $code);
    }

    protected function sortData(Collection $collection, $transformer)
    {
        if (request()->has('sort_by')) {
            $attribute = $transformer::originalAttributes(request()->sort_by);
            $collection = $collection->sortBy($attribute);
        }

        return $collection;
    }

    protected function filterData(Collection $collection, $transformer)
    {
        foreach (request()->query() as $query => $value) {
            $attribute = $transformer::originalAttributes($query);

            if (isset($attribute, $value)) {
                $collection = $collection->where($attribute, $value);
            }
        }

        return $collection;
    }

    private function transformData($data, $transformer)
    {
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();
    }

    protected function paginate(Collection $collection, int $perPage)
    {
        $rules = [
            'per_page' => 'integer|min:2|max:25',
        ];

        $validator = Validator::make(request()->all(), $rules);

        if ($validator->fails())  {
            throw new HttpException(500, $validator->errors());
        }
        

        if (request()->has('per_page')) {
            $perPage = (int) request()->per_page;
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    public function cacheResponce($data)
    {
        $url = request()->url();
        $queryParams = request()->query();
        ksort($queryParams);

        $queryString = http_build_query($queryParams);
        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30, function () use ($data) {
            return $data;
        });

    }
}