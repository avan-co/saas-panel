<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function tenantContext(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('tenantContext');
        }

        return new \App\Libraries\TenantContext(
            model(\App\Models\TenantModel::class),
            model(\App\Models\TenantMembershipModel::class),
            model(\App\Models\ModuleModel::class),
        );
    }

    public static function permissions(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('permissions');
        }

        return new \App\Libraries\PermissionService();
    }

    public static function upload(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('upload');
        }

        return new \App\Libraries\UploadService();
    }

    public static function audit(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('audit');
        }

        return new \App\Libraries\AuditLogger();
    }

    public static function periodLock(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('periodLock');
        }

        return new \App\Libraries\PeriodLockService();
    }

    public static function journal(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('journal');
        }

        return new \App\Libraries\JournalService();
    }

    public static function financeTxn(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('financeTxn');
        }

        return new \App\Libraries\FinanceTransactionService();
    }

    public static function webhook(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('webhook');
        }

        return new \App\Libraries\WebhookDispatcher();
    }

    public static function export(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('export');
        }

        return new \App\Libraries\ExportService();
    }

    public static function forecast(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('forecast');
        }

        return new \App\Libraries\ForecastService();
    }

    public static function modian(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('modian');
        }

        return new \App\Libraries\ModianService();
    }

    public static function payrollCalc(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('payrollCalc');
        }

        return new \App\Libraries\PayrollCalculator();
    }

    public static function insights(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('insights');
        }

        return new \App\Libraries\InsightEngine();
    }

    public static function person(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('person');
        }

        return new \App\Libraries\PersonService();
    }

    public static function erp(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('erp');
        }

        return new \App\Libraries\ErpIntegrationService();
    }

    public static function timesheet(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('timesheet');
        }

        return new \App\Libraries\TimesheetService();
    }

    public static function document(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('document');
        }

        return new \App\Libraries\DocumentService();
    }

    public static function project(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('project');
        }

        return new \App\Libraries\ProjectService();
    }
}
