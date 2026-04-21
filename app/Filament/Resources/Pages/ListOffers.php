<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\OfferResource;
use Filament\Resources\Pages\ListRecords;

class ListOffers extends ListRecords
{
    protected static string $resource = OfferResource::class;
}
