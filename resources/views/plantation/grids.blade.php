@extends('layouts.app')

@section('title','Grid Management')

@section('content')

<h2 class="text-2xl font-bold mb-6">Grid Operations</h2>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-100">
<tr>
<th class="p-3">Grid Code</th>
<th class="p-3">Status</th>
</tr>
</thead>

<tbody>

@foreach($grids as $grid)

<tr class="border-t">

<td class="p-3">{{ $grid->code }}</td>

<td class="p-3">

@if($grid->status=='available')

<span class="text-green-600">Available</span>

@else

<span class="text-orange-600">Used</span>

@endif

</td>

</tr>

@endforeach

</tbody>

</table>

@endsection
