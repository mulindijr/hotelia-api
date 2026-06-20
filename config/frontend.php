<?php

/*
|--------------------------------------------------------------------------
| Frontend Application URL
|--------------------------------------------------------------------------
|
| Fetches the 'FRONTEND_URL' from the .env file.
| Defaults to 'http://localhost:3000' if the environment variable is not defined.
|
*/

return [
  'url' => env('FRONTEND_URL', 'http://localhost:3000'),
];