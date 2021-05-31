<?php


namespace Sirius\controller\resolvers;


use Sirius\http\Request;

class RequestAttributeResolver
{
    public function checkValue($argument, Request $request): bool
    {
        return $request->hasAttribute($argument['name']);
    }

    public function setValue(Request $request, $argument)
    {
        return $request->getAttribute($argument["name"]);
    }
}