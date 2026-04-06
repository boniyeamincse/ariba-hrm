<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Patient extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'tenant_id',
        'uhid',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'phone',
        'email',
        'national_id_no',
        'passport_no',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'marital_status',
        'occupation',
        'religion',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'photo_path',
        'photo_thumb_path',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected $appends = [
        'photo_url',
        'photo_thumb_url',
    ];

    public function searchableAs(): string
    {
        return 'patients';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'tenant_id' => (int) $this->tenant_id,
            'uhid' => (string) $this->uhid,
            'name' => trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' ')),
            'first_name' => (string) $this->first_name,
            'last_name' => (string) ($this->last_name ?? ''),
            'phone' => (string) ($this->phone ?? ''),
            'national_id_no' => (string) ($this->national_id_no ?? ''),
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
        ];
    }

    public function history(): HasOne
    {
        return $this->hasOne(PatientHistory::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(PatientVisit::class)->latest('visit_at');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return \Storage::disk('s3')->url($this->photo_path);
    }

    public function getPhotoThumbUrlAttribute(): ?string
    {
        if (! $this->photo_thumb_path) {
            return null;
        }

        return \Storage::disk('s3')->url($this->photo_thumb_path);
    }
}
