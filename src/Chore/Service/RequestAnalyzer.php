<?php

namespace App\Chore\Service;

use Symfony\Component\HttpFoundation\Request;

class RequestAnalyzer
{
    /**
     * Get the sort parameters from the request.
     * The sort parameter is in the format 'attribute_direction'.
     * The attribute is the name of the attribute to sort by and the direction is either 'up' or 'down'.
     * The function returns an array with the attribute as key and the direction as value.
     * If the sort parameter is not set, the function returns an empty array.
     * If the sort parameter is not in the correct format, the function returns an empty array and shows a warning.
     * If the attribute is not a valid attribute of the User class, the function returns an empty array and shows a warning.
     * If the direction is not 'up' or 'down', the function returns an empty array and shows a warning.
     *
     * @param $request
     * @return array
     */
    public static function getSortParams(Request $request, mixed $object): array
    {
        $sort = $request->request->get('sort');

        if(in_array($sort, [null, '', 'none', 'default', 'null'])) {
            return [];
        }

        if(str_contains($sort, '_')) {
            $sort = explode('_', $sort);

            if(!in_array($sort[1], ['down', 'up'])) {
                FrontLogger::showError('Invalid sort direction : ' . $sort[1] . '.');
                return [];
            }

            if(!method_exists($object, 'getAttributes')) {
                FrontLogger::showError('Invalid object : ' . $object::class . ' does not have a getAttributes method.');
                return [];
            }

            if(!in_array($sort[0], $object::getAttributes())) {
                FrontLogger::showError('Invalid sort attribute : ' . $sort[0] . ' is not a attribute of '. $object::class . '.');
                return [];
            }

            return [$sort[0] => str_replace(['down', 'up'], ['ASC', 'DESC'], $sort[1])];
        }

        FrontLogger::showError('Invalid sort format : ' . $sort . '.');
        return [];
    }
}