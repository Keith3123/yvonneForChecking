<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// ✅ ADD THIS — public channel, no auth needed for admin
Broadcast::channel('admin-orders', function () {
    return true;
});