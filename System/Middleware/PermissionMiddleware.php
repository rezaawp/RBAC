<?php

namespace App\Extensions\RBAC\System\Middleware;

use App\Enums\Roles;
use App\Models\Common\Menu;
use App\Services\Common\MenuService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd($request->route()->getName());
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        if (! Auth::user()?->isAdmin()) {
            return redirect()->route('index');
        }

        $role = Role::findByName(Roles::ADMIN->value);
        $approvedArray = collect($role->getAllPermissions())->pluck('name')->merge(['admin_dashboard']);
        
        $service = new MenuService;
        $menus = collect($service->generate())->where('is_admin', true);
        
        $accessibleRoutes = collect();

        foreach ($menus as $menu) {
            if ($approvedArray->contains($menu['key'])) {
                if (is_array($menu['active_condition'])) {
                    $accessibleRoutes = $accessibleRoutes->concat($menu['active_condition']);
                } else {
                    foreach ($menu['children'] as $child) {
                        $accessibleRoutes->push($child['active_condition'] ?? $child['route']);
                    }
                }
            }
        }

        if ($accessibleRoutes->contains(fn ($pattern) => Str::is($pattern, $request->route()->getName()))) {
            return $next($request);
        }

        $menu = Menu::query()->where('route', $request->route()->getName())->first();

        if (auth()->user()->can($menu->key)) {
            return $next($request);
        }

        abort(401);
    }
}
