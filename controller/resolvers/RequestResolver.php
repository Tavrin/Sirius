<?php


namespace Sirius\controller\resolvers;


use Sirius\http\Request;

class RequestResolver
{
    public function checkValue($argument):bool
    {
        return Request::class === $argument['type'];
    }

    public function setValue(Request $request): Request
    {
        return $request;
    }
}