@extends('layouts.app')
@section('title', 'Add Mining Record')
@section('page-title', 'Mining and Assay Records')
@section('content')
<div style="max-width:680px;">
    <div class="page-header">
        <h1 class="page-title">Add Mining Record</h1>
        <a href="{{ route('assay.index', ['tab' => 'mining']) }}" class="btn-cancel">&larr; Back</a>
    </div>
    @include('assay.mining._form', ['action' => route('assay.mining.store'), 'method' => 'POST', 'miningRecord' => null])
</div>
@endsection
