<?php

namespace App\Exports;

use App\Models\Team;
use Maatwebsite\Excel\Concerns\{FromQuery, WithHeadings, WithMapping, ShouldAutoSize};

class TeamsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return Team::query()->with('users:id,name,email,team_id');
    }

    public function headings(): array
    {
        return ['ID', 'Team Name', 'Description', 'Avatar', 'Members Names', 'Members Emails'];
    }

    public function map($team): array
    {
        return [
            $team->id,
            $team->name,
            $team->description,
            $team->avatar,
            $team->users->pluck('name')->implode(','),
            $team->users->pluck('email')->implode(','),
        ];
    }
}