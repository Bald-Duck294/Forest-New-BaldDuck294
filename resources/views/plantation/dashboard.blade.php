@extends('layouts.app')

@section('title','Plantation Dashboard')

@section('content')

<h2 class="text-2xl font-bold mb-6">Active Plantations</h2>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-100">
<tr>
<th class="p-3">Code</th>
<th class="p-3">Name</th>
<th class="p-3">Grid</th>
<th class="p-3">Phase</th>
<th class="p-3">Action</th>
</tr>
</thead>

<tbody>

@foreach($plantations as $pln)

<tr class="border-t">

<td class="p-3">{{ $pln->code }}</td>
<td class="p-3">{{ $pln->name }}</td>
<td class="p-3">{{ $pln->grid_id }}</td>
<td class="p-3">{{ $pln->current_phase }}</td>

<td class="p-3">

<a href="/plantation/workflow/{{$pln->id}}"
class="text-green-600 font-semibold">

Open Workflow

</a>

</td>

</tr>

@endforeach

</tbody>

</table>

@endsection
