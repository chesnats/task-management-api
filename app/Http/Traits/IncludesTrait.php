<?php

namespace App\Http\Traits;

trait IncludesTrait
{
    /**
     * Parse include parameter and return eager load relations.
     * Example: ?include=team,tasks → ['team', 'tasks']
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $allowedIncludes
     * @return array
     */
    protected function parseIncludes($request, array $allowedIncludes = [])
    {
        $includes = $request->query('include', '');
        
        if (!$includes) {
            return [];
        }

        $requested = array_map('trim', explode(',', $includes));
        
        // Filter to only allowed includes
        if (!empty($allowedIncludes)) {
            $requested = array_intersect($requested, $allowedIncludes);
        }

        return $requested;
    }
}
