<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- IMMEDIATE translation service initialization - MUST be first -->
  <script>
    (function(){window.translationService=window.translationService||{translate:function(k){return k},get:function(k){return k},trans:function(k){return k},choice:function(k){return k},setLocale:function(l){return l},getLocale:function(){return'en'},has:function(){return true},translations:{},setTranslations:function(t){this.translations=t||{}}};window.trans=window.translationService.translate;window.__=window.translationService.translate;window.app=window.app||{locale:'en',fallback_locale:'en',translationService:window.translationService};window.Laravel=window.Laravel||{};window.Laravel.translationService=window.translationService;if(typeof global!=='undefined'){global.translationService=window.translationService}})();
  </script>
  
  <title>@yield('title', 'Employee Portal - Jetlouge Travels')</title>
  
  <!-- Load translation service FIRST to prevent undefined errors -->
  <script src="{{ asset('js/translation-service-init.js') }}"></script>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  @stack('styles')
</head>
<body style="background-color: #f8f9fa !important;">

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

<main id="main-content">
  @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>