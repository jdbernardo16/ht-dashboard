<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AdministrativeAlertsTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::select(['id', 'name', 'email'])->get();
            return response()->json([
                'data' => $users,
                'message' => 'Users retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'data' => $user->only(['id', 'name', 'email', 'created_at']),
            'message' => 'User retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Get the original values before update
        $originalValues = $user->only(['name', 'email', 'role', 'status']);
        
        $user->update($validated);
        
        // Check if an admin account was modified
        if ($user->isAdmin() || $user->getOriginal('role') === 'admin') {
            $changes = [];
            
            foreach ($originalValues as $key => $originalValue) {
                if ($user->$key !== $originalValue) {
                    $changes[$key] = [
                        'old' => $originalValue,
                        'new' => $user->$key
                    ];
                }
            }
            
            if (!empty($changes)) {
                $this->triggerAdminAccountModifiedAlert($user, $changes);
            }
        }

        return response()->json([
            'data' => $user->only(['id', 'name', 'email']),
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Check if this is an admin account being deleted
        $isAdmin = $user->isAdmin();
        
        // Store user info before deletion
        $userInfo = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ];
        
        $user->delete();
        
        // Trigger user deletion alert
        if ($isAdmin) {
            $this->triggerUserAccountDeletedAlert(
                (object) $userInfo,
                'Admin account deleted by ' . auth()->user()->name
            );
        } else {
            $this->triggerUserAccountDeletedAlert(
                (object) $userInfo,
                'User account deleted by ' . auth()->user()->name
            );
        }

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
