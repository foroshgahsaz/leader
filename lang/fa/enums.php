<?php

return [

    'buyer_status' => [
        'new' => 'جدید',
        'saved' => 'ذخیره‌شده',
        'contacted' => 'تماس گرفته‌شده',
        'replied' => 'پاسخ داده',
        'qualified' => 'واجد شرایط',
        'unqualified' => 'فاقد شرایط',
        'do_not_contact' => 'تماس ممنوع',
    ],

    'company_type' => [
        'importer' => 'واردکننده',
        'distributor' => 'توزیع‌کننده',
        'retailer' => 'خرده‌فروش',
        'manufacturer' => 'تولیدکننده',
        'wholesaler' => 'عمده‌فروش',
    ],

    'organization_role' => [
        'admin' => 'مدیر سیستم',
        'manager' => 'مدیر',
        'rep' => 'نماینده فروش',
    ],

    'org_member_status' => [
        'active' => 'فعال',
        'invited' => 'دعوت‌شده',
        'deactivated' => 'غیرفعال',
    ],

    'organization_status' => [
        'active' => 'فعال',
        'suspended' => 'معلق',
        'cancelled' => 'لغو شده',
    ],

    'score_band' => [
        'high' => 'بالا',
        'medium' => 'متوسط',
        'low' => 'پایین',
    ],

    'task_priority' => [
        'high' => 'بالا',
        'medium' => 'متوسط',
        'low' => 'پایین',
    ],

    'task_status' => [
        'pending' => 'در انتظار',
        'snoozed' => 'به تعویق افتاده',
        'completed' => 'تکمیل‌شده',
        'cancelled' => 'لغو شده',
    ],

    'lead_source' => [
        'search' => 'جستجو',
        'import' => 'واردات',
        'manual' => 'دستی',
        'recommendation' => 'پیشنهاد سیستم',
    ],

    'ai_generation_type' => [
        'email' => 'ایمیل',
        'whatsapp' => 'واتساپ',
        'follow_up' => 'پیگیری',
        'translate' => 'ترجمه',
        'company_summary' => 'خلاصه شرکت',
        'next_best_action' => 'بهترین اقدام بعدی',
        'risk_analysis' => 'تحلیل ریسک',
    ],

    'ai_generation_status' => [
        'pending' => 'در صف',
        'processing' => 'در حال پردازش',
        'completed' => 'تکمیل‌شده',
        'failed' => 'ناموفق',
    ],

    'import_batch_status' => [
        'pending' => 'در انتظار',
        'processing' => 'در حال پردازش',
        'completed' => 'تکمیل‌شده',
        'failed' => 'ناموفق',
    ],

    'crm_activity_type' => [
        'call' => 'تماس تلفنی',
        'email' => 'ایمیل',
        'visit' => 'بازدید',
        'demo' => 'دموی محصول',
        'linkedin' => 'لینکدین',
        'other' => 'سایر',
    ],

    'crm_meeting_status' => [
        'scheduled' => 'زمان‌بندی‌شده',
        'completed' => 'برگزار شده',
        'cancelled' => 'لغو شده',
        'no_show' => 'عدم حضور',
    ],

    'crm_timeline_entry_type' => [
        'activity' => 'فعالیت',
        'crm_activity' => 'فعالیت ثبت‌شده',
        'note' => 'یادداشت',
        'task' => 'وظیفه',
        'meeting' => 'جلسه',
        'file' => 'فایل',
        'stage_change' => 'تغییر مرحله',
        'system' => 'سیستم',
    ],

    'activity_action' => [
        'created' => 'ایجاد',
        'updated' => 'به‌روزرسانی',
        'deleted' => 'حذف',
        'login' => 'ورود',
        'logout' => 'خروج',
        'invited' => 'دعوت',
        'joined' => 'پیوستن',
        'deactivated' => 'غیرفعال‌سازی',
        'role_changed' => 'تغییر نقش',
        'settings_updated' => 'به‌روزرسانی تنظیمات',
    ],

    'audit_action' => [
        'user.login' => 'ورود کاربر',
        'user.logout' => 'خروج کاربر',
        'user.login_failed' => 'ورود ناموفق',
        'user.registered' => 'ثبت‌نام کاربر',
        'user.password_changed' => 'تغییر رمز عبور',
        'user.profile_updated' => 'به‌روزرسانی پروفایل',
        'organization.updated' => 'به‌روزرسانی سازمان',
        'team.member_invited' => 'دعوت عضو تیم',
        'team.member_removed' => 'حذف عضو تیم',
        'team.role_changed' => 'تغییر نقش تیم',
        'settings.updated' => 'به‌روزرسانی تنظیمات',
    ],

];
