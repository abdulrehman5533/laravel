<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $table = 'hr_interviews';

    protected $fillable = [
        'candidate_id', 'interviewer_id', 'scheduled_at', 'location',
        'feedback', 'rating', 'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function feedback()
    {
        return $this->hasMany(InterviewFeedback::class);
    }
}
