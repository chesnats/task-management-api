<?php

namespace App\Imports;

use App\Models\{Team, User};
use Maatwebsite\Excel\Concerns\{ToCollection, WithHeadingRow};
use Illuminate\Support\Collection;

class TeamsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['team_name'])) {
            continue;
            }

            $team = Team::updateOrCreate(
                ['name' => trim($row['team_name'])],
                ['description' => $row['description'] ?? null, 'avatar' => $row['avatar'] ?? null]
            );

            $names  = explode(',', $row['members_names'] ?? '');
            $emails = explode(',', $row['members_emails'] ?? '');

            collect($emails)->combine($names)->each(function ($name, $email) use ($team) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    User::updateOrCreate(
                        ['email' => $email], 
                        [
                            'name'     => $name,
                            'team_id'  => $team->id,
                            'password' => bcrypt('password'),
                        ]
                    );
                }
            });
        }
    }
}