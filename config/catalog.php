<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Opening Stock For The Starter Catalog
    |--------------------------------------------------------------------------
    |
    | GroceryCatalogSeeder records demo opening stock (as inventory ledger
    | adjustments) outside production. In production, stock should come from
    | purchase orders or admin inventory adjustments, so the seeder only adds
    | the catalog itself unless this is explicitly switched on.
    |
    */

    'seed_opening_stock' => (bool) env('CATALOG_SEED_OPENING_STOCK', false),

];
