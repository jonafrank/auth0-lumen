<?php
$app = app();

$app->group(['namespace' => 'Auth0\Lumen\Http'], function($app) {
    $app->get('/auth0/callback', 'Auth0Controller@callback');
});
