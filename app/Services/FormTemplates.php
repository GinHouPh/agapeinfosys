<?php

namespace App\Services;

use App\Enums\FormFieldType;

/**
 * Pre-built form templates for the visual form editor.
 */
final class FormTemplates
{
    /**
     * @return list<array{id: string, name: string, category: string, description: string, fields: list<array<string, mixed>>}>
     */
    public static function all(): array
    {
        return [
            [
                'id' => 'contact',
                'name' => 'Contact Us',
                'category' => 'General',
                'description' => 'Classic inquiry form with name, email, subject, message, and GDPR consent.',
                'fields' => [
                    self::field('Full Name', 'full_name', FormFieldType::Text, ['width' => 'half', 'is_required' => true, 'placeholder' => 'Jane Doe']),
                    self::field('Email Address', 'email', FormFieldType::Email, ['width' => 'half', 'is_required' => true, 'placeholder' => 'jane@company.com']),
                    self::field('Subject', 'subject', FormFieldType::Text, ['width' => 'full', 'is_required' => true, 'placeholder' => 'How can we help?']),
                    self::field('Message', 'message', FormFieldType::Textarea, ['width' => 'full', 'is_required' => true, 'placeholder' => 'Tell us about your inquiry...']),
                    self::field('I agree to the Privacy Policy and consent to my data being processed.', 'gdpr_consent', FormFieldType::GdprConsent, ['is_required' => true]),
                ],
            ],
            [
                'id' => 'support',
                'name' => 'Support Request',
                'category' => 'Support',
                'description' => 'Structured helpdesk ticket with priority, category, screenshot upload, and details.',
                'fields' => [
                    self::field('Your Name', 'name', FormFieldType::Text, ['width' => 'half', 'is_required' => true]),
                    self::field('Account Email', 'email', FormFieldType::Email, ['width' => 'half', 'is_required' => true]),
                    self::field('Topic', 'topic', FormFieldType::Select, ['width' => 'half', 'is_required' => true, 'options' => ['Technical Issue', 'Billing & Invoices', 'Account Access', 'Feature Request']]),
                    self::field('Priority', 'priority', FormFieldType::Radio, ['width' => 'half', 'is_required' => true, 'options' => ['Low', 'Normal', 'High', 'Urgent']]),
                    self::field('Issue Description', 'description', FormFieldType::Textarea, ['is_required' => true, 'placeholder' => 'Steps to reproduce...']),
                    self::field('Attachment / Screenshot', 'attachment', FormFieldType::File, ['file_settings' => ['mimes' => ['png', 'jpg', 'jpeg', 'pdf'], 'max_kilobytes' => 5120]]),
                ],
            ],
            [
                'id' => 'event',
                'name' => 'Event Registration',
                'category' => 'Events',
                'description' => 'Attendee signup with company, ticket tier, dietary notes, and terms.',
                'fields' => [
                    self::field('Attendee Name', 'attendee_name', FormFieldType::Text, ['width' => 'half', 'is_required' => true]),
                    self::field('Email', 'email', FormFieldType::Email, ['width' => 'half', 'is_required' => true]),
                    self::field('Phone', 'phone', FormFieldType::Phone, ['width' => 'half']),
                    self::field('Ticket Type', 'ticket_type', FormFieldType::Select, ['width' => 'half', 'is_required' => true, 'options' => ['General Admission', 'VIP Pass', 'Student / Academic', 'Virtual']]),
                    self::field('Sessions Interested In', 'sessions', FormFieldType::Checkbox, ['options' => ['Keynote', 'Technical Workshop', 'Networking Lunch', 'Panel Q&A']]),
                    self::field('I accept the Event Terms & Conditions.', 'terms', FormFieldType::Terms, ['is_required' => true]),
                ],
            ],
            [
                'id' => 'feedback',
                'name' => 'Customer Feedback',
                'category' => 'Research',
                'description' => 'Quick satisfaction rating, recommendation likelihood, and open comments.',
                'fields' => [
                    self::field('Overall Experience', 'experience', FormFieldType::Radio, ['is_required' => true, 'options' => ['Excellent', 'Good', 'Neutral', 'Poor']]),
                    self::field('What did you like most?', 'liked', FormFieldType::Textarea, ['placeholder' => 'Share what worked well...']),
                    self::field('What could we improve?', 'improvements', FormFieldType::Textarea, ['placeholder' => 'Any friction points?']),
                    self::field('May we follow up with you?', 'follow_up_email', FormFieldType::Email, ['placeholder' => 'Optional email address']),
                ],
            ],
            [
                'id' => 'quote',
                'name' => 'Quote / Project Request',
                'category' => 'Sales',
                'description' => 'Lead capture for custom project scoping, budget range, and timeline.',
                'fields' => [
                    self::field('Contact Name', 'name', FormFieldType::Text, ['width' => 'third', 'is_required' => true, 'group' => 'Contact']),
                    self::field('Work Email', 'email', FormFieldType::Email, ['width' => 'third', 'is_required' => true, 'group' => 'Contact']),
                    self::field('Company', 'company', FormFieldType::Text, ['width' => 'third', 'group' => 'Contact']),
                    self::field('Estimated Budget', 'budget', FormFieldType::Select, ['width' => 'half', 'is_required' => true, 'group' => 'Project Scope', 'options' => ['Under $5,000', '$5,000 - $15,000', '$15,000 - $50,000', '$50,000+']]),
                    self::field('Target Launch Date', 'launch_date', FormFieldType::Date, ['width' => 'half', 'group' => 'Project Scope']),
                    self::field('Project Brief', 'brief', FormFieldType::Textarea, ['is_required' => true, 'group' => 'Project Scope']),
                ],
            ],
            [
                'id' => 'job',
                'name' => 'Job Application',
                'category' => 'HR',
                'description' => 'Candidate application with portfolio URL, role selection, and resume upload.',
                'fields' => [
                    self::field('Full Name', 'full_name', FormFieldType::Text, ['width' => 'half', 'is_required' => true]),
                    self::field('Email', 'email', FormFieldType::Email, ['width' => 'half', 'is_required' => true]),
                    self::field('Phone', 'phone', FormFieldType::Phone, ['width' => 'half', 'is_required' => true]),
                    self::field('Portfolio / LinkedIn URL', 'portfolio_url', FormFieldType::Url, ['width' => 'half', 'placeholder' => 'https://...']),
                    self::field('Resume / CV (PDF)', 'resume', FormFieldType::File, ['is_required' => true, 'file_settings' => ['mimes' => ['pdf', 'doc', 'docx'], 'max_kilobytes' => 10240]]),
                    self::field('Cover Note', 'cover_note', FormFieldType::Textarea),
                ],
            ],
            [
                'id' => 'newsletter',
                'name' => 'Newsletter Signup',
                'category' => 'Marketing',
                'description' => 'Minimalist email subscription with topic preferences and consent.',
                'fields' => [
                    self::field('First Name', 'first_name', FormFieldType::Text, ['width' => 'half']),
                    self::field('Email Address', 'email', FormFieldType::Email, ['width' => 'half', 'is_required' => true]),
                    self::field('Topics', 'topics', FormFieldType::Checkbox, ['options' => ['Product Updates', 'Engineering Blog', 'Case Studies']]),
                    self::field('Yes, send me occasional emails. I can unsubscribe anytime.', 'gdpr', FormFieldType::GdprConsent, ['is_required' => true]),
                ],
            ],
            [
                'id' => 'intake',
                'name' => 'Client / Patient Intake',
                'category' => 'Operations',
                'description' => 'Onboarding intake with contact details, preferred appointment date/time, and notes.',
                'fields' => [
                    self::field('Full Name', 'full_name', FormFieldType::Text, ['width' => 'half', 'is_required' => true]),
                    self::field('Email', 'email', FormFieldType::Email, ['width' => 'half', 'is_required' => true]),
                    self::field('Preferred Date', 'preferred_date', FormFieldType::Date, ['width' => 'half']),
                    self::field('Preferred Time', 'preferred_time', FormFieldType::Time, ['width' => 'half']),
                    self::field('Additional Notes', 'notes', FormFieldType::Textarea),
                ],
            ],
            [
                'id' => 'survey',
                'name' => 'Product Survey',
                'category' => 'Research',
                'description' => 'Multi-choice usage survey with conditional follow-up question.',
                'fields' => [
                    self::field('How often do you use our product?', 'frequency', FormFieldType::Radio, ['is_required' => true, 'options' => ['Daily', 'Weekly', 'Monthly', 'Rarely']]),
                    self::field('Which features matter most?', 'features', FormFieldType::Checkbox, ['options' => ['Speed', 'Integrations', 'Reporting', 'Support']]),
                    self::field('Anything else we should know?', 'comments', FormFieldType::Textarea),
                ],
            ],
            [
                'id' => 'order',
                'name' => 'Order Inquiry',
                'category' => 'Sales',
                'description' => 'Wholesale or custom order request with quantity, SKU, and delivery date.',
                'fields' => [
                    self::field('Customer Name', 'customer_name', FormFieldType::Text, ['width' => 'half', 'is_required' => true]),
                    self::field('Email', 'email', FormFieldType::Email, ['width' => 'half', 'is_required' => true]),
                    self::field('Product / SKU', 'product_sku', FormFieldType::Text, ['width' => 'half', 'is_required' => true]),
                    self::field('Quantity', 'quantity', FormFieldType::Number, ['width' => 'half', 'is_required' => true]),
                    self::field('Requested Delivery Date', 'delivery_date', FormFieldType::Date),
                    self::field('Shipping Notes', 'shipping_notes', FormFieldType::Textarea),
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function field(string $label, string $name, FormFieldType $type, array $overrides = []): array
    {
        return array_merge([
            'label' => $label,
            'name' => $name,
            'type' => $type->value,
            'group' => null,
            'options' => null,
            'has_text_input' => false,
            'text_placeholder' => null,
            'placeholder' => null,
            'help_text' => null,
            'default_value' => null,
            'width' => 'full',
            'css_class' => null,
            'validation' => [],
            'condition' => [],
            'condition_match' => 'all',
            'file_settings' => null,
            'is_required' => false,
        ], $overrides);
    }
}
