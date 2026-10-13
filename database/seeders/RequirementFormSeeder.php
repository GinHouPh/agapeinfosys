<?php

namespace Database\Seeders;

use App\Enums\FormFieldType;
use App\Models\Form;
use Illuminate\Database\Seeder;

class RequirementFormSeeder extends Seeder
{
    /**
     * Seed the requirement specification form as the first builder-managed
     * form, carrying over the original SRS questionnaire.
     */
    public function run(): void
    {
        $industries = [
            'OTT & media', 'Appointment booking', 'Hospital & healthcare', 'POS & retail',
            'Education & e-learning', 'Logistics & fleet', 'FinTech & banking', 'E-commerce',
            'HR & payroll', 'Travel & hospitality', 'Real estate', 'Other',
        ];

        $solutions = [
            'Web application', 'Mobile app (iOS)', 'Mobile app (Android)', 'Cross-platform mobile',
            'Desktop software', 'API / backend only', 'UI/UX design', 'Maintenance & support',
        ];

        $integrations = [
            'Payment gateway', 'SMS / WhatsApp', 'Email / SMTP', 'Maps & geolocation',
            'Analytics & reporting', 'CRM', 'ERP', 'Accounting / billing', 'None yet',
        ];

        $fields = [
            ['label' => 'Full Name', 'name' => 'name', 'type' => FormFieldType::Text, 'group' => 'Your details', 'placeholder' => 'Your full name', 'is_required' => true],
            ['label' => 'Email', 'name' => 'email', 'type' => FormFieldType::Email, 'group' => 'Your details', 'placeholder' => 'you@company.com', 'is_required' => true],
            ['label' => 'Contact Number', 'name' => 'phone', 'type' => FormFieldType::Phone, 'group' => 'Your details', 'placeholder' => '+91 00000 00000'],
            ['label' => 'Company', 'name' => 'company', 'type' => FormFieldType::Text, 'group' => 'Your details', 'placeholder' => 'Company name'],

            ['label' => 'Industry', 'name' => 'industry', 'type' => FormFieldType::Select, 'group' => 'The business', 'options' => $industries, 'placeholder' => 'Select your industry…', 'is_required' => true],

            ['label' => 'Project Name', 'name' => 'project_name', 'type' => FormFieldType::Text, 'group' => 'The product', 'placeholder' => 'What should we call this project?', 'is_required' => true],
            ['label' => 'Project Overview', 'name' => 'brief', 'type' => FormFieldType::Textarea, 'group' => 'The product', 'placeholder' => 'What problem does it solve, who is it for, and what does success look like?', 'help_text' => 'A few sentences are plenty — we will ask follow-ups if needed.', 'is_required' => true],

            ['label' => 'Solutions Needed', 'name' => 'solutions', 'type' => FormFieldType::Checkbox, 'group' => 'Solutions needed', 'options' => $solutions, 'is_required' => true],

            ['label' => 'Features Checklist', 'name' => 'features', 'type' => FormFieldType::Textarea, 'group' => 'Core features', 'placeholder' => 'List the must-have features — e.g. user registration, payments, admin dashboard, reports…', 'is_required' => true],

            ['label' => 'Integrations', 'name' => 'integrations', 'type' => FormFieldType::Checkbox, 'group' => 'Integrations', 'options' => $integrations],

            ['label' => 'Expected Users', 'name' => 'user_scale', 'type' => FormFieldType::Select, 'group' => 'Scale, budget & timeline', 'options' => ['Under 100 users', '100 – 1,000 users', '1,000 – 10,000 users', 'More than 10,000 users'], 'placeholder' => 'Select…', 'is_required' => true],
            ['label' => 'Budget Range', 'name' => 'budget', 'type' => FormFieldType::Select, 'group' => 'Scale, budget & timeline', 'options' => ['Under ₹2,00,000', '₹2,00,000 – ₹5,00,000', '₹5,00,000 – ₹15,00,000', 'Above ₹15,00,000', 'Not decided yet'], 'placeholder' => 'Select…', 'is_required' => true],
            ['label' => 'Timeline', 'name' => 'timeline', 'type' => FormFieldType::Select, 'group' => 'Scale, budget & timeline', 'options' => ['As soon as possible', 'Within 1 month', '1 – 3 months', '3 – 6 months', 'More than 6 months', 'Flexible'], 'placeholder' => 'Select…', 'is_required' => true],

            ['label' => 'Additional Notes', 'name' => 'notes', 'type' => FormFieldType::Textarea, 'group' => 'Anything else', 'placeholder' => 'Existing systems, compliance needs, references you like, or anything else worth knowing…'],
        ];

        $form = Form::query()->updateOrCreate(
            ['slug' => 'requirement-specification'],
            [
                'title' => 'Requirement Specification',
                'description' => 'The more detail you share, the sharper our quotation. Fill in the brief below — it takes about five minutes, and every field helps us scope honestly.',
                'submit_label' => 'Submit Requirement',
                'success_message' => 'Thank you, {{name}} — your requirement brief ({{reference}}) is with us. Our team will revert with a quotation shortly.',
                'reference_prefix' => 'REQ',
                'is_active' => true,
            ],
        );

        foreach ($fields as $index => $field) {
            $form->fields()->updateOrCreate(
                ['name' => $field['name']],
                array_merge($field, ['sort_order' => $index]),
            );
        }
    }
}
