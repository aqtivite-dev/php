<?php

namespace Aqtivite\Php\Modules;

use Aqtivite\Php\Resources\Common\CountryResource;
use Aqtivite\Php\Resources\Common\CurrencyResource;
use Aqtivite\Php\Resources\Common\DistrictResource;
use Aqtivite\Php\Resources\Common\HallResource;
use Aqtivite\Php\Resources\Common\NeighborhoodResource;
use Aqtivite\Php\Resources\Common\ProvinceResource;
use Aqtivite\Php\Resources\Common\RegionResource;
use Aqtivite\Php\Resources\Common\VenueResource;

class CommonModule extends Module
{
    public function venues(): VenueResource
    {
        return new VenueResource($this->http);
    }

    public function halls(): HallResource
    {
        return new HallResource($this->http);
    }

    public function currencies(): CurrencyResource
    {
        return new CurrencyResource($this->http);
    }

    public function regions(): RegionResource
    {
        return new RegionResource($this->http);
    }

    public function countries(): CountryResource
    {
        return new CountryResource($this->http);
    }

    public function provinces(): ProvinceResource
    {
        return new ProvinceResource($this->http);
    }

    public function districts(): DistrictResource
    {
        return new DistrictResource($this->http);
    }

    public function neighborhoods(): NeighborhoodResource
    {
        return new NeighborhoodResource($this->http);
    }
}
