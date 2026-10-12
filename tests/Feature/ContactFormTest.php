<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
    }

    public function test_contact_page_renders(): void
    {
        $this->get(route('contact.create'))->assertOk();
    }

    public function test_a_visitor_can_send_an_enquiry(): void
    {
        $payload = [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '+44 20 7946 0000',
            'company' => 'Analytical Engines',
            'subject' => 'Replace our spreadsheets',
            'message' => 'We need a platform to replace our spreadsheets.',
        ];

        $this->from(route('contact.create'))
            ->post(route('contact.store'), $payload)
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'subject' => 'Replace our spreadsheets',
        ]);

        $this->assertSame(1, ContactMessage::count());
    }

    public function test_enquiry_fields_are_required(): void
    {
        $this->from(route('contact.create'))
            ->post(route('contact.store'), [])
            ->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_email_must_be_a_valid_address(): void
    {
        $this->from(route('contact.create'))
            ->post(route('contact.store'), [
                'name' => 'Grace Hopper',
                'email' => 'not-an-email',
                'message' => 'Hello there.',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_message_body_must_meet_the_minimum_length(): void
    {
        $this->from(route('contact.create'))
            ->post(route('contact.store'), [
                'name' => 'Grace Hopper',
                'email' => 'grace@example.com',
                'message' => 'Hi',
            ])
            ->assertSessionHasErrors('message');
    }
}
