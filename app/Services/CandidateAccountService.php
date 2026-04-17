<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\User;

class CandidateAccountService
{
    public function getPreferredAccountType(User $user): ?string
    {
        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $preferred = $metadata['account_type'] ?? null;

        return in_array($preferred, ['candidate', 'employer'], true) ? $preferred : null;
    }

    public function setPreferredAccountType(User $user, string $type): void
    {
        if (! in_array($type, ['candidate', 'employer'], true)) {
            return;
        }

        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $metadata['account_type'] = $type;
        $user->metadata = $metadata;
        $user->save();
    }

    public function hasCandidateAccount(User $user): bool
    {
        if ($user->role === 'candidate') {
            return true;
        }

        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];

        return in_array('candidate', $accountTypes, true);
    }

    public function activateFor(User $user): Candidate
    {
        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];

        $accountTypes[] = 'candidate';
        if (in_array($user->role, ['hr', 'admin'], true)) {
            $accountTypes[] = 'employer';
        }

        $metadata['account_types'] = array_values(array_unique(array_filter($accountTypes, 'is_string')));
        $metadata['account_type'] = in_array($user->role, ['hr', 'admin'], true)
            ? ($this->getPreferredAccountType($user) === 'candidate' ? 'candidate' : 'employer')
            : 'candidate';

        if (! isset($metadata['phone']) && $user->candidate?->phone) {
            $metadata['phone'] = $user->candidate->phone;
        }

        $user->metadata = $metadata;
        $user->save();

        return $this->resolveFor($user);
    }

    public function resolveFor(User $user): Candidate
    {
        $candidate = Candidate::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $candidate && is_string($user->email) && $user->email !== '') {
            $candidate = Candidate::query()
                ->where('email', $user->email)
                ->first();
        }

        $attributes = array_filter([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $this->extractPhone($user),
        ], fn ($value) => ! is_null($value) && $value !== '');

        if ($candidate) {
            $candidate->fill($attributes);

            if ($candidate->isDirty()) {
                $candidate->save();
            }

            return $candidate;
        }

        return Candidate::query()->create($attributes);
    }

    private function extractPhone(User $user): ?string
    {
        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $phone = $metadata['phone'] ?? null;

        return is_string($phone) && trim($phone) !== '' ? trim($phone) : null;
    }
}
