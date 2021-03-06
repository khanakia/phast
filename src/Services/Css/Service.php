<?php

namespace Kibo\Phast\Services\Css;

use Kibo\Phast\Services\BaseService;
use Kibo\Phast\ValueObjects\Resource;

class Service extends BaseService {
    protected function makeResponse(Resource $resource, array $request) {
        $response = parent::makeResponse($resource, $request);
        $response->setHeader('Content-Type', 'text/css');
        return $response;
    }
}
