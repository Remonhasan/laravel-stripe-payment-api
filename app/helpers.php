<?php

if (! function_exists('slg')) {
    function slg($data, int $status = 200)
    {
        response()
            ->json($data, $status)
            ->send();

        exit;
    }
}
