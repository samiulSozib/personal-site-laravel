<?php

namespace App\Http\Resources;

use App\Support\Localized;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Experience */
class ExperienceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $location = $this->location ? Localized::text($this->location) : null;

        return [
            'id' => $this->id,
            'company' => $this->company,
            'companyUrl' => $this->company_url,
            'role' => Localized::text($this->role),
            'employmentType' => $this->employment_type,
            'location' => $location && $location['en'] !== '' ? $location : null,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'current' => $this->end_date === null,
            'summary' => Localized::text($this->summary),
            'highlights' => Localized::list($this->highlights),
            'stack' => $this->stack ?? [],
        ];
    }
}
