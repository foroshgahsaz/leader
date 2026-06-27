<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @return list<string>
     */
    public static function crmPermissions(): array
    {
        return [
            'crm.view',
            'crm.companies.view',
            'crm.companies.create',
            'crm.companies.update',
            'crm.companies.delete',
            'crm.contacts.view',
            'crm.contacts.create',
            'crm.contacts.update',
            'crm.contacts.delete',
            'crm.deals.view',
            'crm.deals.create',
            'crm.deals.update',
            'crm.deals.change-stage',
            'crm.tasks.view',
            'crm.tasks.create',
            'crm.tasks.update',
            'crm.tasks.complete',
            'crm.activities.view',
            'crm.activities.create',
            'crm.meetings.view',
            'crm.meetings.create',
            'crm.meetings.update',
            'crm.files.view',
            'crm.files.upload',
            'crm.files.delete',
            'crm.pipeline.view',
            'crm.reports.view',
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'company.view',
            'company.update',
            'team.view',
            'team.invite',
            'team.manage',
            'settings.view',
            'settings.update',
            'activity.view',
            'audit.view',
            'notifications.view',
            'dashboard.view',
            'leads.view',
            'leads.search',
            'leads.save',
            'leads.update',
            'leads.delete',
            'leads.export',
            'leads.import',
            'leads.assign',
            'lists.view',
            'lists.manage',
            'ai.generate',
            'ai.view',
            ...self::crmPermissions(),
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $managerCrmPermissions = array_values(array_filter(
            self::crmPermissions(),
            fn (string $permission): bool => $permission !== 'crm.companies.delete',
        ));

        $repCrmPermissions = [
            'crm.view',
            'crm.companies.view',
            'crm.companies.create',
            'crm.companies.update',
            'crm.contacts.view',
            'crm.contacts.create',
            'crm.contacts.update',
            'crm.deals.view',
            'crm.deals.create',
            'crm.deals.update',
            'crm.deals.change-stage',
            'crm.tasks.view',
            'crm.tasks.create',
            'crm.tasks.update',
            'crm.tasks.complete',
            'crm.activities.view',
            'crm.activities.create',
            'crm.meetings.view',
            'crm.meetings.create',
            'crm.meetings.update',
            'crm.files.view',
            'crm.files.upload',
            'crm.pipeline.view',
        ];

        $rolePermissions = [
            OrganizationRole::Admin->value => $permissions,
            OrganizationRole::Manager->value => [
                'company.view',
                'company.update',
                'team.view',
                'team.invite',
                'team.manage',
                'settings.view',
                'settings.update',
                'activity.view',
                'notifications.view',
                'dashboard.view',
                'leads.view',
                'leads.search',
                'leads.save',
                'leads.update',
                'leads.delete',
                'leads.export',
                'leads.import',
                'leads.assign',
                'lists.view',
                'lists.manage',
                'ai.generate',
                'ai.view',
                ...$managerCrmPermissions,
            ],
            OrganizationRole::Rep->value => [
                'company.view',
                'settings.view',
                'settings.update',
                'notifications.view',
                'dashboard.view',
                'leads.view',
                'leads.search',
                'leads.save',
                'leads.update',
                'leads.export',
                'lists.view',
                'ai.generate',
                'ai.view',
                ...$repCrmPermissions,
            ],
        ];

        foreach ($rolePermissions as $roleName => $assignedPermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($assignedPermissions);
        }
    }
}
