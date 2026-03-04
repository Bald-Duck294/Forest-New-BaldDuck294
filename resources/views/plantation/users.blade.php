@extends('layouts.app')

@section('title','User Roles')

@section('content')

<h2 class="text-2xl font-bold mb-6">User Management</h2>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-100">
<tr>
<th class="p-3">User ID</th>
<th class="p-3">Name</th>
<th class="p-3">Role</th>
<th class="p-3">Status</th>
</tr>
</thead>

<tbody>

@foreach($users as $user)

<tr class="border-t">

<td class="p-3">{{ $user->id }}</td>
<td class="p-3">{{ $user->name }}</td>
<td class="p-3">{{ $user->role_id }}</td>

<td class="p-3">

@if($user->isActive)

<span class="text-green-600">Active</span>

@else

<span class="text-gray-500">Inactive</span>

@endif

</td>

</tr>

@endforeach

</tbody>

</table>

@endsection
