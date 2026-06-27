<?php

namespace Tests\Feature\Crm;

use App\Actions\Crm\CreateCompanyAction;
use App\Actions\Crm\CreateContactAction;
use App\Actions\Crm\CreateDealAction;
use App\Actions\Crm\LogCrmActivityAction;
use App\Data\Crm\CreateCompanyData;
use App\Data\Crm\CreateContactData;
use App\Data\Crm\CreateDealData;
use App\Data\Crm\LogCrmActivityData;
use App\Enums\CrmActivityType;
use App\Models\Buyer;
use App\Models\BuyerContact;
use App\Models\CrmActivity;
use App\Models\Deal;
use App\Services\Crm\PipelineStageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesAuthenticatedOrganizationUser;
use Tests\TestCase;

class CrmCompanyTest extends TestCase
{
    use CreatesAuthenticatedOrganizationUser;
    use RefreshDatabase;

    public function test_crm_companies_page_requires_authentication(): void
    {
        $this->get(route('crm.companies.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_companies_page(): void
    {
        $user = $this->createOrganizationUser();

        $this->actingAs($user)
            ->get(route('crm.companies.index'))
            ->assertOk()
            ->assertSee('Companies');
    }

    public function test_user_can_create_company_via_livewire(): void
    {
        $user = $this->createOrganizationUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Crm\CompanyIndex::class)
            ->set('name', 'Acme Trading GmbH')
            ->set('newCountryCode', 'DE')
            ->set('city', 'Berlin')
            ->set('industry', 'Import')
            ->call('createCompany')
            ->assertRedirect();

        $this->assertDatabaseHas('buyers', [
            'name' => 'Acme Trading GmbH',
            'country_code' => 'DE',
            'city' => 'Berlin',
            'organization_id' => $user->current_organization_id,
        ]);
    }

    public function test_user_can_create_company_contact_deal_and_activity(): void
    {
        $user = $this->createOrganizationUser();

        $organization = $user->currentOrganization;
        app(PipelineStageService::class)->ensureDefaults($organization);

        $company = app(CreateCompanyAction::class)->execute(
            new CreateCompanyData(
                name: 'Global Foods Ltd',
                countryCode: 'US',
                city: 'New York',
            ),
            $user,
        );

        $contact = app(CreateContactAction::class)->execute(
            new CreateContactData(
                buyerId: $company->id,
                fullName: 'Jane Smith',
                email: 'jane@globalfoods.com',
                isPrimary: true,
            ),
            $user,
        );

        $this->assertInstanceOf(BuyerContact::class, $contact);
        $this->assertDatabaseHas('buyer_contacts', [
            'buyer_id' => $company->id,
            'full_name' => 'Jane Smith',
            'is_primary' => true,
        ]);

        $deal = app(CreateDealAction::class)->execute(
            new CreateDealData(
                buyerId: $company->id,
                title: 'Q3 Supply Contract',
                estimatedValue: 50000,
                currencyCode: 'USD',
            ),
            $user,
        );

        $this->assertInstanceOf(Deal::class, $deal);
        $this->assertDatabaseHas('deals', [
            'buyer_id' => $company->id,
            'title' => 'Q3 Supply Contract',
        ]);

        $activity = app(LogCrmActivityAction::class)->execute(
            new LogCrmActivityData(
                buyerId: $company->id,
                activityType: CrmActivityType::Call,
                subject: 'Intro call with procurement',
                body: 'Discussed olive oil volumes.',
                dealId: $deal->id,
                contactId: $contact->id,
            ),
            $user,
        );

        $this->assertInstanceOf(CrmActivity::class, $activity);
        $this->assertDatabaseHas('crm_activities', [
            'buyer_id' => $company->id,
            'deal_id' => $deal->id,
            'contact_id' => $contact->id,
            'subject' => 'Intro call with procurement',
        ]);
    }

    public function test_user_can_view_company_detail_page(): void
    {
        $user = $this->createOrganizationUser();

        $company = app(CreateCompanyAction::class)->execute(
            new CreateCompanyData(name: 'Detail Test Co', countryCode: 'TR'),
            $user,
        );

        $this->actingAs($user)
            ->get(route('crm.companies.show', $company))
            ->assertOk()
            ->assertSee('Detail Test Co');
    }

    public function test_user_can_log_activity_via_company_show(): void
    {
        $user = $this->createOrganizationUser();

        $company = Buyer::query()->create([
            'organization_id' => $user->current_organization_id,
            'name' => 'Activity Test Co',
            'country_code' => 'US',
            'status' => 'saved',
            'pipeline_stage' => 'new',
            'owner_id' => $user->id,
            'created_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Crm\CompanyShow::class, ['company' => $company])
            ->set('activeTab', 'activities')
            ->set('activityType', 'email')
            ->set('activitySubject', 'Sent product catalog')
            ->call('logActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('crm_activities', [
            'buyer_id' => $company->id,
            'subject' => 'Sent product catalog',
        ]);
    }
}
