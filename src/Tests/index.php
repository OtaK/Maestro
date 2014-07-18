<?php

    use Maestro\Maestro;
    use Maestro\HTTP\Request;
    use Maestro\HTTP\Response;

    Maestro::gi()
        ->set('app path', __DIR__.'/app/')
        ->set('controller namespace', 'Maestro\\Tests\\Controllers')
        ->route()
            ->get('/test.json', function(Request $req, Response $res) {
                $res->json(array('yolo' => 'pls'));
            })
            ->get('/test.html', 'test#index');

    Maestro::gi()->conduct();