<?php

namespace Aqtivite\Php\Modules;

use Aqtivite\Php\Resources\User\CurrencyResource;
use Aqtivite\Php\Resources\User\DistrictResource;
use Aqtivite\Php\Resources\User\EventCategoryResource;
use Aqtivite\Php\Resources\User\EventResource;
use Aqtivite\Php\Resources\User\OccurrenceResource;
use Aqtivite\Php\Resources\User\OrganizerResource;
use Aqtivite\Php\Resources\User\ProvinceResource;
use Aqtivite\Php\Resources\User\UserResource;
use Aqtivite\Php\Resources\User\VenueResource;
use Aqtivite\Php\Response\ApiResponse;

class UserModule extends Module
{
    public function users(): UserResource
    {
        return new UserResource($this->http);
    }

    public function events(): EventResource
    {
        return new EventResource($this->http);
    }

    public function eventCategories(): EventCategoryResource
    {
        return new EventCategoryResource($this->http);
    }

    public function occurrences(): OccurrenceResource
    {
        return new OccurrenceResource($this->http);
    }

    public function organizers(): OrganizerResource
    {
        return new OrganizerResource($this->http);
    }

    public function venues(): VenueResource
    {
        return new VenueResource($this->http);
    }

    public function provinces(): ProvinceResource
    {
        return new ProvinceResource($this->http);
    }

    public function districts(): DistrictResource
    {
        return new DistrictResource($this->http);
    }

    public function currencies(): CurrencyResource
    {
        return new CurrencyResource($this->http);
    }

    public function search(string $query): ApiResponse
    {
        return $this->http->get('/user/search', ['query' => $query]);
    }

    public function network(): ApiResponse
    {
        return $this->http->get('/user/network');
    }
}
