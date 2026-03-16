{{-- Guard Performance Rankings --}}
@php
    $fullPerf = $guardPerformance['fullPerformance'] ?? collect();
    $hasMeaningfulData = $fullPerf->contains(function ($guard) {
        return ($guard->patrol_sessions ?? 0) > 0
            || ($guard->total_distance_km ?? 0) > 0
            || ($guard->days_present ?? 0) > 0
            || ($guard->incidents_reported ?? 0) > 0;
    });
    $showNoData = $fullPerf->isEmpty() || !$hasMeaningfulData;
@endphp
{{-- Guard Performance Rankings --}}
@php
    $fullPerf = $guardPerformance['fullPerformance'] ?? collect();
    $hasMeaningfulData = $fullPerf->contains(function ($guard) {
        return ($guard->patrol_sessions ?? 0) > 0
            || ($guard->total_distance_km ?? 0) > 0
            || ($guard->days_present ?? 0) > 0
            || ($guard->incidents_reported ?? 0) > 0;
    });
    $showNoData = $fullPerf->isEmpty() || !$hasMeaningfulData;
@endphp

<div class="bg-white dark:bg-card-bg p-6 rounded-2xl border border-slate-200 dark:border-slate-700/50 mb-6">
    <div class="flex items-center justify-between mb-6">
        <h4 class="font-bold text-lg text-slate-900 dark:text-white">Guard Performance Overview</h4>
        <button class="text-sm text-neon-blue font-medium hover:underline p-0 bg-transparent border-0 cursor-pointer">View All</button>
    </div>

    @if($showNoData)
        <div class="flex items-center justify-center py-12 px-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700/50">
            <p class="mb-0 text-slate-500 dark:text-slate-400 font-medium">No data available for this range</p>
        </div>
    @else
        @php
            $guardsWithData = $fullPerf->values();
        @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left sortable-table">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700/50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-center w-12 cursor-pointer" data-sortable>Rank</th>
                        <th class="px-4 py-3 font-semibold cursor-pointer" data-sortable>Guard</th>
                        <th class="px-4 py-3 font-semibold text-center cursor-pointer" data-sortable data-type="number">Patrols</th>
                        <th class="px-4 py-3 font-semibold text-center cursor-pointer" data-sortable data-type="number">Distance</th>
                        <th class="px-4 py-3 font-semibold text-center hidden md:table-cell cursor-pointer" data-sortable data-type="number">Avg Time</th>
                        <th class="px-4 py-3 font-semibold text-center cursor-pointer" data-sortable data-type="number">Incidents</th>
                        <th class="px-4 py-3 font-semibold text-center cursor-pointer" data-sortable data-type="number">Score /5.0</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    @foreach($guardsWithData as $index => $guard)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-4 py-3 text-center">
                                @if($index == 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-500 font-bold text-xs"><i class="fa-solid fa-trophy text-[10px]"></i></span>
                                @elseif($index == 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-500/20 text-slate-600 dark:text-slate-400 font-bold text-xs">2</span>
                                @elseif($index == 2)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-500 font-bold text-xs">3</span>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-neon-blue/20 text-neon-blue flex items-center justify-center font-bold mr-3 border border-neon-blue/30 text-xs">
                                        {{ substr($guard->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900 dark:text-white guard-name-link cursor-pointer hover:text-neon-blue" data-guard-id="{{ $guard->id }}">{{ $guard->name }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $guard->days_present ?? 0 }}d present</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300">{{ $guard->patrol_sessions ?? 0 }}</td>
                            <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300">{{ number_format($guard->total_distance_km ?? 0, 1) }}km</td>
                            <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 hidden md:table-cell">{{ number_format($guard->avg_duration_hours ?? 0, 1) }}h</td>
                            <td class="px-4 py-3 text-center">
                                <span class="cursor-pointer hover:text-neon-coral transition-colors" onclick="showIncidentsByType('all', 'Incidents by {{ addslashes($guard->name) }}', {user: '{{ $guard->id }}'})">
                                    @if(($guard->incidents_reported ?? 0) > 0)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-medium bg-neon-coral/20 text-neon-coral border border-neon-coral/30">
                                            {{ $guard->incidents_reported }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">0</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center">
                                    <span class="font-bold text-neon-emerald mr-2">{{ number_format($guard->performance_score ?? 0, 1) }}</span>
                                    <div class="w-16 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                        @php
                                            $scoreWidth = min(100, (($guard->performance_score ?? 0) / 5) * 100);
                                        @endphp
                                        <div class="h-full bg-neon-emerald" style="width: {{ $scoreWidth }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
 


