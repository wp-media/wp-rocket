<?php

return [
   'singleLicense' => [
     'config' => [
        'customer_data' => (object)['licence_account' => 1],
     ],
     'expected' => 'Single',
   ],
   'infiniteLicense' => [
     'config' => [
        'customer_data' => (object)['licence_account' => -1],
     ],
     'expected' => 'Infinite',
   ],
   'plusLicense' => [
     'config' => [
        'customer_data' => (object)['licence_account' => 3],
     ],
     'expected' => 'Plus',
   ],
   'unavailableLicense' => [
     'config' => [
        'customer_data' => null,
     ],
     'expected' => 'Unavailable',
   ],
];
