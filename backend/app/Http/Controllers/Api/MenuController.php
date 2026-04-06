<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $menus = Menu::with('children')
            ->topLevel()
            ->active()
            ->orderBy('order')
            ->get()
            ->filter(function ($menu) use ($user) {
                // If permission is null, it's public (for authenticated users)
                if (!$menu->permission) {
                    return true;
                }
                
                // If nested children exist, filter them too
                $menu->setRelation('children', $menu->children->filter(function ($child) use ($user) {
                    return !$child->permission || $user->hasPermissionTo($child->permission);
                }));

                return $user->hasPermissionTo($menu->permission);
            });

        return response()->json($menus->values());
    }
}
