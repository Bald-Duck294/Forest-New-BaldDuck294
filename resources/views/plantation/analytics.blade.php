@extends('layouts.app')

@section('title','Survival Analytics')

@section('content')

<h2 class="text-2xl font-bold mb-6">Plantation Analytics</h2>

<div class="bg-white p-6 rounded shadow">

<canvas id="survivalChart"></canvas>

</div>

<script>

const ctx = document.getElementById('survivalChart');

new Chart(ctx, {

type:'bar',

data:{

labels:['Site 1','Site 2','Site 3'],

datasets:[

{
label:'Planted',
data:[5000,2000,3500],
backgroundColor:'#e2e8f0'
},

{
label:'Survived',
data:[4250,1440,3150],
backgroundColor:'#22c55e'
}

]

}

});

</script>

@endsection
