<?php

namespace App\Models;

use App\Enums\RequirementStatus;
use Database\Factories\RequirementSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A requirement specification brief submitted through the public form.
 */
class RequirementSubmission extends Model
{
    /** @use HasFactory<RequirementSubmissionFactory> */
    use HasFactory;

    /**
     * Solution categories the client can request.
     *
     * @var list<string>
     */
    public const SOLUTION_OPTIONS = [
        'Web application',
        'Mobile app (iOS)',
        'Mobile app (Android)',
        'Cross-platform mobile',
        'Desktop software',
        'API / backend only',
        'UI/UX design',
        'Maintenance & support',
    ];

    /**
     * Third-party systems the product may need to talk to.
     *
     * @var list<string>
     */
    public const INTEGRATION_OPTIONS = [
        'Payment gateway',
        'SMS / WhatsApp',
        'Email / SMTP',
        'Maps & geolocation',
        'Analytics & reporting',
        'CRM',
        'ERP',
        'Accounting / billing',
        'None yet',
    ];

    /**
     * @var list<string>
     */
    public const INDUSTRY_OPTIONS = [
        'OTT & media',
        'Appointment booking',
        'Hospital & healthcare',
        'POS & retail',
        'Education & e-learning',
        'Logistics & fleet',
        'FinTech & banking',
        'E-commerce',
        'HR & payroll',
        'Travel & hospitality',
        'Real estate',
        'Other',
    ];

    /**
     * @var list<string>
     */
    public const USER_SCALE_OPTIONS = [
        'Under 100 users',
        '100 – 1,000 users',
        '1,000 – 10,000 users',
        'More than 10,000 users',
    ];

    /**
     * @var list<string>
     */
    public const BUDGET_OPTIONS = [
        'Under ₹2,00,000',
        '₹2,00,000 – ₹5,00,000',
        '₹5,00,000 – ₹15,00,000',
        'Above ₹15,00,000',
        'Not decided yet',
    ];

    /**
     * @var list<string>
     */
    public const TIMELINE_OPTIONS = [
        'As soon as possible',
        'Within 1 month',
        '1 – 3 months',
        '3 – 6 months',
        'More than 6 months',
        'Flexible',
    ];

    protected $fillable = [
        'reference',
        'name',
        'email',
        'phone',
        'company',
        'industry',
        'project_name',
        'brief',
        'solutions',
        'features',
        'integrations',
        'user_scale',
        'budget',
        'timeline',
        'notes',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'solutions' => 'array',
            'integrations' => 'array',
            'status' => RequirementStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            if ($submission->reference !== null && $submission->reference !== '') {
                return;
            }

            do {
                $reference = 'REQ-'.Str::upper(Str::random(6));
            } while (static::query()->where('reference', $reference)->exists());

            $submission->reference = $reference;
        });
    }
}
