<?php

if (!function_exists('notifyAdminNewEmployee')) {
    function notifyAdminNewEmployee($employee)
    {
        \App\Services\AdminNotificationService::notifyNewEmployeeRegistration($employee);
    }
}

if (!function_exists('notifyAdminTrainingRequest')) {
    function notifyAdminTrainingRequest($trainingRequest)
    {
        \App\Services\AdminNotificationService::notifyTrainingRequest($trainingRequest);
    }
}

if (!function_exists('notifyAdminTrainingCompletion')) {
    function notifyAdminTrainingCompletion($completion)
    {
        \App\Services\AdminNotificationService::notifyTrainingCompletion($completion);
    }
}

if (!function_exists('getAdminUnreadNotificationCount')) {
    function getAdminUnreadNotificationCount()
    {
        return \App\Services\AdminNotificationService::getUnreadCount();
    }
}
