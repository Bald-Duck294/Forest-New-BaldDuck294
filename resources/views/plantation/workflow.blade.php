@extends('layouts.app')

@section('title','Plantation Workflow')

@section('content')

<div class="max-w-7xl mx-auto">

    <!-- BACK -->
    <a href="/plantation/dashboard"
        class="flex items-center text-sm text-slate-500 mb-4 hover:text-slate-800">
        ← Back to Dashboard
    </a>


    <!-- HEADER -->

    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6 flex justify-between items-start">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                {{ $plantation->name ?? $plantation->code }}
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Code : {{ $plantation->code }} |
                Grid : {{ $plantation->grid_id }}
            </p>
        </div>

        <div class="text-right">
            <div class="text-xs text-slate-400 uppercase mb-1">
                System Status
            </div>

            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-700">
                Strict Sequential Flow
            </span>
        </div>

    </div>



    <!-- STEPPER -->

    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6 overflow-x-auto">

        <ol class="flex items-center w-full min-w-[600px]">

            @php
            $currentIndex = array_search($plantation->current_phase,$phases);
            @endphp

            @foreach($phases as $phase)

            @php $i = $loop->index; @endphp

            <li class="flex w-full items-center text-sm font-medium">

                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2

@if($i < $currentIndex)
bg-green-600 border-green-600 text-white
@elseif($i == $currentIndex)
border-green-600 text-green-600
@else
border-slate-200 text-slate-400
@endif
">

                    @if($i < $currentIndex)
                        ✓
                        @else
                        {{ $i+1 }}
                        @endif

                        </div>

                        <span class="ml-3 capitalize
@if($i == $currentIndex) font-bold text-green-600 @endif">
                            {{ $phase }}
                        </span>

                        @if(!$loop->last)
                        <div class="flex-auto border-t-2 mx-4
@if($i < $currentIndex) border-green-600
@else border-slate-200
@endif"></div>
                        @endif

            </li>

            @endforeach

        </ol>

    </div>



    <!-- CONTENT GRID -->

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT FORM -->

        <div class="lg:col-span-2">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                <div class="bg-slate-50 p-4 border-b border-slate-200">

                    <h3 class="font-bold capitalize">
                        Active Phase : {{ $plantation->current_phase }}
                    </h3>

                </div>


                <div class="p-6">

                    <form method="POST">
                        @csrf


                        @if($plantation->current_phase=='identification')

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="text-sm font-medium">Land Owner Name</label>
                                <input type="text" name="land_owner_name"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Land Type</label>
                                <select name="land_type"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">

                                    <option value="govt">Government</option>
                                    <option value="private">Private</option>
                                    <option value="community">Community</option>

                                </select>
                            </div>

                        </div>

                        @endif



                        @if($plantation->current_phase=='measurement')

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="text-sm font-medium">Total Area (sq m)</label>
                                <input type="number" name="area"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Soil Type</label>
                                <input type="text" name="soil_type"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                        </div>

                        @endif



                        @if($plantation->current_phase=='planning')

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="text-sm font-medium">Plant Species</label>
                                <input type="text" name="plant_species"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Plant Count</label>
                                <input type="number" name="plant_count"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                        </div>

                        @endif



                        @if($plantation->current_phase=='planting')

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="text-sm font-medium">Planting Date</label>
                                <input type="date" name="planting_date"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Team Size</label>
                                <input type="number" name="team_size"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                        </div>

                        @endif



                        @if($plantation->current_phase=='fencing')

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="text-sm font-medium">Fence Type</label>
                                <input type="text" name="fence_type"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Boundary Length</label>
                                <input type="number" name="boundary_length"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                        </div>

                        @endif



                        @if($plantation->current_phase=='observation')

                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="text-sm font-medium">Survival Count</label>
                                <input type="number" name="survival_count"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Avg Height</label>
                                <input type="number" name="avg_height"
                                    class="w-full border border-slate-300 rounded-lg p-2 mt-1">
                            </div>

                        </div>

                        @endif



                        <div class="mt-8 flex justify-end pt-4 border-t">

                            <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">

                                Save & Advance Phase →

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>



        <!-- RIGHT PANEL -->

        <div class="space-y-6">

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">

                <h3 class="font-bold mb-4">
                    Audit Trail
                </h3>

                <p class="text-sm text-slate-500">
                    No actions recorded yet.
                </p>

            </div>


            <div class="bg-blue-50 p-5 rounded-xl border border-blue-100">

                <h3 class="font-bold text-blue-900 mb-2">
                    Workflow Rules
                </h3>

                <ul class="text-sm text-blue-800 space-y-2 list-disc pl-4">

                    <li>Phases cannot be skipped</li>
                    <li>Completed phase data is read-only</li>
                    <li>Relocation restarts workflow</li>

                </ul>

            </div>

        </div>

    </div>

</div>

@endsection
