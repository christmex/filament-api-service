<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\ApiService;

Route::prefix('api')
    ->name('api.')
    ->group(function () {
        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {

                $panelRoutePrefix = ApiService::isRoutePrefixedByPanel() ? $panel->getId() : '';
                $hasTenancy = $panel->hasTenancy();
                $tenantRoutePrefix = $panel->getTenantRoutePrefix();
                $tenantSlugAttribute = $panel->getTenantSlugAttribute();

                if ($hasTenancy && config('api-service.tenancy.is_tenant_aware')) {
                    Route::prefix($panelRoutePrefix . '/' . (($tenantRoutePrefix) ? "{$tenantRoutePrefix}/" : '') . '{tenant' . (($tenantSlugAttribute) ? ":{$tenantSlugAttribute}" : '') . '}')
                        ->name($panelRoutePrefix . '.')
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                } else {
                    Route::prefix($panelRoutePrefix)
                        ->name($panelRoutePrefix . '.')
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }
            } catch (Exception $e) {
            }
        }
    });
